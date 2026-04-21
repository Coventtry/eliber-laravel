<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Models\Material;
use App\Services\PrestamoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservaController extends Controller
{
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
        ]);

        $material = Material::findOrFail($request->material_id);
        $stockReal = $material->disponibilidad - $material->disponibilidad_reservada;

        if ($stockReal <= 0) {
            return response()->json(['message' => 'Material no disponible'], 422);
        }

        $user = Auth::user();
        $socioId = $user->socio_id ?? $request->socio_id;

        if (!$socioId) {
            return response()->json(['message' => 'Socio no identificado'], 422);
        }

        $existeActiva = Reserva::where('material_id', $material->id)
            ->where('socio_id', $socioId)
            ->whereIn('estado', ['pendiente', 'aprobada'])
            ->exists();

        if ($existeActiva) {
            return response()->json(['message' => 'Ya tienes una reserva activa para este material'], 422);
        }

        $reserva = Reserva::create([
            'material_id' => $material->id,
            'socio_id' => $socioId,
            'estado' => 'pendiente',
            'fecha_reserva' => now(),
            'fecha_vencimiento' => now()->addDays(2),
        ]);

        $material->increment('disponibilidad_reservada');

        return response()->json($reserva, 201);
    }

    public function destroy(int $reserva): JsonResponse
    {
        $reserva = Reserva::findOrFail($reserva);
        $user = Auth::user();

        if (!$user->can('gestionar-prestamos') && $reserva->socio_id !== ($user->socio_id ?? 0)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if ($reserva->estado === 'pendiente') {
            $reserva->material->decrement('disponibilidad_reservada');
        }

        $reserva->update(['estado' => 'expirada']);

        return response()->json(['message' => 'Reserva cancelada']);
    }

    public function aprobar(int $reserva, Request $request, PrestamoService $prestamoService): JsonResponse
    {
        $reserva = Reserva::with('material')->findOrFail($reserva);

        if ($reserva->estado !== 'pendiente') {
            return response()->json(['message' => 'Solo reservas pendientes pueden ser aprobadas'], 422);
        }

        $request->validate([
            'dias' => 'nullable|integer|min:1|max:30',
        ]);

        $dias = $request->input('dias', 14);
        $fechaDevolucion = now()->addDays($dias);

        try {
            $prestamoService->crearPrestamo(
                $reserva->socio_id,
                $reserva->material_id,
                1,
                $fechaDevolucion
            );

            $reserva->update([
                'estado' => 'aprobada',
                'fecha_vencimiento' => $fechaDevolucion,
            ]);

            $reserva->material->decrement('disponibilidad_reservada');

            return response()->json(['message' => 'Reserva aprobada y prestamo creado']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}