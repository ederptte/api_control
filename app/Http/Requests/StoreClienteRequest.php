<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreClienteRequest extends FormRequest
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
            'nombre'   => ['required', 'string', 'max:255'],
            'whatsapp' => ['required', 'digits:10', 'unique:clientes,whatsapp'],
            'email'    => ['required', 'email', 'unique:clientes,email'],
        ];
    }
    public function messages(): array
    {
        return [
            'nombre.required'   => 'Error: El campo nombre no puede estar vacío.',
            'whatsapp.required' => 'Error: El campo WhatsApp no puede estar vacío.',
            'whatsapp.digits'   => 'Error: El WhatsApp debe tener exactamente 10 dígitos.',
            'whatsapp.unique'   => 'Error: El WhatsApp ingresado ya existe.',
            'email.required'    => 'Error: El campo email no puede estar vacío.',
            'email.email'       => 'Error: El email no es válido.',
            'email.unique'      => 'Error: El email ya existe.',
        ];
    }
}
