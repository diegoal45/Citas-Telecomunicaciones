<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use App\Models\Team;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TechLeaderController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        // Obtener equipos que lidera
        $teams = $user->ledTeams()->with('members', 'leader')->get();
        $teamIds = $teams->pluck('id')->toArray();

        // Obtener citas de sus equipos
        $appointments = Appointment::whereIn('team_id', $teamIds)
            ->with('client', 'team', 'quotation')
            ->orderBy('scheduled_date', 'desc')
            ->get();

        // Obtener miembros de sus equipos
        $teamMembers = collect();
        foreach ($teams as $team) {
            $members = $team->members()->with('role', 'teams')->get();
            $teamMembers = $teamMembers->merge($members);
        }
        $teamMembers = $teamMembers->unique('id')->values();

        return response()->json([
            'teams' => $teams,
            'appointments' => $appointments,
            'teamMembers' => $teamMembers
        ]);
    }

    public function executeAppointment($id)
    {
        $appointment = Appointment::findOrFail($id);
        $user = Auth::user();

        // Verificar que sea técnico líder del equipo
        if (!$user->ledTeams()->where('team_id', $appointment->team_id)->exists()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Verificar que esté en estado programada
        if ($appointment->status !== 'programada') {
            return response()->json(['error' => 'La cita debe estar en estado programada'], 400);
        }

        $appointment->update(['status' => 'ejecutada']);

        // Notificar al cliente que su cita fue ejecutada
        Notification::create([
            'user_id' => $appointment->client_id,
            'type' => 'appointment_executed',
            'title' => 'Cita ejecutada',
            'message' => "El equipo {$appointment->team->name} ha completado la ejecución de tu cita.",
            'data' => [
                'appointment_id' => $appointment->id,
                'team_name' => $appointment->team->name,
                'executed_by' => $user->name,
                'executed_at' => Carbon::now()->toIso8601String()
            ]
        ]);

        // Notificar al equipo que la cita fue ejecutada
        $teamMembers = $appointment->team->members()->get();
        foreach ($teamMembers as $member) {
            Notification::create([
                'user_id' => $member->id,
                'type' => 'team_appointment_executed',
                'title' => 'Cita ejecutada',
                'message' => "La cita para el cliente {$appointment->client->name} ha sido marcada como ejecutada por {$user->name}.",
                'data' => [
                    'appointment_id' => $appointment->id,
                    'client_name' => $appointment->client->name,
                    'marked_by' => $user->name
                ]
            ]);
        }

        return response()->json(['message' => 'Cita marcada como ejecutada']);
    }
}
