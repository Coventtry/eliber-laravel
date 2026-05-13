<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            $exists = DB::select("
                SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'prestamos'
                  AND CONSTRAINT_NAME = 'prestamos_material_id_foreign'
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            ");

            if (empty($exists)) {
                Schema::table('prestamos', function (Blueprint $table) {
                    $table->foreign('material_id')->references('id')->on('materiales');
                });
            }
        }
    }

    public function down(): void
    {
        Schema::table('prestamos', function (Blueprint $table) {
            $table->dropForeignIfExists('prestamos_material_id_foreign');
        });
    }
};
