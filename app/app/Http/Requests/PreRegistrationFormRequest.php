<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PreRegistrationFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'site' => ['required', 'in:asuncion,fernando-de-la-mora'],
            'message' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'email' => 'correo',
            'phone' => 'teléfono',
            'site' => 'sede',
            'message' => 'mensaje',
        ];
    }
}
