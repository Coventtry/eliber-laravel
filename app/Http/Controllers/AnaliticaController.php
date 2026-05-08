<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Prestamo;
use App\Models\Socio;
use Inertia\Inertia;
use Inertia\Response;

class AnaliticaController extends Controller
{
    public function index(): Response
    {
        $instId = auth()->user()->institucion_id;

        // Préstamos por mes — últimos 6 meses
        $prestamosPorMes = [];
        for ($i = 5; $i >= 0; $i--) {
            $mes = now()->subMonths($i);
            $count = Prestamo::where('institucion_id', $instId)
                ->whereYear('fecha_prestamo', $mes->year)
                ->whereMonth('fecha_prestamo', $mes->month)
                ->count();
            $prestamosPorMes[] = [
                'label' => $mes->translatedFormat('M Y'),
                'value' => $count,
            ];
        }

        // Distribución de materiales por área
        $porArea = Material::where('materiales.institucion_id', $instId)
            ->join('areas', 'areas.id', '=', 'materiales.area_id')
            ->selectRaw('areas.nombre as area, COUNT(materiales.id) as total')
            ->groupBy('areas.nombre')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => ['label' => $r->area, 'value' => (int) $r->total])
            ->toArray();

        // Socios activos vs dados de baja
        $sociosActivos = Socio::where('institucion_id', $instId)->where('activo', true)->count();
        $sociosBaja = Socio::where('institucion_id', $instId)->where('activo', false)->count();

        // Top 5 materiales más prestados
        $topMateriales = Prestamo::where('prestamos.institucion_id', $instId)
            ->join('materiales', 'materiales.id', '=', 'prestamos.material_id')
            ->selectRaw('materiales.titulo, materiales.autor, COUNT(prestamos.id) as total_prestamos')
            ->groupBy('materiales.id', 'materiales.titulo', 'materiales.autor')
            ->orderByDesc('total_prestamos')
            ->limit(5)
            ->get()
            ->map(fn ($r) => [
                'titulo' => $r->titulo,
                'autor' => $r->autor,
                'total_prestamos' => (int) $r->total_prestamos,
            ])
            ->toArray();

        // Totales rápidos
        $totales = [
            'prestamos_total' => Prestamo::where('institucion_id', $instId)->count(),
            'prestamos_activos' => Prestamo::where('institucion_id', $instId)->where('estado', 'activo')->count(),
            'prestamos_vencidos' => Prestamo::where('institucion_id', $instId)->where('estado', 'atrasado')->count(),
            'materiales_total' => Material::where('institucion_id', $instId)->count(),
            'socios_total' => Socio::where('institucion_id', $instId)->count(),
        ];

        return Inertia::render('Admin/Analitica/Index', [
            'prestamos_por_mes' => $prestamosPorMes,
            'por_area' => $porArea,
            'socios_activos' => $sociosActivos,
            'socios_baja' => $sociosBaja,
            'top_materiales' => $topMateriales,
            'totales' => $totales,
        ]);
    }
}
