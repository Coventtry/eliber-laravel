<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Agrega las FK que faltan en el dump original.
 * Verifica la existencia antes de agregar para ser idempotente.
 */
return new class extends Migration
{
    public function up(): void
    {
        // prestamos → socios
        $this->addFkIfMissing('prestamos', 'prestamos_socio_id_foreign', function (Blueprint $table) {
            $table->foreign('socio_id')->references('id')->on('socios');
        });

        // prestamos → materiales: omitido — el dump original tiene material_id INT UNSIGNED
        // pero materiales.id es INT (signed). MySQL 9 rechaza FK entre tipos distintos y PDO
        // no permite SET FOREIGN_KEY_CHECKS=0 + ALTER en la misma sesión de migración.
        // La integridad se mantiene a nivel Eloquent (belongsTo).

        // materiales → areas
        $this->addFkIfMissing('materiales', 'materiales_area_id_foreign', function (Blueprint $table) {
            $table->foreign('area_id')->references('id')->on('areas');
        });
    }

    public function down(): void
    {
        Schema::table('prestamos', function (Blueprint $table) {
            $table->dropForeignIfExists('prestamos_socio_id_foreign');
        });
        Schema::table('materiales', function (Blueprint $table) {
            $table->dropForeignIfExists('materiales_area_id_foreign');
        });
    }

    private function addFkIfMissing(string $tableName, string $fkName, callable $callback): void
    {
        $exists = DB::select("
            SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND CONSTRAINT_NAME = ?
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ", [$tableName, $fkName]);

        if (empty($exists)) {
            Schema::table($tableName, $callback);
        }
    }
};
