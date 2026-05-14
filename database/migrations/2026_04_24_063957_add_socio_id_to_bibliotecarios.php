<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('bibliotecarios')) {
            return;
        }
        Schema::table('bibliotecarios', function (Blueprint $table) {
            $table->integer('socio_id')->nullable()->after('institucion_id');
            $table->foreign('socio_id')->references('id')->on('socios')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bibliotecarios', function (Blueprint $table) {
            $table->dropForeign(['socio_id']);
            $table->dropColumn('socio_id');
        });
    }
};
