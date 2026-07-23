<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('perfil_cuentas', function (Blueprint $table) {
            $table->id();
            // Relación con la compra (cuenta completa comprada)
            $table->foreignId('compra_id')->constrained('compras')->onDelete('cascade');

            $table->string('nombre_perfil'); // Ej: "Perfil 1", "Perfil 2"
            $table->string('pin', 4)->nullable();
            $table->string('dispositivo_autorizado')->nullable();
            $table->enum('estado', ['disponible', 'vendido', 'mantenimiento'])->default('disponible');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perfil_cuentas');
    }
};
