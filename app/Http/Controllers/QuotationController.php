<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\Appointment;
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

        return response()->json($appointment, 200);
    }
}