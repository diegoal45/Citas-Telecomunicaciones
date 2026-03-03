<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeamRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->role->name === 'admin';
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'leader_id' => 'required|exists:users,id',
            'member_ids' => 'sometimes|array',
            'member_ids.*' => 'exists:users,id'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre del equipo es obligatorio',
            'leader_id.required' => 'Debe seleccionar un líder',
            'leader_id.exists' => 'El líder seleccionado no existe',
            'member_ids.*.exists' => 'Uno de los miembros seleccionados no existe'
        ];
    }
}