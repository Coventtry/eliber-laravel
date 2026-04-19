<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega remember_token a bibliotecarios — requerido por Authenticatable de Laravel.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('bibliotecarios', 'remember_token')) {
            Schema::table('bibliotecarios', function (Blueprint $table) {
                $table->rememberToken()->after('password');
            });
        }
    }

    public function down(): void
    {
        Schema::table('bibliotecarios', function (Blueprint $table) {
            $table->dropColumn('remember_token');
        });
    }
};
