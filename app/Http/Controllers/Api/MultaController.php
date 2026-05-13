<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Multa;
use App\Services\MultaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MultaController extends Controller
{
    public function __construct(private MultaService $multaService) {}

    public function index(Request $request): JsonResponse
    {
        $multas = Multa::with('socio:id,nombre,apellido')
            ->when($request->pagada !== null, fn($q) => $q->where('pagada', $request->pagada))
            ->when($request->socio_id, fn($q, $s) => $q->where('socio_id', $s))
            ->orderByDesc('fecha_creacion')
            ->paginate(20);

        return response()->json($multas);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Multa::class);

        $validated = $request->validate([
            'socio_id'      => 'required|exists:socios,id',
            'monto'         => 'required|numeric|min:0.01',
            'motivo'        => 'required|string|max:255',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $multa = $this->multaService->registrar(
            $validated['socio_id'],
            (float) $validated['monto'],
            $validated['motivo'],
            null,
            $validated['observaciones'] ?? null,
        );

        return response()->json($multa, 201);
    }

    public function pagar(Multa $multa): JsonResponse
    {
        $this->authorize('pay', $multa);
        $this->multaService->pagar($multa);

        return response()->json(['message' => 'Multa pagada', 'multa' => $multa->fresh()]);
    }

    public function perdonar(Request $request, Multa $multa): JsonResponse
    {
        $this->authorize('forgive', $multa);

        $validated = $request->validate(['observaciones' => 'nullable|string|max:1000']);
        $this->multaService->perdonar($multa, $validated['observaciones'] ?? null);

        return response()->json(['message' => 'Multa perdonada', 'multa' => $multa->fresh()]);
    }
}
