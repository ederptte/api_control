<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVentaRequest extends FormRequest
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
            'cliente_id'             => ['required', 'exists:clientes,id'],
            'perfil_cuenta_id'       => ['required', 'exists:perfil_cuentas,id'],
            'precio_venta'           => ['required', 'numeric', 'min:0'],
            'fecha_venta'            => ['required', 'date'],
            'pin'                    => ['nullable', 'string', 'max:4'],
            'dispositivo_autorizado' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.required'       => 'Error: Debes seleccionar un cliente.',
            'cliente_id.exists'         => 'Error: El cliente seleccionado no existe.',
            'perfil_cuenta_id.required' => 'Error: Debes seleccionar un perfil.',
            'perfil_cuenta_id.exists'   => 'Error: El perfil seleccionado no existe.',
            'precio_venta.required'     => 'Error: El precio de venta es obligatorio.',
            'precio_venta.numeric'      => 'Error: El precio de venta debe ser un número.',
            'precio_venta.min'          => 'Error: El precio de venta no puede ser negativo.',
            'fecha_venta.required'      => 'Error: La fecha de venta es obligatoria.',
            'fecha_venta.date'          => 'Error: La fecha de venta no es válida.',
            'pin.max'                   => 'Error: El PIN no puede superar los 4 caracteres.',
            'dispositivo_autorizado.max'=> 'Error: El dispositivo autorizado no puede superar los 255 caracteres.',
        ];
    }
}
