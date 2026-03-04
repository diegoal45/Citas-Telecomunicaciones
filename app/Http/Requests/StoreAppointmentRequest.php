<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->role->name === 'cliente';
    }

    public function rules()
    {
        return [
            'scheduled_date' => 'required|date|after:now',
            'address' => 'required|string',
            'description' => 'nullable|string'
        ];
    }

    public function messages()
    {
        return [
            'scheduled_date.required' => 'La fecha y hora son obligatorias',
            'scheduled_date.after' => 'La fecha debe ser posterior a ahora',
            'address.required' => 'La dirección es obligatoria'
        ];
    }
}