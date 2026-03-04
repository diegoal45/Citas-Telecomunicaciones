<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use App\Models\Team;

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

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $teamId = $this->route('id');
            if ($this->has('member_ids')) {
                foreach ($this->member_ids as $memberId) {
                    $member = User::find($memberId);
                    if ($member) {
                        // Check if user is leader of this team
                        if ($teamId && $member->ledTeams()->where('teams.id', $teamId)->exists()) {
                            $validator->errors()->add('member_ids', 'El usuario con id ' . $memberId . ' es líder de este equipo y no puede ser miembro');
                            continue;
                        }
                        
                        // Check if user is leader of any other team
                        if ($member->ledTeams()->exists()) {
                            $validator->errors()->add('member_ids', 'El usuario con id ' . $memberId . ' es líder de otro equipo y no puede ser miembro');
                            continue;
                        }
                        
                        // Check if user is member of another team (excluding current if exists)
                        if ($teamId) {
                            if ($member->teams()->where('team_id', '<>', $teamId)->exists()) {
                                $validator->errors()->add('member_ids', 'El usuario con id ' . $memberId . ' ya pertenece a otro equipo');
                            }
                        } else {
                            if ($member->teams()->exists()) {
                                $validator->errors()->add('member_ids', 'El usuario con id ' . $memberId . ' ya pertenece a otro equipo');
                            }
                        }
                    }
                }
            }
        });
    }
}