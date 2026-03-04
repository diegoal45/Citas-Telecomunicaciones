<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;

class UpdateTeamRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->role->name === 'admin';
    }

    public function rules()
    {
        return [
            'name' => 'sometimes|string|max:255',
            'leader_id' => 'sometimes|exists:users,id'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('leader_id')) {
                $leader = User::find($this->leader_id);
                if (!$leader || $leader->id_rol != 2) {
                    $validator->errors()->add('leader_id', 'El líder debe ser un técnico líder (rol=2)');
                }
                if ($leader && $leader->teams()->exists()) {
                    $validator->errors()->add('leader_id', 'El líder no puede ser miembro de un equipo');
                }
            }
        });
    }
}