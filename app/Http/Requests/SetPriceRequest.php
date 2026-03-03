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
            'price' => 'required|numeric|min:0'
        ];
    }

    public function messages()
    {
        return [
            'price.required' => 'El precio es obligatorio',
            'price.min' => 'El precio debe ser mayor o igual a 0'
        ];
    }
}