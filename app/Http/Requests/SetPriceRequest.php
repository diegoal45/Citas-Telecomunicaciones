<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetPriceRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->role->name === 'admin';
    }

    public function rules()
    {
        return [
            'price' => 'required|numeric|min:0',
            'scheduled_date' => 'required|date|after:now'
        ];
    }

    public function messages()
    {
        return [
            'price.required' => 'El precio es obligatorio',
            'price.min' => 'El precio debe ser mayor o igual a 0',
            'scheduled_date.required' => 'La fecha de ejecución es obligatoria',
            'scheduled_date.date' => 'La fecha no es válida',
            'scheduled_date.after' => 'La fecha debe ser posterior a ahora'
        ];
    }
}