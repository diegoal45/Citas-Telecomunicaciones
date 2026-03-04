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
        
        if ($appointment->team->leader_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $quotation = Quotation::updateOrCreate(
            ['appointment_id' => $request->appointment_id],
            $request->validated()
        );

        $appointment->update(['status' => 'cotizada']);

        return response()->json($quotation, 201);
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

        $quotation->update(['approved_at' => Carbon::now()]);
        $appointment->update([
            'status' => 'aprobada',
            'appointment_type' => 'ejecucion'
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

        $executionAppointment = Appointment::create([
            'client_id' => $appointment->client_id,
            'team_id' => $request->team_id,
            'appointment_type' => 'ejecucion',
            'status' => 'programada',
            'scheduled_date' => $request->scheduled_date,
            'address' => $appointment->address,
            'phone' => $appointment->phone,
            'description' => $appointment->description
        ]);

        return response()->json($executionAppointment, 201);
    }
}