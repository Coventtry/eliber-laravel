<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prestamos', function (Blueprint $table) {
            $table->unsignedBigInteger('ejemplar_id')->nullable()->after('material_id');
            $table->foreign('ejemplar_id')->references('id')->on('material_ejemplares')->nullOnDelete();
            $table->index('ejemplar_id');
        });
    }

    public function down(): void
    {
        Schema::table('prestamos', function (Blueprint $table) {
            $table->dropForeign(['ejemplar_id']);
            $table->dropIndex(['ejemplar_id']);
            $table->dropColumn('ejemplar_id');
        });
    }
};
