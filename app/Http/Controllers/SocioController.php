<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSocioRequest;
use App\Models\Socio;
use App\Services\SocioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SocioController extends Controller
{
    public function __construct(private SocioService $socioService) {}

    public function index(Request $request): Response
    {
        $socios = Socio::query()
            ->withCount(['prestamos as atrasados_count' => fn($q) => $q->where('estado', 'atrasado')])
            ->when($request->busqueda, fn($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('nombre', 'like', "%{$s}%")
                  ->orWhere('apellido', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            }))
            ->when($request->activo !== null && $request->activo !== '', fn($q) => $q->where('activo', $request->activo))
            ->when($request->anio, fn($q, $a) => $q->where('anio', $a))
            ->when($request->division, fn($q, $d) => $q->where('division', $d))
            ->when($request->moroso, fn($q) => $q->whereHas('prestamos', fn($pq) => $pq->where('estado', 'atrasado')))
            ->orderBy('apellido')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Socios/Index', [
            'socios'  => $socios,
            'filters' => $request->only(['busqueda', 'activo', 'anio', 'division', 'moroso']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Socios/Create');
    }

    public function store(StoreSocioRequest $request): RedirectResponse
    {
        $this->authorize('create', Socio::class);
        Socio::create([
            ...$request->validated(),
            'institucion_id' => $request->user()->institucion_id,
        ]);
        return redirect()->route('socios.index')->with('success', 'Socio registrado correctamente.');
    }

    public function edit(Socio $socio): Response
    {
        return Inertia::render('Socios/Edit', [
            'socio'    => $socio,
            'historial' => $socio->historial()->orderByDesc('fecha')->get(),
        ]);
    }

    public function update(StoreSocioRequest $request, Socio $socio): RedirectResponse
    {
        $this->authorize('update', $socio);
        $socio->update($request->validated());
        return redirect()->route('socios.index')->with('success', 'Socio actualizado.');
    }

    public function destroy(Socio $socio): RedirectResponse
    {
        $this->authorize('delete', $socio);
        $socio->delete();
        return redirect()->route('socios.index')->with('success', 'Socio eliminado.');
    }

    public function baja(Request $request, Socio $socio): RedirectResponse
    {
        $this->authorize('update', $socio);
        $this->socioService->darDeBaja($socio, $request->input('observaciones', ''));
        return redirect()->route('socios.index')->with('success', 'Socio dado de baja.');
    }

    public function reactivar(Socio $socio): RedirectResponse
    {
        $this->authorize('update', $socio);
        $this->socioService->reactivar($socio);
        return redirect()->route('socios.index')->with('success', 'Socio reactivado.');
    }

    public function buscar(Request $request): JsonResponse
    {
        $termino = $request->input('q', $request->input('email', ''));

        $socios = Socio::activos()
            ->where(function ($q) use ($termino) {
                $q->where('nombre', 'like', "%{$termino}%")
                  ->orWhere('apellido', 'like', "%{$termino}%")
                  ->orWhere('email', 'like', "%{$termino}%");
            })
            ->select('id', 'nombre', 'apellido', 'email', 'anio', 'division')
            ->limit(10)
            ->get();

        return response()->json($socios);
    }

    public function prestamosSocio(Socio $socio): JsonResponse
    {
        $prestamos = $socio->prestamos()
            ->with('material:id,titulo,codigo')
            ->orderByRaw("FIELD(estado, 'atrasado', 'activo', 'pendiente', 'devuelto')")
            ->orderBy('fecha_devolucion')
            ->get(['id', 'material_id', 'fecha_prestamo', 'fecha_devolucion', 'estado', 'cantidad'])
            ->map(fn($p) => [
                'id'                   => $p->id,
                'material'             => $p->material?->titulo,
                'codigo'               => $p->material?->codigo,
                'fecha_prestamo'       => $p->fecha_prestamo?->format('d/m/Y'),
                'fecha_devolucion'     => $p->fecha_devolucion?->format('d/m/Y'),
                'fecha_devolucion_raw' => $p->fecha_devolucion?->format('Y-m-d'),
                'estado'               => $p->estado,
                'cantidad'             => $p->cantidad,
            ]);

        return response()->json([
            'circulacion' => $prestamos->whereIn('estado', ['activo', 'pendiente', 'atrasado'])->values(),
            'historial'   => $prestamos->where('estado', 'devuelto')->take(10)->values(),
            'atrasados'   => $prestamos->where('estado', 'atrasado')->count(),
        ]);
    }
}
