<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RenovarCompraRequest extends FormRequest
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
            'precio_compra' => ['required', 'numeric', 'min:0'],
            'fecha_compra'  => ['required', 'date'],
            'nota'          => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'precio_compra.required' => 'Error: El precio de la renovación es obligatorio.',
            'precio_compra.numeric'  => 'Error: El precio de la renovación debe ser un número.',
            'precio_compra.min'      => 'Error: El precio de la renovación no puede ser negativo.',
            'fecha_compra.required'  => 'Error: La fecha de la renovación es obligatoria.',
            'fecha_compra.date'      => 'Error: La fecha de la renovación no es válida.',
        ];
    }
}
