<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RescheduleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'new_date' => 'required|date|after:now'
        ];
    }

    public function messages()
    {
        return [
            'new_date.required' => 'La nueva fecha es obligatoria',
            'new_date.after' => 'La fecha debe ser posterior a ahora'
        ];
    }
}