<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('apellido', 100)->nullable()->after('nombre');
            $table->unsignedTinyInteger('anio')->nullable()->after('apellido');
            $table->unsignedTinyInteger('division')->nullable()->after('anio');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['apellido', 'anio', 'division']);
        });
    }
};
