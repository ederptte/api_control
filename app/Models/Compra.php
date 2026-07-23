<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Compra extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'compras';

    protected $fillable = [
        'cuenta_id',
        'precio_compra',
        'fecha_compra',
        'pantallas',
        'nota',
        'estado',
        'dias_duracion',
        'fecha_vencimiento',
    ];

    protected $casts = [
        'fecha_compra' => 'date',
        'fecha_vencimiento' => 'date',
    ];

    /**
     * Calcula automáticamente fecha_vencimiento = fecha_compra + dias_duracion,
     * solo si no viene ya definida manualmente.
     */
    protected static function booted()
    {
        static::creating(function ($compra) {
            if ($compra->fecha_compra && $compra->dias_duracion && !$compra->fecha_vencimiento) {
                $compra->fecha_vencimiento = Carbon::parse($compra->fecha_compra)
                    ->addDays($compra->dias_duracion)
                    ->format('Y-m-d');
            }
        });
    }

    public function getPantallasDisponiblesAttribute()
    {
        if (array_key_exists('pantallas_vendidas', $this->attributes)) {
            return $this->pantallas - ($this->attributes['pantallas_vendidas'] ?? 0);
        }

        return $this->pantallas - $this->ventas()->count();
    }

    /**
     * Fecha de vencimiento más próxima entre los perfiles vendidos de esta compra.
     * (Vencimiento a nivel de cada cliente/venta, distinto del vencimiento de la cuenta misma)
     */
    public function getProximoVencimientoAttribute()
    {
        return $this->perfilCuentas()
            ->where('estado', 'vendido')
            ->join('ventas', 'ventas.perfil_cuenta_id', '=', 'perfil_cuentas.id')
            ->whereNull('ventas.deleted_at')
            ->min('ventas.fecha_vencimiento');
    }

    public function perfilCuentas()
    {
        return $this->hasMany(PerfilCuenta::class, 'compra_id');
    }

    public function cuenta()
    {
        return $this->belongsTo(Cuenta::class);
    }

    public function ventas()
    {
        return $this->hasManyThrough(
            Venta::class,
            PerfilCuenta::class,
            'compra_id',
            'perfil_cuenta_id',
            'id',
            'id'
        );
    }
}
