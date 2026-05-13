<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLES = ['socios', 'materiales', 'prestamos', 'areas', 'noticias', 'anotaciones', 'bibliotecarios', 'users'];

    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        foreach (self::TABLES as $table) {
            if (! Schema::hasColumn($table, 'institucion_id')) {
                continue;
            }

            $fkName = $table.'_institucion_id_foreign';

            try {
                Schema::table($table, function (Blueprint $t) use ($fkName) {
                    $t->dropForeign($fkName);
                });
            } catch (\Exception) {
                continue;
            }

            Schema::table($table, function (Blueprint $t) {
                $t->foreign('institucion_id')
                    ->references('id')
                    ->on('instituciones')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        foreach (self::TABLES as $table) {
            if (! Schema::hasColumn($table, 'institucion_id')) {
                continue;
            }

            $fkName = $table.'_institucion_id_foreign';

            try {
                Schema::table($table, function (Blueprint $t) use ($fkName) {
                    $t->dropForeign($fkName);
                });
            } catch (\Exception) {
                continue;
            }

            Schema::table($table, function (Blueprint $t) {
                $t->foreign('institucion_id')->references('id')->on('instituciones');
            });
        }
    }
};
