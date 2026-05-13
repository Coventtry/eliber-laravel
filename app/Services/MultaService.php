<?php

namespace App\Services;

use App\Models\Configuracion;
use App\Models\Multa;
use App\Models\Prestamo;
use App\Models\Socio;
use Illuminate\Support\Facades\DB;

class MultaService
{
    public function registrar(int $socioId, float $monto, string $motivo, ?int $prestamoId = null, ?string $observaciones = null): Multa
    {
        $socio = Socio::findOrFail($socioId);

        return Multa::create([
            'socio_id'      => $socioId,
            'prestamo_id'   => $prestamoId,
            'monto'         => $monto,
            'motivo'        => $motivo,
            'observaciones' => $observaciones,
            'pagada'        => false,
            'fecha_creacion' => now()->toDateString(),
            'institucion_id' => $socio->institucion_id,
        ]);
    }

    public function pagar(Multa $multa): void
    {
        $multa->update([
            'pagada'     => true,
            'fecha_pago' => now()->toDateString(),
        ]);
    }

    public function perdonar(Multa $multa, ?string $observaciones = null): void
    {
        $multa->update([
            'pagada'       => true,
            'fecha_pago'   => now()->toDateString(),
            'observaciones' => $observaciones ? trim($observaciones) : $multa->observaciones,
        ]);
    }

    public function generarMultaPorVencimiento(Prestamo $prestamo): ?Multa
    {
        $prestamo->load('material');

        $monto = (float) Configuracion::get($prestamo->institucion_id, 'monto_multa_diaria', 100);

        $diasVencido = now()->startOfDay()->diffInDays($prestamo->fecha_devolucion->startOfDay(), false);

        $montoTotal = round($monto * max(1, $diasVencido), 2);

        return DB::transaction(function () use ($prestamo, $montoTotal) {
            $existe = Multa::where('prestamo_id', $prestamo->id)->lockForUpdate()->exists();
            if ($existe) {
                return null;
            }

            return $this->registrar(
                $prestamo->socio_id,
                $montoTotal,
                'Devolución tardía',
                $prestamo->id,
                "Préstamo #{$prestamo->id} — {$prestamo->material->titulo} vencido el {$prestamo->fecha_devolucion->format('d/m/Y')}"
            );
        });
    }
}
