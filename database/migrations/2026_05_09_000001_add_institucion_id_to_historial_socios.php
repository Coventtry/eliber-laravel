<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('historial_socios', 'institucion_id')) {
            Schema::table('historial_socios', function (Blueprint $table) {
                $table->unsignedBigInteger('institucion_id')->nullable()->after('id_socio');

                $table->foreign('institucion_id')->references('id')->on('instituciones');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('historial_socios', 'institucion_id')) {
            Schema::table('historial_socios', function (Blueprint $table) {
                $table->dropForeign(['institucion_id']);
                $table->dropColumn('institucion_id');
            });
        }
    }
};
