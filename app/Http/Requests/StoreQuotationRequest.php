<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'labor_hours' => 'required|numeric|min:0',
            'required_staff' => 'required|integer|min:1'
        ];
    }

    public function messages()
    {
        return [
            'appointment_id.required' => 'La cita es obligatoria',
            'labor_hours.required' => 'Las horas hombre son obligatorias',
            'labor_hours.min' => 'Las horas hombre deben ser mayores a 0',
            'required_staff.required' => 'El personal requerido es obligatorio',
            'required_staff.min' => 'El personal requerido debe ser al menos 1'
        ];
    }
}