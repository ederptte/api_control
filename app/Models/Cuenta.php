<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuenta extends Model
{
    use HasFactory;

    protected $table = 'cuentas';

    protected $fillable = [
        'plataforma',
        'correo',
        'clave'
    ];

    protected $casts = [
        'clave' => 'encrypted',
    ];

    public function compras()
    {
        return $this->hasMany(Compra::class);
    }

    protected function plataforma(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => mb_strtoupper($value, 'UTF-8'),
        );
    }

    protected function correo(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => strtolower($value),
        );
    }
}
