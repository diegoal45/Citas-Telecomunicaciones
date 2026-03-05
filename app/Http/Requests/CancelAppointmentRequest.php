<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelAppointmentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'reason' => 'required|string|max:255'
        ];
    }

    public function messages()
    {
        return [
            'reason.required' => 'La razón de cancelación es obligatoria',
            'reason.string' => 'La razón debe ser texto',
            'reason.max' => 'La razón no debe exceder 255 caracteres'
        ];
    }
}