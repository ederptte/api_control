<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;




class Venta extends Model
{

    use HasFactory, SoftDeletes;

    // Campos permitidos para asignación masiva
    protected $fillable = [
        'cliente_id',          // <--- Este era el que causaba el error
        'perfil_cuenta_id',    // ID de la pantalla asignada
        'precio_venta',
        'fecha_venta',
        'fecha_vencimiento',
        'pin',
        
    ];

    protected $casts = [
        'fecha_venta' => 'date',
        'fecha_vencimiento' => 'date',
    ];

    protected static function booted()
    {
        static::creating(function ($venta) {
            if ($venta->fecha_venta && !$venta->fecha_vencimiento) {
                $venta->fecha_vencimiento = Carbon::parse($venta->fecha_venta)
                    ->addDays(30)
                    ->format('Y-m-d');
            }
        });

        static::created(function ($venta) {
            $venta->perfilCuenta()->update(['estado' => 'vendido']);
        });
    }


    // Relación: Una venta pertenece a un cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    // Relación: Una venta se asocia a un perfil de cuenta (pantalla)
    public function perfilCuenta()
    {
        return $this->belongsTo(PerfilCuenta::class, 'perfil_cuenta_id');
    }
}
