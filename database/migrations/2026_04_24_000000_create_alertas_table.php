<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->constrained('instituciones');
            $table->integer('prestamo_id')->nullable();
            $table->foreign('prestamo_id')->references('id')->on('prestamos')->nullOnDelete();
            $table->enum('tipo', ['proximo_vencer', 'vencido', 'renovacion']);
            $table->string('descripcion');
            $table->datetime('fecha_alerta');
            $table->boolean('leida')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas');
    }
};
