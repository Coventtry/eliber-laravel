<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias_fisicas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->unsignedBigInteger('institucion_id');
            $table->foreign('institucion_id')->references('id')->on('instituciones');
            $table->unique(['nombre', 'institucion_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias_fisicas');
    }
};
