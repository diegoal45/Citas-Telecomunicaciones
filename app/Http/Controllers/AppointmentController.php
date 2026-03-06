<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use App\Models\Notification;
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
        // Buscar un técnico líder disponible
        $scheduledDate = $request->scheduled_date;

        // Preferencia: si el cliente ya ha tenido una cita previa con un equipo
        // (por ejemplo una cotización aprobada o ejecución programada), tratamos
        // de reutilizar ese mismo equipo siempre que no tenga conflicto de fecha.
        $preferredTeam = null;
        $lastAppointment = Appointment::where('client_id', Auth::id())
            ->whereNotIn('status', ['cancelada'])
            // prefer the most recently created record when dates tie
            ->orderBy('scheduled_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();
        if ($lastAppointment && $lastAppointment->team_id) {
            $preferredTeam = $lastAppointment->team_id;
            // comprobar si el equipo preferido está libre en la fecha solicitada
            $conflict = Appointment::where('team_id', $preferredTeam)
                ->whereDate('scheduled_date', Carbon::parse($scheduledDate)->toDateString())
                ->exists();
            if ($conflict) {
                $preferredTeam = null; // si hay conflicto, no lo usamos
            }
        }

        // Obtener todos los técnicos líderes con sus equipos
        $techLeaders = User::whereHas('role', function($query) {
            $query->where('name', 'tecnico_lider');
        })->with('ledTeams')->get();

        if ($techLeaders->isEmpty()) {
            return response()->json([
                'error' => 'No hay técnicos líderes disponibles en el sistema'
            ], 400);
        }

        // Determinar el equipo a asignar: preferencia primero, de lo contrario el
        // primer equipo libre que encuentre la búsqueda estándar.
        $availableTeam = $preferredTeam;
        if (is_null($availableTeam)) {
            foreach ($techLeaders as $techLeader) {
                // Obtener los equipos que lidera
                $ledTeamIds = $techLeader->ledTeams()->pluck('id');
                
                if ($ledTeamIds->isEmpty()) {
                    continue; // Este líder no lidera ningún equipo
                }

                // Verificar si alguno de sus equipos está disponible en esa fecha
                $appointmentInDate = Appointment::whereIn('team_id', $ledTeamIds)
                    ->whereDate('scheduled_date', Carbon::parse($scheduledDate)->toDateString())
                    ->exists();

                if (!$appointmentInDate) {
                    // Este equipo está disponible
                    $availableTeam = $ledTeamIds->first();
                    break;
                }
            }
        }

        if (!$availableTeam) {
            return response()->json([
                'error' => 'No hay tecnicos líderes disponibles en la fecha y hora solicitada'
            ], 400);
        }

        // Crear la cita con el equipo asignado
        $appointment = Appointment::create([
            'client_id' => Auth::id(),
            'team_id' => $availableTeam,
            'appointment_type' => 'cotizacion',
            'status' => 'solicitada',
            'scheduled_date' => $scheduledDate,
            'address' => $request->address,
            'description' => $request->description
        ]);

        // Notificar al cliente que su cita fue creada
        Notification::create([
            'user_id' => Auth::id(),
            'type' => 'appointment_created',
            'title' => 'Cita creada',
            'message' => 'Tu cita ha sido registrada exitosamente',
            'data' => [
                'appointment_id' => $appointment->id,
                'scheduled_date' => $appointment->scheduled_date
            ]
        ]);

        // Notificar a todos los administradores sobre nueva solicitud
        $admins = User::whereHas('role', function ($query) {
            $query->where('name', 'admin');
        })->get();

        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'admin_new_appointment',
                'title' => 'Nueva cita solicitada',
                'message' => "El cliente {$appointment->client->name} solicitó una nueva cita.",
                'data' => [
                    'appointment_id' => $appointment->id,
                    'client_id' => $appointment->client_id,
                    'scheduled_date' => $appointment->scheduled_date,
                ]
            ]);
        }

        return response()->json($appointment->load('team'), 201);
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
        } elseif ($user->role->name === 'tecnico_lider') {
            // Ver citas de equipos que lidera
            $ledTeamIds = $user->ledTeams()->pluck('id');
            // Ver también citas de equipos donde es miembro
            $memberTeamIds = $user->teams()->pluck('team_id');
            $allTeamIds = $ledTeamIds->merge($memberTeamIds)->unique();

            $appointments = Appointment::whereIn('team_id', $allTeamIds)
                ->with('client', 'team', 'quotation')
                ->orderBy('scheduled_date')
                ->get();
        } elseif ($user->role->name === 'tecnico') {
            // Técnico ve solo citas aprobadas o programadas (ejecución) de sus equipos
            $teamIds = $user->teams()->pluck('team_id');
            $appointments = Appointment::whereIn('team_id', $teamIds)
                ->whereIn('status', ['aprobada', 'programada'])
                ->with('client', 'team', 'quotation')
                ->orderBy('scheduled_date')
                ->get();
        } else {
            $appointments = collect();
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
            
            // Técnico normal solo ve citas aprobadas o programadas
            if ($user->role->name === 'tecnico' && !in_array($appointment->status, ['aprobada', 'programada'])) {
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

        // Notificar al cliente que su cita fue cancelada
        Notification::create([
            'user_id' => $appointment->client_id,
            'type' => 'appointment_cancelled',
            'title' => 'Cita cancelada',
            'message' => "Tu cita ha sido cancelada. Motivo: {$request->reason}",
            'data' => [
                'appointment_id' => $appointment->id,
                'reason' => $request->reason
            ]
        ]);

        // Notificar al equipo que la cita fue cancelada
        if ($appointment->team) {
            $teamMembers = $appointment->team->members()->get();
            foreach ($teamMembers as $member) {
                Notification::create([
                    'user_id' => $member->id,
                    'type' => 'appointment_cancelled_team',
                    'title' => 'Cita cancelada',
                    'message' => "La cita del cliente {$appointment->client->name} ha sido cancelada. Motivo: {$request->reason}",
                    'data' => [
                        'appointment_id' => $appointment->id,
                        'client_name' => $appointment->client->name,
                        'reason' => $request->reason
                    ]
                ]);
            }
        }

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

        // Notificar a los miembros del equipo asignado
        $team = \App\Models\Team::with('leader')->findOrFail($request->team_id);
        $teamMembers = $team->members()->get();
        $teamLederName = $team->leader ? $team->leader->name : 'N/A';
        
        foreach ($teamMembers as $member) {
            Notification::create([
                'user_id' => $member->id,
                'type' => 'appointment_assigned',
                'title' => 'Nueva cita asignada',
                'message' => "{$user->name} te asignó una cotización del cliente {$appointment->client->name} al equipo {$team->name}. El técnico {$teamLederName} será quien irá.",
                'data' => [
                    'appointment_id' => $appointment->id,
                    'team_id' => $team->id,
                    'team_name' => $team->name,
                    'client_name' => $appointment->client->name,
                    'scheduled_date' => $appointment->scheduled_date,
                    'assigned_by' => $user->name,
                    'lead_technician' => $teamLederName,
                    'lead_technician_id' => $team->leader_id,
                ]
            ]);
        }

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
        
        // Notificar al cliente que su cotización está lista
        Notification::create([
            'user_id' => $appointment->client_id,
            'type' => 'quotation_ready',
            'title' => 'Cotización lista',
            'message' => "Tu cotización está lista para revisar",
            'data' => [
                'appointment_id' => $appointment->id,
                'team_name' => $appointment->team->name
            ]
        ]);
        
        return response()->json(['message' => 'Cita marcada como cotizada']);
    }

    public function reschedule(RescheduleRequest $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        $user = Auth::user();

        if ($user->role->name === 'cliente' && $appointment->client_id !== $user->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $oldDate = $appointment->scheduled_date;
        
        // Si la cita fue cancelada, restaurar el estado anterior (aprobada)
        // Si no fue cancelada, mantener lógica normal
        $newStatus = $appointment->status === 'cancelada' ? 'aprobada' : ($appointment->status === 'solicitada' ? 'solicitada' : 'aprobada');
        
        $appointment->update([
            'scheduled_date' => $request->new_date,
            'status' => $newStatus
        ]);

        // Notificar al equipo que la cita fue reprogramada
        if ($appointment->team) {
            $teamMembers = $appointment->team->members()->get();
            foreach ($teamMembers as $member) {
                Notification::create([
                    'user_id' => $member->id,
                    'type' => 'appointment_rescheduled',
                    'title' => 'Cita reprogramada',
                    'message' => "La cita del cliente {$appointment->client->name} ha sido reprogramada del " . Carbon::parse($oldDate)->format('d/m/Y H:i') . " al " . Carbon::parse($request->new_date)->format('d/m/Y H:i'),
                    'data' => [
                        'appointment_id' => $appointment->id,
                        'client_name' => $appointment->client->name,
                        'old_date' => $oldDate,
                        'new_date' => $request->new_date
                    ]
                ]);
            }
        }

        // Notificar al cliente que su cita fue reprogramada
        Notification::create([
            'user_id' => $appointment->client_id,
            'type' => 'appointment_rescheduled_client',
            'title' => 'Cita reprogramada',
            'message' => "Tu cita ha sido reprogramada para el " . Carbon::parse($request->new_date)->format('d/m/Y H:i'),
            'data' => [
                'appointment_id' => $appointment->id,
                'new_date' => $request->new_date
            ]
        ]);

        return response()->json($appointment);
    }

    public function markAsExecuted($id)
    {
        $appointment = Appointment::findOrFail($id);
        $user = Auth::user();

        // Solo técnico líder o admin pueden marcar como ejecutada
        if (!in_array($user->role->name, ['tecnico_lider', 'admin'])) {
            return response()->json(['error' => 'Solo técnico líder o admin pueden marcar como ejecutada'], 403);
        }

        // Verificar que sea cita de ejecución
        if ($appointment->appointment_type !== 'ejecucion') {
            return response()->json(['error' => 'Solo citas de ejecución pueden ser marcadas como ejecutadas'], 400);
        }

        // Verificar que sea del equipo del técnico líder
        if ($user->role->name === 'tecnico_lider' && $appointment->team->leader_id !== $user->id) {
            return response()->json(['error' => 'No eres el líder del equipo asignado'], 403);
        }

        $appointment->update(['status' => 'ejecutada']);

        // Notificar al cliente que la ejecución fue completada
        Notification::create([
            'user_id' => $appointment->client_id,
            'type' => 'execution_completed',
            'title' => 'Ejecución completada',
            'message' => 'Tu ejecución ha sido completada exitosamente',
            'data' => [
                'appointment_id' => $appointment->id,
                'completed_date' => Carbon::now()
            ]
        ]);

        // Notificar al equipo que la ejecución fue completada
        if ($appointment->team) {
            $teamMembers = $appointment->team->members()->get();
            foreach ($teamMembers as $member) {
                Notification::create([
                    'user_id' => $member->id,
                    'type' => 'execution_completed_team',
                    'title' => 'Ejecución completada',
                    'message' => "La ejecución para el cliente {$appointment->client->name} ha sido completada",
                    'data' => [
                        'appointment_id' => $appointment->id,
                        'client_name' => $appointment->client->name,
                        'completed_date' => Carbon::now()
                    ]
                ]);
            }
        }

        return response()->json(['message' => 'Cita marcada como ejecutada']);
    }
}