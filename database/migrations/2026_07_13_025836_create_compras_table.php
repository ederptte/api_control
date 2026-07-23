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
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuenta_id')->constrained('cuentas')->onDelete('cascade');
            $table->decimal('precio_compra', 10, 2);
            $table->date('fecha_compra');
            $table->unsignedTinyInteger('pantallas');
            $table->unsignedTinyInteger('dias_duracion')->default(30);
            $table->date('fecha_vencimiento')->nullable();
            $table->text('nota')->nullable();
            $table->string('estado')->default('activa');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};
