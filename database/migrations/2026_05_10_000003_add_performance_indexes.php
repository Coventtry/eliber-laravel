<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndex('prestamos', 'estado');
        $this->addIndex('prestamos', 'fecha_devolucion');
        $this->addIndex('socios', 'email');
        $this->addIndex('multas', 'pagada');
        $this->addIndex('alertas', 'leida');
        $this->addIndex('historial_socios', 'id_socio');
    }

    public function down(): void
    {
        $tables = [
            'prestamos' => ['estado', 'fecha_devolucion'],
            'socios' => ['email'],
            'multas' => ['pagada'],
            'alertas' => ['leida'],
            'historial_socios' => ['id_socio'],
        ];

        foreach ($tables as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            Schema::table($table, function (Blueprint $t) use ($columns) {
                foreach ($columns as $column) {
                    $t->dropIndex([$column]);
                }
            });
        }
    }

    private function addIndex(string $table, string $column): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $t) use ($column) {
                $t->index($column);
            });
        } catch (\Exception $e) {
            // Index may already exist on production — safe to ignore
        }
    }
};
