<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        if (! Schema::hasTable('prestamos')) {
            return;
        }

        try {
            Schema::table('prestamos', function (Blueprint $table) {
                $table->dropForeignIfExists('prestamos_socio_id_foreign');
            });
        } catch (\Exception) {
            return;
        }

        Schema::table('prestamos', function (Blueprint $table) {
            $table->foreign('socio_id')
                ->references('id')
                ->on('socios')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        if (! Schema::hasTable('prestamos')) {
            return;
        }

        try {
            Schema::table('prestamos', function (Blueprint $table) {
                $table->dropForeignIfExists('prestamos_socio_id_foreign');
            });
        } catch (\Exception) {
            return;
        }

        Schema::table('prestamos', function (Blueprint $table) {
            $table->foreign('socio_id')->references('id')->on('socios');
        });
    }
};
