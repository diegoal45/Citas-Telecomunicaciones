<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        // lógica de autorización si es necesaria
        return true;
    }

    public function rules()
    {
        $userId = $this->route('id') ?? $this->route('user');

        return [
            'name' => 'sometimes|string|max:255',
            'email' => "sometimes|string|email|max:255|unique:users,email,{$userId}",
            'password' => 'sometimes|string|min:8|confirmed',
            'phone' => "sometimes|string|max:20|unique:users,phone,{$userId}",
            'id_rol' => 'sometimes|exists:roles,id',
        ];
    }

    public function messages()
    {
        return [
            'email.email' => 'El email no es válido',
            'email.unique' => 'El email ya está registrado',
            'phone.unique' => 'El teléfono ya está registrado',
            'phone.max' => 'El teléfono excede la longitud máxima permitida',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
            'id_rol.exists' => 'El rol seleccionado no existe',
        ];
    }
}
