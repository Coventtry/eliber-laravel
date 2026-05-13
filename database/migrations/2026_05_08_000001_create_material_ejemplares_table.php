<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_ejemplares', function (Blueprint $table) {
            $table->id();
            $table->integer('material_id');
            $table->unsignedBigInteger('institucion_id');
            $table->string('codigo_ejemplar')->unique();
            $table->enum('estado', ['disponible', 'prestado', 'reservado', 'baja'])->default('disponible');
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->foreign('material_id')->references('id')->on('materiales')->cascadeOnDelete();
            $table->foreign('institucion_id')->references('id')->on('instituciones');

            $table->index(['material_id', 'estado']);
            $table->index(['institucion_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_ejemplares');
    }
};
