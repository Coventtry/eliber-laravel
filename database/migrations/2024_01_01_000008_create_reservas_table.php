<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained()->onDelete('cascade');
            $table->foreignId('socio_id')->constrained('socios')->onDelete('cascade');
            $table->enum('estado', ['pendiente', 'aprobada', 'rechazada', 'expirada'])->default('pendiente');
            $table->dateTime('fecha_reserva');
            $table->dateTime('fecha_vencimiento')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['material_id', 'estado']);
            $table->index(['socio_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};