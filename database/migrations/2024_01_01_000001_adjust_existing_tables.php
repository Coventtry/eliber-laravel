<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Ajusta tablas existentes para compatibilidad con MySQL 8.0.
 * Usa Schema::table — no destruye datos.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Asegurar charset utf8mb4 en conexión
        DB::statement("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");

        // socios: asegurar columna activo con default 1
        if (Schema::hasColumn('socios', 'activo')) {
            Schema::table('socios', function (Blueprint $table) {
                $table->tinyInteger('activo')->default(1)->change();
            });
        }

        // prestamos: asegurar campo estado tiene default 'activo'
        if (Schema::hasColumn('prestamos', 'estado')) {
            Schema::table('prestamos', function (Blueprint $table) {
                $table->string('estado', 50)->default('activo')->change();
            });
        }

        // prestamos: asegurar campo cantidad tiene default 1
        if (Schema::hasColumn('prestamos', 'cantidad')) {
            Schema::table('prestamos', function (Blueprint $table) {
                $table->integer('cantidad')->default(1)->change();
            });
        }

        // materiales: asegurar disponibilidad no null
        if (Schema::hasColumn('materiales', 'disponibilidad')) {
            Schema::table('materiales', function (Blueprint $table) {
                $table->integer('disponibilidad')->default(0)->change();
            });
        }

        // prestamos.material_id: el dump original usaba INT UNSIGNED pero materiales.id es INT (signed)
        // MySQL 8/9 es estricto con tipos en FK. Corregir si todavía es unsigned.
        $col = DB::selectOne("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='prestamos' AND COLUMN_NAME='material_id'");
        if ($col && str_contains(strtolower($col->COLUMN_TYPE), 'unsigned')) {
            DB::statement("SET FOREIGN_KEY_CHECKS=0");
            DB::statement("ALTER TABLE prestamos MODIFY material_id INT NULL");
            DB::statement("SET FOREIGN_KEY_CHECKS=1");
        }
    }

    public function down(): void
    {
        // No reversible — solo ajusta defaults, no altera datos
    }
};
