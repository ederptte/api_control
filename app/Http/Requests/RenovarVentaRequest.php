<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RenovarVentaRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'cliente_id'       => ['required', 'exists:clientes,id'],
            'perfil_cuenta_id' => ['required', 'exists:perfil_cuentas,id'],
            'precio_venta'     => ['required', 'numeric', 'min:0'],
            'fecha_venta'      => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.required'       => 'Error: Debes indicar el cliente que renueva.',
            'cliente_id.exists'         => 'Error: El cliente indicado no existe.',
            'perfil_cuenta_id.required' => 'Error: Debes indicar el perfil que se renueva.',
            'perfil_cuenta_id.exists'   => 'Error: El perfil indicado no existe.',
            'precio_venta.required'     => 'Error: El precio de la renovación es obligatorio.',
            'precio_venta.numeric'      => 'Error: El precio de la renovación debe ser un número.',
            'precio_venta.min'          => 'Error: El precio de la renovación no puede ser negativo.',
            'fecha_venta.required'      => 'Error: La fecha de renovación es obligatoria.',
            'fecha_venta.date'          => 'Error: La fecha de renovación no es válida.',
        ];
    }
}
