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
            $table->foreignId('socio_id')->constrained('socios');
            $table->foreignId('prestamo_id')->nullable()->constrained('prestamos')->nullOnDelete();
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
