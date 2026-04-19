<?php

namespace App\Services;

use App\Models\HistorialSocio;
use App\Models\Socio;

class SocioService
{
    public function darDeBaja(Socio $socio, string $observaciones = ''): void
    {
        $socio->update(['activo' => 0]);

        HistorialSocio::create([
            'id_socio'      => $socio->id,
            'accion'        => 'BAJA',
            'fecha'         => now(),
            'observaciones' => $observaciones ?: 'Baja registrada.',
        ]);
    }

    public function reactivar(Socio $socio): void
    {
        $socio->update(['activo' => 1]);

        HistorialSocio::create([
            'id_socio'      => $socio->id,
            'accion'        => 'ALTA',
            'fecha'         => now(),
            'observaciones' => 'Reactivación de cuenta.',
        ]);
    }
}
