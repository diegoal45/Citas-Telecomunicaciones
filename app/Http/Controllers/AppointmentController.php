<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\AssignTeamRequest;
use App\Http\Requests\CancelAppointmentRequest;
use App\Http\Requests\RescheduleRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function store(StoreAppointmentRequest $request)
    {
        $appointment = Appointment::create([
            'client_id' => Auth::id(),
            'appointment_type' => 'cotizacion',
            'status' => 'solicitada',
            'scheduled_date' => $request->scheduled_date,
            'address' => $request->address,
            'phone' => $request->phone,
            'description' => $request->description
        ]);

        return response()->json($appointment, 201);
    }

    public function index()
    {
        $user = Auth::user();

        if ($user->role->name === 'admin') {
            $appointments = Appointment::with('client', 'team', 'quotation')
                ->orderBy('scheduled_date')
                ->get();
        } elseif ($user->role->name === 'cliente') {
            $appointments = Appointment::where('client_id', $user->id)
                ->with('team', 'quotation')
                ->orderBy('scheduled_date')
                ->get();
        } elseif (in_array($user->role->name, ['tecnico_lider', 'tecnico'])) {
            $teamIds = $user->teams()->pluck('team_id');
            $appointments = Appointment::whereIn('team_id', $teamIds)
                ->with('client', 'quotation')
                ->orderBy('scheduled_date')
                ->get();
        }

        return response()->json($appointments);
    }

    public function show($id)
    {
        $appointment = Appointment::with('client', 'team', 'quotation')->findOrFail($id);
        
        $user = Auth::user();
        
        // Admin ve todo
        if ($user->role->name === 'admin') {
            return response()->json($appointment);
        }
        
        // Cliente ve solo sus citas
        if ($user->role->name === 'cliente') {
            if ($appointment->client_id !== $user->id) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
            return response()->json($appointment);
        }

        // Técnico/Técnico Líder ve solo citas asignadas a su equipo
        if (in_array($user->role->name, ['tecnico_lider', 'tecnico'])) {
            $teamIds = $user->teams()->pluck('team_id');
            if (!$appointment->team_id || !in_array($appointment->team_id, $teamIds->toArray())) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
        }

        return response()->json($appointment);
    }

    public function cancel(CancelAppointmentRequest $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        $user = Auth::user();

        $scheduledDate = Carbon::parse($appointment->scheduled_date);
        $hoursDifference = Carbon::now()->diffInHours($scheduledDate, false);

        if ($hoursDifference < 2) {
            return response()->json([
                'error' => 'No se puede cancelar con menos de 2 horas de anticipación'
            ], 400);
        }

        if ($user->role->name === 'cliente' && $appointment->client_id !== $user->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        if (in_array($user->role->name, ['tecnico_lider', 'tecnico'])) {
            $teamIds = $user->teams()->pluck('team_id');
            if (!in_array($appointment->team_id, $teamIds->toArray())) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
        }

        $appointment->update([
            'status' => 'cancelada',
            'cancelled_at' => Carbon::now(),
            'cancelled_by' => $user->id,
            'cancellation_reason' => $request->reason
        ]);

        return response()->json(['message' => 'Cita cancelada correctamente']);
    }

    public function assignTeam(AssignTeamRequest $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        $user = Auth::user();

        // Solo admin o líder del equipo puede asignar
        if ($user->role->name !== 'admin') {
            // Verificar si el usuario es líder del equipo que intenta asignar
            $team = \App\Models\Team::findOrFail($request->team_id);
            if ($team->leader_id !== $user->id) {
                return response()->json(['error' => 'No autorizado'], 403);
            }
        }

        $appointment->update([
            'team_id' => $request->team_id,
            'status' => 'pendiente_cotizacion'
        ]);

        return response()->json($appointment);
    }

    public function markAsCotizada($id)
    {
        $appointment = Appointment::findOrFail($id);
        $user = Auth::user();

        if ($appointment->team->leader_id !== $user->id) {
            return response()->json(['error' => 'Solo el líder puede hacer esto'], 403);
        }

        $appointment->update(['status' => 'cotizada']);
        return response()->json(['message' => 'Cita marcada como cotizada']);
    }

    public function reschedule(RescheduleRequest $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        $user = Auth::user();

        if ($user->role->name === 'cliente' && $appointment->client_id !== $user->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $appointment->update([
            'scheduled_date' => $request->new_date,
            'status' => 'solicitada'
        ]);

        return response()->json($appointment);
    }
}