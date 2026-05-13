<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('alertas', 'tipo')) {
            return;
        }

        Schema::table('alertas', function (Blueprint $table) {
            $table->string('tipo', 50)->change();
        });
    }

    public function down(): void
    {
    }
};
