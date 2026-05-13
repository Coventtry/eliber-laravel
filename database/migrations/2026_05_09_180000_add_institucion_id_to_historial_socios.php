<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('historial_socios', 'institucion_id')) {
            Schema::table('historial_socios', function (Blueprint $table) {
                $table->unsignedBigInteger('institucion_id')->nullable()->after('id');
            });

            $first = DB::table('instituciones')->first();
            if ($first) {
                DB::table('historial_socios')->whereNull('institucion_id')->update(['institucion_id' => $first->id]);
            }

            Schema::table('historial_socios', function (Blueprint $table) {
                $table->unsignedBigInteger('institucion_id')->nullable(false)->change();
                $table->foreign('institucion_id')->references('id')->on('instituciones');
            });
        }
    }

    public function down(): void
    {
        Schema::table('historial_socios', function (Blueprint $table) {
            $table->dropForeign(['institucion_id']);
            $table->dropColumn('institucion_id');
        });
    }
};
