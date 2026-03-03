<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleExecutionRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->role->name === 'admin';
    }

    public function rules()
    {
        return [
            'scheduled_date' => 'required|date|after:now',
            'team_id' => 'required|exists:teams,id'
        ];
    }

    public function messages()
    {
        return [
            'scheduled_date.required' => 'La fecha es obligatoria',
            'scheduled_date.after' => 'La fecha debe ser posterior a ahora',
            'team_id.required' => 'Debe seleccionar un equipo',
            'team_id.exists' => 'El equipo seleccionado no existe'
        ];
    }
}