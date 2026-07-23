<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClienteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Aquí defines el molde o "estilo" de tu JSON
        return [
            'id'                => $this->id,
            'nombre_completo'   => $this->nombre, // Podemos renombrar el campo si queremos
            'whatsapp'          => $this->whatsapp,
            'correo_electronico'=> $this->email,
            // Podemos formatear las fechas para que sean legibles en el frontend
            'fecha_registro'    => $this->created_at?->format('d/m/Y h:i A'),
        ];
    }
}
