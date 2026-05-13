<?php

namespace App\Services;

use App\Models\Alerta;
use App\Models\Material;
use App\Models\Reserva;
use App\Models\Prestamo;
use App\Models\User;
use App\Notifications\ReservaAprobada;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservaService
{
    public function crearReserva(int $socioId, int $materialId): Reserva
    {
        $material = Material::findOrFail($materialId);
        $stockReal = $material->disponibilidad - $material->disponibilidad_reservada;

        if ($stockReal <= 0) {
            throw ValidationException::withMessages(['material_id' => 'Material no disponible']);
        }

        $yaReservado = Reserva::where('material_id', $materialId)
            ->where('socio_id', $socioId)
            ->whereIn('estado', ['pendiente', 'aprobada'])
            ->exists();

        if ($yaReservado) {
            throw ValidationException::withMessages(['material_id' => 'Ya tienes una reserva activa para este material']);
        }

        return DB::transaction(function () use ($material, $socioId) {
            $reserva = Reserva::create([
                'material_id' => $material->id,
                'socio_id' => $socioId,
                'estado' => 'pendiente',
                'fecha_reserva' => now(),
                'fecha_vencimiento' => now()->addDays(2),
                'institucion_id' => auth()->user()->institucion_id,
            ]);

            $material->increment('disponibilidad_reservada');

            $reserva->load('socio');

            Alerta::create([
                'institucion_id' => $reserva->institucion_id,
                'prestamo_id'    => null,
                'tipo'           => 'solicitud_reserva',
                'descripcion'    => "{$reserva->socio->full_name} solicitó: {$material->titulo}",
                'fecha_alerta'   => now(),
                'leida'          => false,
            ]);

            return $reserva;
        });
    }

    public function aprobarReserva(Reserva $reserva, int $dias = 14): Prestamo
    {
        if ($reserva->estado !== 'pendiente') {
            throw ValidationException::withMessages(['estado' => 'Solo reservas pendientes pueden ser aprobadas']);
        }

        $material = $reserva->material;
        $disponible = $material->disponibilidad - $material->disponibilidad_reservada;

        if ($disponible < 1) {
            throw ValidationException::withMessages(['material_id' => 'Material ya no disponible']);
        }

        $fechaDevolucion = now()->addDays($dias);

        $prestamo = DB::transaction(function () use ($reserva, $fechaDevolucion) {
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

        $user = User::where('socio_id', $reserva->socio_id)->first();
        if ($user) {
            $user->notify(new ReservaAprobada($reserva));
        }

        return $prestamo;
    }

    public function rechazarReserva(Reserva $reserva, string $motivo = null): void
    {
        if (!in_array($reserva->estado, ['pendiente', 'aprobada'])) {
            throw ValidationException::withMessages(['estado' => 'La reserva no puede ser rechazada']);
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
