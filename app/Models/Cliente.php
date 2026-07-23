<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'clientes';

    protected $fillable = [
        'nombre',
        'whatsapp',
        'email'
    ];

    // Este mutator convierte nombre antes de guardar en la base de datos
    protected function nombre(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => mb_strtoupper($value, 'UTF-8'),
        );
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => strtolower($value),
        );
    }
}
