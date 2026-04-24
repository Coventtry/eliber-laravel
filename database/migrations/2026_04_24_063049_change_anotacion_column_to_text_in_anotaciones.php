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
        Schema::table('anotaciones', function (Blueprint $table) {
            $table->text('anotacion')->change();
        });
    }

    public function down(): void
    {
        Schema::table('anotaciones', function (Blueprint $table) {
            $table->string('anotacion', 255)->change();
        });
    }
};
