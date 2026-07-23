<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class PerfilCuenta extends Model
{
    use HasFactory;

    protected $fillable = [
        'compra_id',
        'nombre_perfil',
        'pin',
        'dispositivo_autorizado',
        'estado'
    ];

    // Relación: Un perfil pertenece a una compra específica
    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    // Relación: Un perfil puede tener un historial de muchas ventas (transacciones)
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'perfil_cuenta_id');
    }
}
