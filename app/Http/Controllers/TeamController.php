<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Notification;
use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Http\Requests\AddMembersRequest;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    /**
     * Listar equipos (usuarios ven solo sus equipos, admins ven todos).
     */
    public function index()
    {
        $user = Auth::user();
        $isAdmin = $user->role->name === 'admin';

        if ($isAdmin) {
            // Admin ve todos los equipos
            $teams = Team::with('leader', 'members')->get();
        } else {
            // Usuario ve solo equipos donde es miembro o líder
            $teams = Team::with('leader', 'members')
                ->where('leader_id', $user->id)
                ->orWhereHas('members', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->get();
        }

        return response()->json($teams);
    }

    /**
     * Crear equipo (solo admins).
     */
    public function store(StoreTeamRequest $request)
    {
        if (Auth::user()->role->name !== 'admin') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $team = Team::create($request->validated());

        if ($request->has('member_ids')) {
            $team->members()->attach($request->member_ids);
        }

        return response()->json($team->load('leader', 'members'), 201);
    }

    /**
     * Ver equipo (usuario ve si pertenece o lidera, admin ve cualquiera).
     */
    public function show(Team $team)
    {
        $user = Auth::user();
        $isAdmin = $user->role->name === 'admin';
        $isLeader = $team->leader_id === $user->id;
        $isMember = $team->members()->where('user_id', $user->id)->exists();

        if (!$isAdmin && !$isLeader && !$isMember) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return response()->json($team->load('leader', 'members', 'appointments'));
    }

    /**
     * Actualizar equipo (solo admin o líder del equipo).
     */
    public function update(UpdateTeamRequest $request, Team $team)
    {
        $user = Auth::user();
        $isAdmin = $user->role->name === 'admin';
        $isLeader = $team->leader_id === $user->id;

        if (!$isAdmin && !$isLeader) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $team->update($request->validated());
        return response()->json($team->load('leader', 'members'));
    }

    /**
     * Eliminar equipo (solo admin o líder del equipo).
     */
    public function destroy(Team $team)
    {
        $user = Auth::user();
        $isAdmin = $user->role->name === 'admin';
        $isLeader = $team->leader_id === $user->id;

        if (!$isAdmin && !$isLeader) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $team->members()->detach();
        $team->delete();
        return response()->json(['message' => 'Equipo eliminado']);
    }

    /**
     * Agregar miembros al equipo (solo admin o líder del equipo).
     */
    public function addMembers(AddMembersRequest $request, $id)
    {
        $team = Team::findOrFail($id);
        $user = Auth::user();
        $isAdmin = $user->role->name === 'admin';
        $isLeader = $team->leader_id === $user->id;

        if (!$isAdmin && !$isLeader) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $team->members()->syncWithoutDetaching($request->member_ids);
        return response()->json($team->load('members'));
    }

    /**
     * Remover miembro del equipo (solo admin o líder del equipo).
     */
    public function removeMember($id, $memberId)
    {
        $team = Team::findOrFail($id);
        $user = Auth::user();
        $isAdmin = $user->role->name === 'admin';
        $isLeader = $team->leader_id === $user->id;

        if (!$isAdmin && !$isLeader) {
            return response()->json(['message' => 'No autorizado. Solo admin o líder pueden remover miembros'], 403);
        }

        // Verificar que el miembro existe en el equipo
        $isMember = $team->members()->where('user_id', $memberId)->exists();
        if (!$isMember) {
            return response()->json(['message' => 'El usuario no es miembro de este equipo'], 400);
        }

        $member = \App\Models\User::findOrFail($memberId);
        
        // Obtener los miembros restantes ANTES de eliminar
        $remainingMembers = $team->members()->where('user_id', '!=', $memberId)->get();
        
        // Ahora eliminar del equipo
        $team->members()->detach($memberId);

        // Notificar al técnico que fue removido del equipo
        Notification::create([
            'user_id' => $memberId,
            'type' => 'removed_from_team',
            'title' => 'Removido del equipo',
            'message' => "Has sido removido del equipo {$team->name}",
            'data' => [
                'team_id' => $team->id,
                'team_name' => $team->name
            ]
        ]);

        // Notificar a los otros miembros del equipo
        foreach ($remainingMembers as $remainingMember) {
            Notification::create([
                'user_id' => $remainingMember->id,
                'type' => 'member_removed_team',
                'title' => 'Miembro removido del equipo',
                'message' => "{$member->name} ha sido removido del equipo {$team->name}",
                'data' => [
                    'team_id' => $team->id,
                    'team_name' => $team->name,
                    'removed_member' => $member->name
                ]
            ]);
        }

        return response()->json([
            'message' => 'Miembro eliminado del equipo',
            'removed_user_id' => $memberId,
            'team_id' => $team->id
        ]);
    }

    /**
     * Cambiar miembro de un equipo a otro (solo admin o líder del equipo origen).
     */
    public function changeMember($id, $memberId)
    {
        $sourceTeam = Team::findOrFail($id);
        $user = Auth::user();
        $isAdmin = $user->role->name === 'admin';
        $isLeader = $sourceTeam->leader_id === $user->id;

        if (!$isAdmin && !$isLeader) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // Validación: debe recibir el ID del equipo destino
        if (!request()->has('new_team_id')) {
            return response()->json(['message' => 'Debe proporcionar new_team_id'], 400);
        }

        $newTeamId = request()->input('new_team_id');
        $newTeam = Team::findOrFail($newTeamId);
        $member = \App\Models\User::findOrFail($memberId);

        // Remover del equipo origen
        $sourceTeam->members()->detach($memberId);
        
        // Agregar al equipo destino
        $newTeam->members()->attach($memberId);

        // Notificar al técnico del cambio
        Notification::create([
            'user_id' => $memberId,
            'type' => 'changed_team',
            'title' => 'Cambio de equipo',
            'message' => "Has sido trasladado del equipo {$sourceTeam->name} al equipo {$newTeam->name}",
            'data' => [
                'source_team_id' => $sourceTeam->id,
                'source_team_name' => $sourceTeam->name,
                'new_team_id' => $newTeam->id,
                'new_team_name' => $newTeam->name
            ]
        ]);

        // Notificar a miembros del equipo origen
        $sourceMembers = $sourceTeam->members()->get();
        foreach ($sourceMembers as $sourceMember) {
            Notification::create([
                'user_id' => $sourceMember->id,
                'type' => 'member_changed_team',
                'title' => 'Miembro trasladado',
                'message' => "{$member->name} ha sido trasladado a otro equipo",
                'data' => [
                    'team_id' => $sourceTeam->id,
                    'team_name' => $sourceTeam->name,
                    'member_name' => $member->name
                ]
            ]);
        }

        // Notificar a miembros del equipo destino
        $newMembers = $newTeam->members()->where('user_id', '!=', $memberId)->get();
        foreach ($newMembers as $newMember) {
            Notification::create([
                'user_id' => $newMember->id,
                'type' => 'new_member_team',
                'title' => 'Nuevo miembro en el equipo',
                'message' => "{$member->name} ha sido agregado al equipo {$newTeam->name}",
                'data' => [
                    'team_id' => $newTeam->id,
                    'team_name' => $newTeam->name,
                    'new_member' => $member->name
                ]
            ]);
        }

        return response()->json([
            'message' => "Miembro trasladado exitosamente de {$sourceTeam->name} a {$newTeam->name}"
        ]);
    }
}