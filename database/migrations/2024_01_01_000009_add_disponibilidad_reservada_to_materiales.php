<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('materiales', 'disponibilidad_reservada')) {
            return;
        }

        Schema::table('materiales', function (Blueprint $table) {
            $table->integer('disponibilidad_reservada')->default(0)->after('disponibilidad');
        });
    }

    public function down(): void
    {
        Schema::table('materiales', function (Blueprint $table) {
            $table->dropColumn('disponibilidad_reservada');
        });
    }
};