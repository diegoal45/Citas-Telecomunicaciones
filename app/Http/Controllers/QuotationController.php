<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\Appointment;
use App\Models\Notification;
use App\Http\Requests\StoreQuotationRequest;
use App\Http\Requests\SetPriceRequest;
use App\Http\Requests\ScheduleExecutionRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class QuotationController extends Controller
{
    public function store(StoreQuotationRequest $request)
    {
        $appointment = Appointment::find($request->appointment_id);
        
        if (!$appointment) {
            return response()->json(['error' => 'La cita no existe'], 404);
        }

        // Verificar que el equipo tenga equipo asignado
        if (!$appointment->team_id) {
            return response()->json(['error' => 'La cita no tiene equipo asignado'], 400);
        }

        // Verificar que sea el líder del equipo
        if ($appointment->team->leader_id !== Auth::id()) {
            return response()->json(['error' => 'No eres el líder del equipo asignado a esta cita'], 403);
        }

        $quotation = Quotation::updateOrCreate(
            ['appointment_id' => $request->appointment_id],
            $request->validated()
        );

        $appointment->update(['status' => 'cotizada']);

        return response()->json($quotation->load('appointment'), 201);
    }

    public function setPrice(SetPriceRequest $request, $id)
    {
        $quotation = Quotation::findOrFail($id);
        $user = Auth::user();

        // Solo el líder del equipo o admin puede establecer precio
        if ($user->role->name !== 'admin' && $quotation->appointment->team->leader_id !== $user->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $quotation->update(['price' => $request->price]);

        return response()->json($quotation);
    }

    public function approve($id)
    {
        $quotation = Quotation::findOrFail($id);
        $appointment = $quotation->appointment;

        if ($appointment->client_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // marca la cotización como aprobada, el cambio de tipo de cita
        // se realiza cuando se programa realmente la ejecución.
        $quotation->update(['approved_at' => Carbon::now()]);
        $appointment->update([
            'status' => 'aprobada',
            // mantiene appointment_type original (cotizacion)
        ]);

        // Notificar al equipo que su cotización fue aprobada
        if ($appointment->team) {
            $teamMembers = $appointment->team->members()->get();
            foreach ($teamMembers as $member) {
                Notification::create([
                    'user_id' => $member->id,
                    'type' => 'quotation_approved',
                    'title' => 'Cotización aprobada',
                    'message' => "La cotización para el cliente {$appointment->client->name} ha sido aprobada",
                    'data' => [
                        'appointment_id' => $appointment->id,
                        'client_name' => $appointment->client->name,
                        'scheduled_date' => $appointment->scheduled_date
                    ]
                ]);
            }
        }

        // Notificar al cliente que su cotización fue aprobada (confirmación)
        Notification::create([
            'user_id' => $appointment->client_id,
            'type' => 'quotation_approved_client',
            'title' => 'Cotización aprobada',
            'message' => 'Has aprobado la cotización, ahora procede a programar la ejecución',
            'data' => [
                'appointment_id' => $appointment->id
            ]
        ]);

        return response()->json(['message' => 'Cotización aprobada']);
    }

    public function reject($id)
    {
        $quotation = Quotation::findOrFail($id);
        $appointment = $quotation->appointment;

        if ($appointment->client_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $quotation->update(['rejected_at' => Carbon::now()]);
        $appointment->update(['status' => 'rechazada']);

        // Notificar al equipo que su cotización fue rechazada
        if ($appointment->team) {
            $teamMembers = $appointment->team->members()->get();
            foreach ($teamMembers as $member) {
                Notification::create([
                    'user_id' => $member->id,
                    'type' => 'quotation_rejected',
                    'title' => 'Cotización rechazada',
                    'message' => "La cotización para el cliente {$appointment->client->name} ha sido rechazada",
                    'data' => [
                        'appointment_id' => $appointment->id,
                        'client_name' => $appointment->client->name
                    ]
                ]);
            }
        }

        // Notificar al cliente que rechazó la cotización (confirmación)
        Notification::create([
            'user_id' => $appointment->client_id,
            'type' => 'quotation_rejected_client',
            'title' => 'Cotización rechazada',
            'message' => 'Has rechazado la cotización',
            'data' => [
                'appointment_id' => $appointment->id
            ]
        ]);

        return response()->json(['message' => 'Cotización rechazada']);
    }

    public function scheduleExecution(ScheduleExecutionRequest $request, $id)
    {
        $quotation = Quotation::findOrFail($id);
        $appointment = $quotation->appointment;
        $user = Auth::user();

        // Solo admin o el cliente puede programar ejecución
        if ($user->role->name !== 'admin' && $appointment->client_id !== $user->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // sólo se puede programar si la cotización ya fue aprobada
        if (is_null($quotation->approved_at)) {
            return response()->json(['error' => 'La cotización debe estar aprobada antes de programar la ejecución'], 400);
        }

        // actualiza la cita original en lugar de crear una nueva, de modo que
        // el historial no se fragmenta y no aparecen citas de ejecución "extra"
        $appointment->update([
            'appointment_type' => 'ejecucion',
            'status' => 'programada',
            'scheduled_date' => $request->scheduled_date,
            'team_id' => $request->team_id,
        ]);

        // Notificar al equipo que la ejecución fue programada
        $team = \App\Models\Team::find($request->team_id);
        if ($team) {
            $teamMembers = $team->members()->get();
            foreach ($teamMembers as $member) {
                Notification::create([
                    'user_id' => $member->id,
                    'type' => 'execution_scheduled',
                    'title' => 'Ejecución programada',
                    'message' => "Se ha programado la ejecución para el cliente {$appointment->client->name} el " . Carbon::parse($request->scheduled_date)->format('d/m/Y H:i'),
                    'data' => [
                        'appointment_id' => $appointment->id,
                        'client_name' => $appointment->client->name,
                        'scheduled_date' => $request->scheduled_date
                    ]
                ]);
            }
        }

        // Notificar al cliente que su ejecución fue programada
        Notification::create([
            'user_id' => $appointment->client_id,
            'type' => 'execution_scheduled_client',
            'title' => 'Ejecución programada',
            'message' => "Tu ejecución ha sido programada para el " . Carbon::parse($request->scheduled_date)->format('d/m/Y H:i'),
            'data' => [
                'appointment_id' => $appointment->id,
                'scheduled_date' => $request->scheduled_date
            ]
        ]);

        return response()->json($appointment, 200);
    }
}