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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            // Relación con el cliente que compra
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');

            // Relación directa con el perfil que está ocupando
            $table->foreignId('perfil_cuenta_id')->constrained('perfil_cuentas')->onDelete('cascade');

            $table->decimal('precio_venta', 8, 2);
            $table->date('fecha_venta');
            $table->date('fecha_vencimiento');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
