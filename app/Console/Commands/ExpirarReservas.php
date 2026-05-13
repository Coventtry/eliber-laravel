<?php

namespace App\Console\Commands;

use App\Services\ReservaService;
use Illuminate\Console\Command;

class ExpirarReservas extends Command
{
    protected $signature = 'reservas:expirar';

    protected $description = 'Expira reservas vencidas y libera stock reservado';

    public function handle(ReservaService $reservaService): int
    {
        $count = $reservaService->expirarReservasVencidas();
        $this->info("{$count} reservas expiradas");

        return Command::SUCCESS;
    }
}
