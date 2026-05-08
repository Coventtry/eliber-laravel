<?php

namespace App\Services;

use App\Models\Alerta;
use App\Models\Configuracion;
use App\Models\Material;
use App\Models\MaterialEjemplar;
use App\Models\Prestamo;
use App\Models\Socio;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PrestamoService
{
    /**
     * Crea un préstamo por cada ejemplar disponible (uno por copia).
     *
     * @return Prestamo[]
     */
    public function crearPrestamo(int $socioId, int $materialId, int $cantidad, string $fechaDevolucion): array
    {
        $socio = Socio::findOrFail($socioId);
        $material = Material::findOrFail($materialId);

        $this->validarCreacion($socio, $material, $cantidad, $fechaDevolucion);

        return DB::transaction(function () use ($socio, $material, $cantidad, $fechaDevolucion) {
            Material::where('id', $material->id)->lockForUpdate();

            $ejemplares = MaterialEjemplar::where('material_id', $material->id)
                ->where('estado', 'disponible')
                ->lockForUpdate()
                ->limit($cantidad)
                ->get();

            if ($ejemplares->count() < $cantidad) {
                throw ValidationException::withMessages([
                    'material_id' => "Stock insuficiente al confirmar. Disponibles: {$ejemplares->count()}.",
                ]);
            }

            $prestamos = [];
            foreach ($ejemplares as $ejemplar) {
                $prestamos[] = Prestamo::create([
                    'socio_id' => $socio->id,
                    'material_id' => $material->id,
                    'ejemplar_id' => $ejemplar->id,
                    'fecha_prestamo' => now()->toDateString(),
                    'fecha_devolucion' => $fechaDevolucion,
                    'estado' => 'activo',
                    'cantidad' => 1,
                    'institucion_id' => $socio->institucion_id,
                ]);
                $ejemplar->update(['estado' => 'prestado']);
            }

            $material->decrement('disponibilidad', $cantidad);

            return $prestamos;
        });
    }

    public function devolverPrestamo(Prestamo $prestamo): void
    {
        DB::transaction(function () use ($prestamo) {
            if ($prestamo->ejemplar_id) {
                $prestamo->ejemplar->update(['estado' => 'disponible']);
            }

            $prestamo->material->increment('disponibilidad', 1);
            $prestamo->update(['estado' => 'devuelto']);

            Alerta::withoutGlobalScopes()
                ->where('prestamo_id', $prestamo->id)
                ->where('leida', false)
                ->update(['leida' => true]);
        });
    }

    public function extenderPrestamo(Prestamo $prestamo, int $dias): void
    {
        if ($dias < 1 || $dias > 30) {
            throw ValidationException::withMessages(['dias' => 'Los días de extensión deben estar entre 1 y 30.']);
        }

        if ($prestamo->ejemplar_id && $prestamo->ejemplar?->estado !== 'prestado') {
            throw ValidationException::withMessages(['prestamo' => 'El ejemplar no está en préstamo activo.']);
        }

        $prestamo->update([
            'fecha_devolucion' => $prestamo->fecha_devolucion->addDays($dias)->toDateString(),
        ]);

        $prestamo->load(['socio', 'material']);
        $nuevaFecha = $prestamo->fecha_devolucion->format('d/m/Y');
        $this->registrarAlerta(
            $prestamo,
            'renovacion',
            "Renovación +{$dias} días — {$prestamo->material->titulo} (nuevo vencimiento: {$nuevaFecha})"
        );
    }

    public function marcarAtrasados(): int
    {
        return DB::transaction(function () {
            $atrasados = Prestamo::with(['socio', 'material'])
                ->whereIn('estado', ['activo', 'pendiente'])
                ->where('fecha_devolucion', '<', now()->toDateString())
                ->lockForUpdate()
                ->get();

            foreach ($atrasados as $prestamo) {
                $prestamo->update(['estado' => 'atrasado']);
                $fecha = $prestamo->fecha_devolucion->format('d/m/Y');
                $this->registrarAlerta(
                    $prestamo,
                    'vencido',
                    "{$prestamo->socio->full_name} — {$prestamo->material->titulo} (venció el {$fecha})",
                    true
                );
            }

            return count($atrasados);
        });
    }

    public function obtenerVencimientosProximos(int $dias = 4): Collection
    {
        $proximos = Prestamo::with(['socio', 'material'])
            ->vencimientoProximo($dias)
            ->get();

        foreach ($proximos as $prestamo) {
            $fecha = $prestamo->fecha_devolucion->format('d/m/Y');
            $this->registrarAlerta(
                $prestamo,
                'proximo_vencer',
                "{$prestamo->socio->full_name} — {$prestamo->material->titulo} (vence el {$fecha})"
            );
        }

        return $proximos;
    }

    private function registrarAlerta(Prestamo $prestamo, string $tipo, string $descripcion, bool $upsert = false): void
    {
        if (! auth()->check()) {
            return;
        }

        $institucionId = $prestamo->institucion_id;

        if ($tipo === 'renovacion') {
            Alerta::create([
                'institucion_id' => $institucionId,
                'prestamo_id' => $prestamo->id,
                'tipo' => $tipo,
                'descripcion' => $descripcion,
                'fecha_alerta' => now(),
                'leida' => false,
            ]);

            return;
        }

        $existe = Alerta::withoutGlobalScopes()
            ->where('prestamo_id', $prestamo->id)
            ->where('tipo', $tipo)
            ->where('leida', false)
            ->exists();

        if ($existe && ! $upsert) {
            return;
        }

        if ($upsert) {
            Alerta::withoutGlobalScopes()->updateOrCreate(
                ['prestamo_id' => $prestamo->id, 'tipo' => $tipo],
                [
                    'institucion_id' => $institucionId,
                    'descripcion' => $descripcion,
                    'fecha_alerta' => now(),
                    'leida' => false,
                ]
            );
        } else {
            Alerta::create([
                'institucion_id' => $institucionId,
                'prestamo_id' => $prestamo->id,
                'tipo' => $tipo,
                'descripcion' => $descripcion,
                'fecha_alerta' => now(),
                'leida' => false,
            ]);
        }
    }

    private function validarCreacion(Socio $socio, Material $material, int $cantidad, string $fechaDevolucion): void
    {
        if (! $socio->activo) {
            throw ValidationException::withMessages(['socio_id' => 'El socio está dado de baja.']);
        }

        $disponibleReal = MaterialEjemplar::where('material_id', $material->id)
            ->where('estado', 'disponible')
            ->count();

        if ($disponibleReal < $cantidad) {
            throw ValidationException::withMessages([
                'material_id' => "Disponibilidad insuficiente. Disponibles: {$disponibleReal}.",
            ]);
        }

        $activos = Prestamo::where('socio_id', $socio->id)
            ->whereIn('estado', ['activo', 'pendiente', 'atrasado'])
            ->count();

        if (($activos + $cantidad) > 3) {
            $restantes = max(0, 3 - $activos);
            throw ValidationException::withMessages([
                'socio_id' => "El socio solo puede tomar {$restantes} préstamo(s) más (límite: 3 activos).",
            ]);
        }

        $diasPrestamo = (int) Configuracion::get($socio->institucion_id, 'dias_prestamo', 14);
        $hoy = now()->startOfDay();
        $limite = now()->addDays($diasPrestamo)->startOfDay();
        $fecha = Carbon::parse($fechaDevolucion)->startOfDay();

        if ($fecha->lt($hoy) || $fecha->gt($limite)) {
            throw ValidationException::withMessages([
                'fecha_devolucion' => "La fecha de devolución debe estar entre hoy y los próximos {$diasPrestamo} días.",
            ]);
        }
    }
}
