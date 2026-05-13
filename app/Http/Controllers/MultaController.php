<?php

namespace App\Http\Controllers;

use App\Models\Multa;
use App\Models\Socio;
use App\Services\MultaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MultaController extends Controller
{
    public function __construct(private MultaService $multaService) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Multa::class);
        $multas = Multa::with('socio:id,nombre,apellido')
            ->when($request->pagada !== null && $request->pagada !== '', fn($q) => $q->where('pagada', $request->pagada))
            ->when($request->socio_id, fn($q, $s) => $q->where('socio_id', $s))
            ->orderByDesc('fecha_creacion')
            ->paginate(20)
            ->withQueryString();

        $socios = Socio::activos()->select('id', 'nombre', 'apellido')->orderBy('apellido')->get();

        return Inertia::render('Multas/Index', [
            'multas'  => $multas,
            'socios'  => $socios,
            'filters' => $request->only(['pagada', 'socio_id']),
        ]);
    }

    public function create(): Response
    {
        $socios = Socio::activos()->select('id', 'nombre', 'apellido')->orderBy('apellido')->get();

        return Inertia::render('Multas/Create', [
            'socios' => $socios,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Multa::class);

        $validated = $request->validate([
            'socio_id'      => 'required|exists:socios,id',
            'monto'         => 'required|numeric|min:0.01',
            'motivo'        => 'required|string|max:255',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $this->multaService->registrar(
            $validated['socio_id'],
            (float) $validated['monto'],
            $validated['motivo'],
            null,
            $validated['observaciones'] ?? null,
        );

        return redirect()->route('multas.index')->with('success', 'Multa registrada correctamente.');
    }

    public function pagar(Multa $multa): RedirectResponse
    {
        $this->authorize('pay', $multa);
        $this->multaService->pagar($multa);

        return redirect()->route('multas.index')->with('success', 'Multa marcada como pagada.');
    }

    public function perdonar(Request $request, Multa $multa): RedirectResponse
    {
        $this->authorize('forgive', $multa);

        $validated = $request->validate(['observaciones' => 'nullable|string|max:1000']);
        $this->multaService->perdonar($multa, $validated['observaciones'] ?? null);

        return redirect()->route('multas.index')->with('success', 'Multa perdonada.');
    }
}
