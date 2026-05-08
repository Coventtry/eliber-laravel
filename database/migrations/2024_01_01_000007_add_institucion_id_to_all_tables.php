<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $institucionId = $this->getOrCreateInstitucion();

        $tables = ['socios', 'materiales', 'prestamos', 'areas', 'noticias', 'anotaciones', 'bibliotecarios'];

        foreach ($tables as $table) {
            if (! Schema::hasColumn($table, 'institucion_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->unsignedBigInteger('institucion_id')->nullable()->after('id');
                });

                DB::table($table)->whereNull('institucion_id')->update(['institucion_id' => $institucionId]);

                Schema::table($table, function (Blueprint $t) {
                    $t->unsignedBigInteger('institucion_id')->nullable(false)->change();
                    $t->foreign('institucion_id')->references('id')->on('instituciones');
                });
            }
        }

        if (! Schema::hasColumn('users', 'institucion_id')) {
            Schema::table('users', function (Blueprint $t) {
                $t->unsignedBigInteger('institucion_id')->nullable()->after('activo');
            });

            DB::table('users')->whereNull('institucion_id')->update(['institucion_id' => $institucionId]);

            Schema::table('users', function (Blueprint $t) {
                $t->unsignedBigInteger('institucion_id')->nullable(false)->change();
                $t->foreign('institucion_id')->references('id')->on('instituciones');
            });
        }
    }

    public function down(): void
    {
        $tables = ['socios', 'materiales', 'prestamos', 'areas', 'noticias', 'anotaciones', 'bibliotecarios', 'users'];
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropForeign(['institucion_id']);
                $t->dropColumn('institucion_id');
            });
        }
    }

    private function getOrCreateInstitucion(): int
    {
        $existing = DB::table('instituciones')->first();
        if ($existing) {
            return $existing->id;
        }

        return DB::table('instituciones')->insertGetId([
            'nombre' => 'Institución Principal',
            'slug' => 'principal',
            'estado' => 'activa',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
