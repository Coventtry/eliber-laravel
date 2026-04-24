<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instituciones', function (Blueprint $table) {
            $table->string('anuncio_texto')->nullable()->after('estado');
            $table->enum('anuncio_estilo', ['warning', 'danger', 'info', 'success'])->default('info')->after('anuncio_texto');
            $table->boolean('anuncio_activo')->default(false)->after('anuncio_estilo');
        });
    }

    public function down(): void
    {
        Schema::table('instituciones', function (Blueprint $table) {
            $table->dropColumn(['anuncio_texto', 'anuncio_estilo', 'anuncio_activo']);
        });
    }
};
