<?php

namespace App\Services;

use App\Models\Material;
use App\Models\Prestamo;
use App\Models\Socio;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PrestamoService
{
    public function crearPrestamo(int $socioId, int $materialId, int $cantidad, string $fechaDevolucion): Prestamo
    {
        $socio    = Socio::findOrFail($socioId);
        $material = Material::findOrFail($materialId);

        $this->validarCreacion($socio, $material, $cantidad, $fechaDevolucion);

        return DB::transaction(function () use ($socio, $material, $cantidad, $fechaDevolucion) {
            $prestamo = Prestamo::create([
                'socio_id'         => $socio->id,
                'material_id'      => $material->id,
                'fecha_prestamo'   => now()->toDateString(),
                'fecha_devolucion' => $fechaDevolucion,
                'estado'           => 'activo',
                'cantidad'         => $cantidad,
            ]);

            $material->decrement('disponibilidad', $cantidad);

            return $prestamo;
        });
    }

    public function devolverPrestamo(Prestamo $prestamo): void
    {
        DB::transaction(function () use ($prestamo) {
            $prestamo->material->increment('disponibilidad', $prestamo->cantidad);
            $prestamo->update(['estado' => 'devuelto']);
        });
    }

    public function extenderPrestamo(Prestamo $prestamo, int $dias): void
    {
        if ($dias < 1 || $dias > 30) {
            throw ValidationException::withMessages(['dias' => 'Los días de extensión deben estar entre 1 y 30.']);
        }

        $prestamo->update([
            'fecha_devolucion' => $prestamo->fecha_devolucion->addDays($dias)->toDateString(),
        ]);
    }

    public function marcarAtrasados(): int
    {
        return Prestamo::whereIn('estado', ['activo', 'pendiente'])
            ->where('fecha_devolucion', '<', now()->toDateString())
            ->update(['estado' => 'atrasado']);
    }

    public function obtenerVencimientosProximos(int $dias = 4): \Illuminate\Database\Eloquent\Collection
    {
        return Prestamo::with(['socio', 'material'])
            ->vencimientoProximo($dias)
            ->get();
    }

    private function validarCreacion(Socio $socio, Material $material, int $cantidad, string $fechaDevolucion): void
    {
        if (!$socio->activo) {
            throw ValidationException::withMessages(['socio_id' => 'El socio está dado de baja.']);
        }

        if ($material->disponibilidad < $cantidad) {
            throw ValidationException::withMessages(['material_id' => "Disponibilidad insuficiente. Disponibles: {$material->disponibilidad}."]);
        }

        $activos = Prestamo::where('socio_id', $socio->id)
            ->whereIn('estado', ['activo', 'pendiente', 'atrasado'])
            ->count();

        if ($activos >= 3) {
            throw ValidationException::withMessages(['socio_id' => 'El socio ya tiene 3 préstamos activos.']);
        }

        $duplicado = Prestamo::where('socio_id', $socio->id)
            ->where('material_id', $material->id)
            ->whereIn('estado', ['activo', 'pendiente', 'atrasado'])
            ->exists();

        if ($duplicado) {
            throw ValidationException::withMessages(['material_id' => 'El socio ya tiene un préstamo activo de este material.']);
        }

        $hoy    = now()->startOfDay();
        $limite = now()->addDays(14)->startOfDay();
        $fecha  = \Carbon\Carbon::parse($fechaDevolucion)->startOfDay();

        if ($fecha->lt($hoy) || $fecha->gt($limite)) {
            throw ValidationException::withMessages(['fecha_devolucion' => 'La fecha de devolución debe estar entre hoy y los próximos 14 días.']);
        }
    }
}
