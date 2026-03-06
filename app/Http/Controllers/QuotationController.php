<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\Appointment;
use App\Models\Notification;
use App\Models\User;
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

        // La cotizacion queda en estado cotizada, pendiente de que admin asigne precio.
        $appointment->update(['status' => 'cotizada']);

        // Notificar a administradores que hay una cotizacion lista para validar.
        $admins = User::whereHas('role', function ($query) {
            $query->where('name', 'admin');
        })->get();

        $user = Auth::user();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'quotation_pending_admin',
                'title' => 'Cotización pendiente de precio',
                'message' => "{$user->name} creó una cotización para {$appointment->client->name}: {$quotation->labor_hours} horas, {$quotation->required_staff} personal. Falta que admin asigne precio.",
                'data' => [
                    'appointment_id' => $appointment->id,
                    'quotation_id' => $quotation->id,
                    'client_name' => $appointment->client->name,
                    'team_name' => $appointment->team->name,
                    'prepared_by' => $user->name,
                    'materials' => $quotation->materials,
                    'labor_hours' => $quotation->labor_hours,
                    'required_staff' => $quotation->required_staff,
                    'price' => $quotation->price,
                ]
            ]);
        }

        // Notificar al cliente con el detalle de su cotizacion (aun sin precio)
        Notification::create([
            'user_id' => $appointment->client_id,
            'type' => 'quotation_created',
            'title' => 'Cotización creada',
            'message' => "Tu cotización fue creada: {$quotation->labor_hours} horas, {$quotation->required_staff} personal. Queda pendiente asignación de precio por administración.",
            'data' => [
                'appointment_id' => $appointment->id,
                'quotation_id' => $quotation->id,
                'team_name' => $appointment->team->name,
                'materials' => $quotation->materials,
                'labor_hours' => $quotation->labor_hours,
                'required_staff' => $quotation->required_staff,
                'price' => $quotation->price,
            ]
        ]);

        // Notificar a los miembros del equipo que la cotización fue creada/actualizada
        $teamMembers = $appointment->team->members()->get();
        foreach ($teamMembers as $member) {
            Notification::create([
                'user_id' => $member->id,
                'type' => 'quotation_created_team',
                'title' => 'Cotización creada para revisión',
                'message' => "La cotización para el cliente {$appointment->client->name} ha sido creada por {$user->name} y está lista para enviar.",
                'data' => [
                    'appointment_id' => $appointment->id,
                    'client_name' => $appointment->client->name,
                    'created_by' => $user->name
                ]
            ]);
        }

        return response()->json($quotation->load('appointment'), 201);
    }

    public function adminApprove($id)
    {
        $quotation = Quotation::with('appointment.client', 'appointment.team')->findOrFail($id);
        $appointment = $quotation->appointment;
        $admin = Auth::user();

        if ($admin->role->name !== 'admin') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        // Mantener status actual, el cambio a para_ejecucion se hace cuando se asigna precio
        $appointment->update(['status' => 'cotizada']);

        // Notificar al cliente que la cotizacion fue validada por admin y ya puede revisarla.
        Notification::create([
            'user_id' => $appointment->client_id,
            'type' => 'quotation_ready',
            'title' => 'Cotización lista para tu aprobación',
            'message' => "Tu cotización fue validada por administración: {$quotation->labor_hours} horas, {$quotation->required_staff} personal" . ($quotation->price ? ", precio {$quotation->price}" : '') . '. Ya puedes aprobarla o rechazarla.',
            'data' => [
                'appointment_id' => $appointment->id,
                'quotation_id' => $quotation->id,
                'client_name' => $appointment->client->name,
                'team_name' => $appointment->team->name,
                'validated_by' => $admin->name,
                'materials' => $quotation->materials,
                'labor_hours' => $quotation->labor_hours,
                'required_staff' => $quotation->required_staff,
                'price' => $quotation->price,
            ]
        ]);

        return response()->json([
            'message' => 'Cotización aprobada por administración y enviada al cliente',
            'appointment_id' => $appointment->id,
            'quotation_id' => $quotation->id,
        ]);
    }

    public function setPrice(SetPriceRequest $request, $id)
    {
        $quotation = Quotation::with('appointment.client', 'appointment.team')->findOrFail($id);
        $user = Auth::user();

        // Solo el admin puede establecer precio
        if ($user->role->name !== 'admin') {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $quotation->update(['price' => $request->price]);
        
        // Actualizar la fecha programada de la cita y cambiar a para_ejecucion
        $appointment = $quotation->appointment;
        $appointment->update([
            'scheduled_date' => $request->scheduled_date,
            'appointment_type' => 'ejecucion',
            'status' => 'para_ejecucion'
        ]);

        // Cuando se asigna precio, la cotizacion ya se muestra al cliente para aprobar/rechazar.
        Notification::create([
            'user_id' => $appointment->client_id,
            'type' => 'quotation_ready',
            'title' => 'Cotización lista para tu aprobación',
            'message' => "Tu cotización ya tiene precio (COP " . number_format($quotation->price, 0) . ") y está lista para que la apruebes o rechaces.",
            'data' => [
                'appointment_id' => $appointment->id,
                'quotation_id' => $quotation->id,
                'team_name' => $appointment->team->name,
                'materials' => $quotation->materials,
                'labor_hours' => $quotation->labor_hours,
                'required_staff' => $quotation->required_staff,
                'price' => $quotation->price,
                'scheduled_date' => $appointment->scheduled_date,
                'priced_by' => $user->name,
            ]
        ]);

        return response()->json($quotation);
    }

    public function approve($id)
    {
        $quotation = Quotation::findOrFail($id);
        $appointment = $quotation->appointment;

        if ($appointment->client_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        if (is_null($quotation->price)) {
            return response()->json(['error' => 'La cotización aún no tiene precio asignado por administración'], 400);
        }

        // marca la cotización como aprobada
        // El status se mantiene en 'para_ejecucion' para que el técnico ejecute
        $quotation->update(['approved_at' => Carbon::now()]);
        // No cambiar el status, se mantiene en 'para_ejecucion'

        // Notificar al equipo que su cotización fue aprobada
        if ($appointment->team) {
            $teamMembers = $appointment->team->members()->get();
            foreach ($teamMembers as $member) {
                Notification::create([
                    'user_id' => $member->id,
                    'type' => 'quotation_approved',
                    'title' => 'Cotización aprobada',
                    'message' => "El cliente {$appointment->client->name} aprobó la cotización. Para iniciar procede a programar la ejecución.",
                    'data' => [
                        'appointment_id' => $appointment->id,
                        'client_name' => $appointment->client->name,
                        'client_id' => $appointment->client_id,
                        'approved_by' => $appointment->client->name,
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
            'message' => 'Has aprobado la cotización. El técnico procederá con la ejecución en la fecha programada.',
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

        if (is_null($quotation->price)) {
            return response()->json(['error' => 'La cotización aún no tiene precio asignado por administración'], 400);
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
                    'message' => "El cliente {$appointment->client->name} rechazó la cotización. Puedes contactarlo para revisar los términos.",
                    'data' => [
                        'appointment_id' => $appointment->id,
                        'client_name' => $appointment->client->name,
                        'client_id' => $appointment->client_id,
                        'rejected_by' => $appointment->client->name
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