<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('multas', function (Blueprint $table) {
            $table->id();
            $table->integer('socio_id');
            $table->foreign('socio_id')->references('id')->on('socios');
            $table->integer('prestamo_id')->nullable();
            $table->foreign('prestamo_id')->references('id')->on('prestamos')->nullOnDelete();
            $table->decimal('monto', 10, 2);
            $table->string('motivo');
            $table->text('observaciones')->nullable();
            $table->boolean('pagada')->default(false);
            $table->date('fecha_pago')->nullable();
            $table->date('fecha_creacion');
            $table->foreignId('institucion_id')->constrained('instituciones');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('multas');
    }
};
