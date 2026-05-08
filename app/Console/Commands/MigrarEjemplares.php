<?php

namespace App\Console\Commands;

use App\Models\Material;
use App\Models\MaterialEjemplar;
use App\Models\Prestamo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrarEjemplares extends Command
{
    protected $signature = 'ejemplares:migrar {--dry-run : Solo mostrar qué se haría sin ejecutar}';

    protected $description = 'Genera ejemplares físicos para materiales existentes y asigna ejemplar_id a préstamos activos';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('--- MODO DRY-RUN: no se modificará nada ---');
        }

        $this->migrarMateriales($dryRun);
        $this->migrarPrestamosActivos($dryRun);

        $this->info('Migración completada.');

        return self::SUCCESS;
    }

    private function migrarMateriales(bool $dryRun): void
    {
        $materiales = Material::withoutGlobalScopes()
            ->where('disponibilidad', '>', 0)
            ->get();

        $this->info("Materiales a procesar: {$materiales->count()}");

        foreach ($materiales as $material) {
            $existentes = MaterialEjemplar::withoutGlobalScopes()
                ->where('material_id', $material->id)
                ->count();

            if ($existentes > 0) {
                $this->line("  SKIP material #{$material->id} ({$material->titulo}): ya tiene {$existentes} ejemplares");

                continue;
            }

            $total = (int) $material->disponibilidad;
            $this->line("  Creando {$total} ejemplares para material #{$material->id}: {$material->titulo}");

            if (! $dryRun) {
                DB::transaction(function () use ($material, $total) {
                    for ($i = 1; $i <= $total; $i++) {
                        MaterialEjemplar::withoutGlobalScopes()->create([
                            'material_id' => $material->id,
                            'institucion_id' => $material->institucion_id,
                            'codigo_ejemplar' => $material->codigo.'-E'.str_pad($i, 2, '0', STR_PAD_LEFT),
                            'estado' => 'disponible',
                        ]);
                    }
                });
            }
        }
    }

    private function migrarPrestamosActivos(bool $dryRun): void
    {
        $prestamos = Prestamo::withoutGlobalScopes()
            ->whereIn('estado', ['activo', 'atrasado', 'pendiente'])
            ->whereNull('ejemplar_id')
            ->with('material')
            ->get();

        $this->info("Préstamos activos sin ejemplar: {$prestamos->count()}");

        foreach ($prestamos as $prestamo) {
            if (! $prestamo->material) {
                $this->warn("  SKIP préstamo #{$prestamo->id}: sin material asociado");

                continue;
            }

            $ejemplar = MaterialEjemplar::withoutGlobalScopes()
                ->where('material_id', $prestamo->material_id)
                ->where('estado', 'disponible')
                ->first();

            if (! $ejemplar) {
                $this->warn("  WARN préstamo #{$prestamo->id}: sin ejemplar disponible, se creará uno adicional");

                if (! $dryRun) {
                    $count = MaterialEjemplar::withoutGlobalScopes()
                        ->where('material_id', $prestamo->material_id)
                        ->count();

                    $ejemplar = MaterialEjemplar::withoutGlobalScopes()->create([
                        'material_id' => $prestamo->material_id,
                        'institucion_id' => $prestamo->material->institucion_id,
                        'codigo_ejemplar' => $prestamo->material->codigo.'-E'.str_pad($count + 1, 2, '0', STR_PAD_LEFT),
                        'estado' => 'disponible',
                    ]);
                } else {
                    continue;
                }
            }

            $this->line("  Asignando ejemplar #{$ejemplar->id} ({$ejemplar->codigo_ejemplar}) al préstamo #{$prestamo->id}");

            if (! $dryRun) {
                DB::transaction(function () use ($prestamo, $ejemplar) {
                    $ejemplar->update(['estado' => 'prestado']);
                    $prestamo->update(['ejemplar_id' => $ejemplar->id]);
                });
            }
        }
    }
}
