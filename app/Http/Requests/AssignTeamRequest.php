<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignTeamRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->role->name === 'admin';
    }

    public function rules()
    {
        return [
            'team_id' => 'required|exists:teams,id'
        ];
    }

    public function messages()
    {
        return [
            'team_id.required' => 'Debe seleccionar un equipo',
            'team_id.exists' => 'El equipo seleccionado no existe'
        ];
    }
}