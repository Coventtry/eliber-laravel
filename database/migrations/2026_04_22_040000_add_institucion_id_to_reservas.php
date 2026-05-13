<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('reservas', 'institucion_id')) {
            Schema::table('reservas', function (Blueprint $table) {
                $table->unsignedBigInteger('institucion_id')->nullable()->after('id');
            });

            $institucionId = $this->getDefaultInstitucionId();
            DB::table('reservas')->whereNull('institucion_id')->update(['institucion_id' => $institucionId]);

            Schema::table('reservas', function (Blueprint $table) {
                $table->unsignedBigInteger('institucion_id')->nullable(false)->change();
                $table->foreign('institucion_id')->references('id')->on('instituciones')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropForeign(['institucion_id']);
            $table->dropColumn('institucion_id');
        });
    }

    private function getDefaultInstitucionId(): int
    {
        $institucion = DB::table('instituciones')->first();

        return $institucion?->id ?? 1;
    }
};
