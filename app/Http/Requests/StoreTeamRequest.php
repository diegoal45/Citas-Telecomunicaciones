<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;

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

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('leader_id')) {
                $leader = User::find($this->leader_id);
                if (!$leader || $leader->id_rol != 2) {
                    $validator->errors()->add('leader_id', 'El líder debe ser un técnico líder (rol=2)');
                }
                if ($leader && $leader->teams()->exists()) {
                    $validator->errors()->add('leader_id', 'El líder no puede ser miembro de otro equipo');
                }
            }

            if ($this->has('member_ids')) {
                foreach ($this->member_ids as $memberId) {
                    if ($this->has('leader_id') && $memberId == $this->leader_id) {
                        $validator->errors()->add('member_ids', 'El líder no puede estar listado como miembro');
                        break;
                    }

                    $member = User::find($memberId);
                    if ($member) {
                        if ($member->ledTeams()->exists()) {
                            $validator->errors()->add('member_ids', 'El usuario con id ' . $memberId . ' es líder de otro equipo y no puede ser miembro');
                        } elseif ($member->teams()->exists()) {
                            $validator->errors()->add('member_ids', 'El usuario con id ' . $memberId . ' ya pertenece a otro equipo');
                        }
                    }
                }
            }
        });
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