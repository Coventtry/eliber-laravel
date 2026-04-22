<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('reservas')) {
            return;
        }

        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->integer('material_id');
            $table->integer('socio_id');
            $table->enum('estado', ['pendiente', 'aprobada', 'rechazada', 'expirada'])->default('pendiente');
            $table->dateTime('fecha_reserva');
            $table->dateTime('fecha_vencimiento')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('material_id')->references('id')->on('materiales')->cascadeOnDelete();
            $table->foreign('socio_id')->references('id')->on('socios')->cascadeOnDelete();
            $table->index(['material_id', 'estado']);
            $table->index(['socio_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
