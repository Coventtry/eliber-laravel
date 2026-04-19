<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSocioRequest;
use App\Models\Socio;
use App\Services\SocioService;
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
            ->when($request->search, fn($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('nombre', 'like', "%{$s}%")
                  ->orWhere('apellido', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            }))
            ->when($request->activo !== null, fn($q) => $q->where('activo', $request->activo))
            ->orderBy('apellido')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Socios/Index', [
            'socios'  => $socios,
            'filters' => $request->only(['search', 'activo']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Socios/Create');
    }

    public function store(StoreSocioRequest $request): RedirectResponse
    {
        Socio::create($request->validated());
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
        $socio->update($request->validated());
        return redirect()->route('socios.index')->with('success', 'Socio actualizado.');
    }

    public function destroy(Socio $socio): RedirectResponse
    {
        $socio->delete();
        return redirect()->route('socios.index')->with('success', 'Socio eliminado.');
    }

    public function baja(Request $request, Socio $socio): RedirectResponse
    {
        $this->socioService->darDeBaja($socio, $request->input('observaciones', ''));
        return redirect()->route('socios.index')->with('success', 'Socio dado de baja.');
    }

    public function reactivar(Socio $socio): RedirectResponse
    {
        $this->socioService->reactivar($socio);
        return redirect()->route('socios.index')->with('success', 'Socio reactivado.');
    }

    public function buscar(Request $request)
    {
        $socios = Socio::activos()
            ->buscarEmail($request->input('email', ''))
            ->select('id', 'nombre', 'apellido', 'email', 'anio', 'division')
            ->limit(10)
            ->get();

        return response()->json($socios);
    }
}
