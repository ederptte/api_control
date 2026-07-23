<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VentaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'precio_venta'      => $this->precio_venta,
            'fecha_venta'       => $this->fecha_venta->format('d/m/Y'),
            'fecha_vencimiento' => $this->fecha_vencimiento->format('d/m/Y'),

            'cliente' => [
                'id'       => $this->cliente->id,
                'nombre'   => $this->cliente->nombre,
                'whatsapp' => $this->cliente->whatsapp, // NUEVO
            ],

            'perfil' => [
                'id'             => $this->perfilCuenta->id,
                'nombre_perfil'  => $this->perfilCuenta->nombre_perfil,
                'estado'         => $this->perfilCuenta->estado,
                'pin'            => $this->perfilCuenta->pin,
                'plataforma'     => $this->perfilCuenta->compra->cuenta->plataforma ?? null,
                'correo'         => $this->perfilCuenta->compra->cuenta->correo ?? null,
                'clave'          => $this->perfilCuenta->compra->cuenta->clave ?? null, // NUEVO
            ],
        ];
    }
}
