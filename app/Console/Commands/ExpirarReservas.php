<?php

namespace App\Console\Commands;

use App\Models\Reserva;
use Illuminate\Console\Command;

class ExpirarReservas extends Command
{
    protected $signature = 'reservas:expirar';

    protected $description = 'Expira reservas vencidas y libera stock reservado';

    public function handle(): int
    {
        $vencidas = Reserva::where('estado', 'pendiente')
            ->where('fecha_vencimiento', '<', now())
            ->get();

        foreach ($vencidas as $reserva) {
            $reserva->material->decrement('disponibilidad_reservada');
            $reserva->update(['estado' => 'expirada']);
            $this->info("Reserva #{$reserva->id} expirada");
        }

        $this->info("{$vencidas->count()} reservas expiradas");

        return Command::SUCCESS;
    }
}