<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Appointment;

class StoreQuotationRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->role->name === 'tecnico_lider';
    }

    public function rules()
    {
        return [
            'appointment_id' => 'required|exists:appointments,id',
            'materials' => 'nullable|string',
            'labor_hours' => 'required|numeric|min:0.1',
            'required_staff' => 'required|integer|min:1'
        ];
    }

    public function messages()
    {
        return [
            'appointment_id.required' => 'La cita es obligatoria',
            'appointment_id.exists' => 'La cita seleccionada no existe',
            'labor_hours.required' => 'Las horas hombre son obligatorias',
            'labor_hours.numeric' => 'Las horas hombre deben ser un número',
            'labor_hours.min' => 'Las horas hombre deben ser mayores a 0',
            'required_staff.required' => 'El personal requerido es obligatorio',
            'required_staff.integer' => 'El personal requerido debe ser un número entero',
            'required_staff.min' => 'El personal requerido debe ser al menos 1'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $appointmentId = $this->appointment_id;
            $appointment = Appointment::find($appointmentId);

            if (!$appointment) {
                $validator->errors()->add('appointment_id', 'La cita seleccionada no existe');
                return;
            }

            // Verificar que la cita sea del equipo del técnico líder
            $user = auth()->user();
            $ledTeamIds = $user->ledTeams()->pluck('id')->toArray();

            if (!in_array($appointment->team_id, $ledTeamIds)) {
                $validator->errors()->add('appointment_id', 'No tienes permiso para cotizar esta cita. Solo puedes cotizar citas de tus equipos');
                return;
            }

            // Verificar que la cita esté en estado válido para cotizar
            if (!in_array($appointment->status, ['solicitada', 'pendiente_cotizacion'])) {
                $validator->errors()->add('appointment_id', 'Esta cita no puede ser cotizada. Estado actual: ' . $appointment->status);
                return;
            }

            // Verificar que no haya una cotización previa
            if ($appointment->quotation) {
                $validator->errors()->add('appointment_id', 'Esta cita ya tiene una cotización');
                return;
            }
        });
    }
}
