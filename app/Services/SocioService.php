<?php

namespace App\Services;

use App\Models\HistorialSocio;
use App\Models\Socio;
use Illuminate\Support\Facades\DB;

class SocioService
{
    public function darDeBaja(Socio $socio, string $observaciones = ''): void
    {
        DB::transaction(function () use ($socio, $observaciones) {
            $socio->update(['activo' => 0]);

        HistorialSocio::create([
            'id_socio' => $socio->id,
            'accion' => 'BAJA',
            'fecha' => now(),
            'observaciones' => $observaciones ?: 'Baja registrada.',
            'institucion_id' => $socio->institucion_id,
        ]);
    }

    public function reactivar(Socio $socio): void
    {
        DB::transaction(function () use ($socio) {
            $socio->update(['activo' => 1]);

        HistorialSocio::create([
            'id_socio' => $socio->id,
            'accion' => 'ALTA',
            'fecha' => now(),
            'observaciones' => 'Reactivacion de cuenta.',
            'institucion_id' => $socio->institucion_id,
        ]);
    }
}
