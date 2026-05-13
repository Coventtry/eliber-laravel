<?php

namespace App\Services;

use App\Models\Alerta;
use App\Models\Material;
use App\Models\MaterialEjemplar;
use App\Models\Prestamo;
use App\Models\Reserva;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservaService
{
    public function crearReserva(int $socioId, int $materialId): Reserva
    {
        $material = Material::findOrFail($materialId);

        $yaReservado = Reserva::where('material_id', $materialId)
            ->where('socio_id', $socioId)
            ->whereIn('estado', ['pendiente', 'aprobada'])
            ->exists();

        if ($yaReservado) {
            throw ValidationException::withMessages(['material_id' => 'Ya tienes una reserva activa para este material']);
        }

        return DB::transaction(function () use ($material, $socioId) {
            $ejemplar = MaterialEjemplar::where('material_id', $material->id)
                ->where('estado', 'disponible')
                ->lockForUpdate()
                ->first();

            if (! $ejemplar) {
                throw new \Exception('Material no disponible');
            }

            $ejemplar->update(['estado' => 'reservado']);

            $reserva = Reserva::create([
                'material_id' => $material->id,
                'ejemplar_id' => $ejemplar->id,
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

        $fechaDevolucion = now()->addDays($dias);

        return DB::transaction(function () use ($reserva, $fechaDevolucion) {
            if ($reserva->ejemplar_id) {
                MaterialEjemplar::where('id', $reserva->ejemplar_id)
                    ->lockForUpdate()
                    ->update(['estado' => 'prestado']);
            }

            $prestamo = Prestamo::create([
                'socio_id' => $reserva->socio_id,
                'material_id' => $reserva->material_id,
                'ejemplar_id' => $reserva->ejemplar_id,
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

            return $prestamo;
        });

        $user = User::where('socio_id', $reserva->socio_id)->first();
        if ($user) {
            $user->notify(new ReservaAprobada($reserva));
        }

        return $prestamo;
    }

    public function rechazarReserva(Reserva $reserva, ?string $motivo = null): void
    {
        if (! in_array($reserva->estado, ['pendiente', 'aprobada'])) {
            throw new \Exception('La reserva no puede ser rechazada');
        }

        DB::transaction(function () use ($reserva) {
            if ($reserva->estado === 'pendiente') {
                if ($reserva->ejemplar_id) {
                    $reserva->ejemplar->update(['estado' => 'disponible']);
                }
                $reserva->material->decrement('disponibilidad_reservada');
            } elseif ($reserva->estado === 'aprobada') {
                if ($reserva->ejemplar_id) {
                    $reserva->ejemplar->update(['estado' => 'disponible']);
                }
            }
            $reserva->update(['estado' => 'rechazada']);
        });
    }

    public function cancelarReserva(Reserva $reserva): void
    {
        DB::transaction(function () use ($reserva) {
            if ($reserva->estado === 'pendiente') {
                if ($reserva->ejemplar_id) {
                    $reserva->ejemplar->update(['estado' => 'disponible']);
                }
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
            if ($reserva->ejemplar_id) {
                $reserva->ejemplar->update(['estado' => 'disponible']);
            }
            $reserva->material->decrement('disponibilidad_reservada');
            $reserva->update(['estado' => 'expirada']);
        }

        return $vencidas->count();
    }
}
