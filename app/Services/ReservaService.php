<?php

namespace App\Services;

use App\Models\Material;
use App\Models\Reserva;
use App\Models\Prestamo;
use Illuminate\Support\Facades\DB;

class ReservaService
{
    public function crearReserva(int $socioId, int $materialId): Reserva
    {
        $material = Material::findOrFail($materialId);
        $stockReal = $material->disponibilidad - $material->disponibilidad_reservada;

        if ($stockReal <= 0) {
            throw new \Exception('Material no disponible');
        }

        $yaReservado = Reserva::where('material_id', $materialId)
            ->where('socio_id', $socioId)
            ->whereIn('estado', ['pendiente', 'aprobada'])
            ->exists();

        if ($yaReservado) {
            throw new \Exception('Ya tienes una reserva activa para este material');
        }

        return DB::transaction(function () use ($material, $socioId) {
            $reserva = Reserva::create([
                'material_id' => $material->id,
                'socio_id' => $socioId,
                'estado' => 'pendiente',
                'fecha_reserva' => now(),
                'fecha_vencimiento' => now()->addDays(2),
            ]);

            $material->increment('disponibilidad_reservada');

            return $reserva;
        });
    }

    public function aprobarReserva(Reserva $reserva, int $dias = 14): Prestamo
    {
        if ($reserva->estado !== 'pendiente') {
            throw new \Exception('Solo reservas pendientes pueden ser aprobadas');
        }

        $material = $reserva->material;
        $disponible = $material->disponibilidad - $material->disponibilidad_reservada;

        if ($disponible < 1) {
            throw new \Exception('Material ya no disponible');
        }

        $fechaDevolucion = now()->addDays($dias);

        return DB::transaction(function () use ($reserva, $fechaDevolucion) {
            $prestamo = Prestamo::create([
                'socio_id' => $reserva->socio_id,
                'material_id' => $reserva->material_id,
                'fecha_prestamo' => now(),
                'fecha_devolucion' => $fechaDevolucion,
                'estado' => 'activo',
                'cantidad' => 1,
                'institucion_id' => $reserva->socio->institucion_id,
            ]);

            $reserva->update([
                'estado' => 'aprobada',
                'fecha_vencimiento' => $fechaDevolucion,
            ]);

            $reserva->material->decrement('disponibilidad_reservada');

            $reserva->material->decrement('disponibilidad');

            return $prestamo;
        });
    }

    public function rechazarReserva(Reserva $reserva, string $motivo = null): void
    {
        if (!in_array($reserva->estado, ['pendiente', 'aprobada'])) {
            throw new \Exception('La reserva no puede ser rechazada');
        }

        DB::transaction(function () use ($reserva) {
            if ($reserva->estado === 'pendiente') {
                $reserva->material->decrement('disponibilidad_reservada');
            }
            $reserva->update(['estado' => 'rechazada']);
        });
    }

    public function cancelarReserva(Reserva $reserva): void
    {
        DB::transaction(function () use ($reserva) {
            if ($reserva->estado === 'pendiente') {
                $reserva->material->decrement('disponibilidad_reservada');
            }
            $reserva->update(['estado' => 'expirada']);
        });
    }

    public function expirarReservasVencidas(): int
    {
        $vencidas = Reserva::where('estado', 'pendiente')
            ->where('fecha_vencimiento', '<', now())
            ->get();

        foreach ($vencidas as $reserva) {
            $reserva->material->decrement('disponibilidad_reservada');
            $reserva->update(['estado' => 'expirada']);
        }

        return $vencidas->count();
    }
}
