<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci');
        }

        if (Schema::hasColumn('socios', 'activo')) {
            Schema::table('socios', function (Blueprint $table) {
                $table->tinyInteger('activo')->default(1)->change();
            });
        }

        if (Schema::hasColumn('prestamos', 'estado')) {
            Schema::table('prestamos', function (Blueprint $table) {
                $table->string('estado', 50)->default('activo')->change();
            });
        }

        if (Schema::hasColumn('prestamos', 'cantidad')) {
            Schema::table('prestamos', function (Blueprint $table) {
                $table->integer('cantidad')->default(1)->change();
            });
        }

        if (Schema::hasColumn('materiales', 'disponibilidad')) {
            Schema::table('materiales', function (Blueprint $table) {
                $table->integer('disponibilidad')->default(0)->change();
            });
        }

        if ($driver === 'mysql') {
            $col = DB::selectOne("SELECT COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='prestamos' AND COLUMN_NAME='material_id'");
            if ($col && str_contains(strtolower($col->COLUMN_TYPE), 'unsigned')) {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
                DB::statement('ALTER TABLE prestamos MODIFY material_id INT NULL');
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
        }
    }

    public function down(): void {}
};
