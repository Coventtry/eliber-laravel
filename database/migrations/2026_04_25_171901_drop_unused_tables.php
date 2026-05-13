<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('prestamos_detalle');
        Schema::dropIfExists('libros');
        Schema::dropIfExists('notificaciones');
        Schema::dropIfExists('ubicaciones_fisicas');
        Schema::dropIfExists('bibliotecarios');
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Tablas obsoletas — no se restauran
    }
};
