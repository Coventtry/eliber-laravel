<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materiales', function (Blueprint $table) {
            $table->enum('tipo_prestamo', ['Solo consulta', 'Copia única', 'Transitorio'])
                ->nullable()
                ->after('clasificacion_fisica');
        });
    }

    public function down(): void
    {
        Schema::table('materiales', function (Blueprint $table) {
            $table->dropColumn('tipo_prestamo');
        });
    }
};
