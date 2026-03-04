<?php

namespace App\Http\Controllers;

use App\Models\Team;
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
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $team->members()->detach($memberId);
        return response()->json(['message' => 'Miembro eliminado del equipo']);
    }
}