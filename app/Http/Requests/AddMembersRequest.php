<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddMembersRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->role->name === 'admin';
    }

    public function rules()
    {
        return [
            'member_ids' => 'required|array',
            'member_ids.*' => 'exists:users,id'
        ];
    }

    public function messages()
    {
        return [
            'member_ids.required' => 'Debe seleccionar al menos un miembro',
            'member_ids.array' => 'Formato inválido',
            'member_ids.*.exists' => 'Uno de los miembros seleccionados no existe'
        ];
    }
}