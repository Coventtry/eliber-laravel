<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! $this->fkExists('prestamos', 'prestamos_socio_id_foreign')) {
            Schema::table('prestamos', function (Blueprint $table) {
                $table->foreign('socio_id')->references('id')->on('socios');
            });
        }

        if (! $this->fkExists('materiales', 'materiales_area_id_foreign')) {
            Schema::table('materiales', function (Blueprint $table) {
                $table->foreign('area_id')->references('id')->on('areas');
            });
        }
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

    private function fkExists(string $table, string $name): bool
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return false;
        }

        $result = DB::select("
            SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND CONSTRAINT_NAME = ?
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ", [$table, $name]);

        return ! empty($result);
    }
};
