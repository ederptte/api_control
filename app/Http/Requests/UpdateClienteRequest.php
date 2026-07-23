<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;



class UpdateClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Obtenemos el ID del cliente que viene de forma dinámica en la ruta URL
        // Esto funciona gracias al Route Model Binding
        $cliente = $this->route('cliente');

        return [
            'nombre'   => ['required', 'string', 'max:255'],

            // El porqué de esta sintaxis:
            // Le decimos que sea único en la tabla 'clientes', columna 'whatsapp',
            // pero que ignore el registro que tenga el ID de este cliente actual.
            'whatsapp' => ['required', 'digits:10', Rule::unique('clientes', 'whatsapp')->ignore($cliente->id)],

            // Hacemos exactamente lo mismo para el email
            'email'    => ['required', 'email', Rule::unique('clientes', 'email')->ignore($cliente->id)],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'   => 'Error: El campo nombre no puede estar vacío.',
            'whatsapp.required' => 'Error: El campo WhatsApp no puede estar vacío.',
            'whatsapp.digits'   => 'Error: El WhatsApp debe tener exactamente 10 dígitos.',
            'whatsapp.unique'   => 'Error: El WhatsApp ingresado ya pertenece a otro cliente.',
            'email.required'    => 'Error: El campo email no puede estar vacío.',
            'email.email'       => 'Error: El email no es válido.',
            'email.unique'      => 'Error: El email ya pertenece a otro cliente.',
        ];
    }
}
