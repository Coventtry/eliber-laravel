<?php

namespace App\Console\Commands;

use App\Models\Institucion;
use App\Services\PrestamoService;
use Illuminate\Console\Command;

class MarcarAtrasados extends Command
{
    protected $signature = 'prestamos:marcar-atrasados';

    protected $description = 'Marca préstamos vencidos como atrasados y genera alertas de vencimientos próximos';

    public function handle(PrestamoService $prestamoService): int
    {
        $this->info('Marcando préstamos vencidos como atrasados...');
        $atrasados = $prestamoService->marcarAtrasados();
        $this->info("{$atrasados} préstamos marcados como atrasados");

        $this->info('Generando alertas de vencimientos próximos...');
        $proximos = $prestamoService->obtenerVencimientosProximos();
        $this->info("{$proximos->count()} alertas de vencimiento generadas");

        return Command::SUCCESS;
    }
}
