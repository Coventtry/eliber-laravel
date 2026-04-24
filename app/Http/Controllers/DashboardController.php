<?php

namespace App\Http\Controllers;

use App\Services\PrestamoService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private PrestamoService $prestamoService) {}

    public function index(): Response|RedirectResponse
    {
        if (auth()->user()->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        if (auth()->user()->hasRole('alumno')) {
            return redirect()->route('alumno.dashboard');
        }

        $this->prestamoService->marcarAtrasados();

        return Inertia::render('Dashboard', [
            'vencimientosProximos' => $this->prestamoService->obtenerVencimientosProximos(4)
                ->map(fn($p) => [
                    'id'              => $p->id,
                    'socio'           => $p->socio->full_name,
                    'material'        => $p->material->titulo,
                    'fecha_devolucion' => $p->fecha_devolucion->format('d/m/Y'),
                    'link_whatsapp'   => $p->link_whatsapp,
                ]),
        ]);
    }
}
