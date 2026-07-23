<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCuentaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'plataforma'    => ['required', 'string', 'max:255'],
            'correo'        => ['required', 'email'],
            'clave'         => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'plataforma.required'   => 'Error: El campo plataforma no puede estar vacío.',
            'plataforma.max' => 'Error: El campo plataforma no puede superar los 255 caracteres.',
            'correo.required'       => 'Error: El campo correo no puede estar vacío.',
            'correo.email'          => 'Error: El correo no es válido.',
            'clave.required'   => 'Error: El campo clave no puede estar vacio.',
            'clave.string'   => 'Error: El campo clave debe ser texto.',

        ];
    }
}
