<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Services\ReservaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservaController extends Controller
{
    public function __construct(
        private ReservaService $reservaService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        if ($user->hasRole('admin') || $user->can('gestionar-prestamos')) {
            $reservas = Reserva::with(['material', 'socio'])
                ->orderBy('fecha_reserva', 'desc')
                ->paginate(20);
        } else {
            $reservas = Reserva::with(['material'])
                ->where('socio_id', $user->socio_id ?? 0)
                ->orderBy('fecha_reserva', 'desc')
                ->paginate(20);
        }

        return response()->json($reservas);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'material_id' => 'required|exists:materiales,id',
            'socio_id' => 'required|exists:socios,id',
        ]);

        try {
            $reserva = $this->reservaService->crearReserva(
                $request->socio_id,
                $request->material_id
            );

            return response()->json($reserva, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy(int $reserva): JsonResponse
    {
        $reserva = Reserva::findOrFail($reserva);
        $user = Auth::user();

        if (!$user->can('gestionar-prestamos') && $reserva->socio_id !== ($user->socio_id ?? 0)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        try {
            $this->reservaService->cancelarReserva($reserva);
            return response()->json(['message' => 'Reserva cancelada']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function aprobar(Request $request, int $reserva): JsonResponse
    {
        $reserva = Reserva::with('material')->findOrFail($reserva);

        $request->validate([
            'dias' => 'nullable|integer|min:1|max:30',
        ]);

        $dias = $request->input('dias', 14);

        try {
            $prestamo = $this->reservaService->aprobarReserva($reserva, $dias);
            return response()->json([
                'message' => 'Reserva aprobada y prestamo creado',
                'prestamo_id' => $prestamo->id,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function rechazar(Request $request, int $reserva): JsonResponse
    {
        $reserva = Reserva::findOrFail($reserva);

        $request->validate([
            'motivo' => 'nullable|string|max:500',
        ]);

        try {
            $this->reservaService->rechazarReserva(
                $reserva,
                $request->motivo
            );
            return response()->json(['message' => 'Reserva rechazada']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}