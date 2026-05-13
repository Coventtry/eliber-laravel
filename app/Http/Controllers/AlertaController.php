<?php

namespace App\Http\Controllers;

use App\Models\Alerta;
use App\Models\Anotacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AlertaController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Alerta::class);
        $alertas = Alerta::with('prestamo')
            ->when($request->tipo, fn ($q, $t) => $q->where('tipo', $t))
            ->when($request->has('leida') && $request->leida !== '', fn ($q) => $q->where('leida', $request->leida))
            ->orderByDesc('fecha_alerta')
            ->paginate(20)
            ->withQueryString()
            ->through(fn ($a) => [
                'id' => $a->id,
                'tipo' => $a->tipo,
                'descripcion' => $a->descripcion,
                'fecha_alerta' => $a->fecha_alerta->format('d/m/Y H:i'),
                'leida' => $a->leida,
                'prestamo_id' => $a->prestamo_id,
            ]);

        return Inertia::render('Alertas/Index', [
            'alertas' => $alertas,
            'filters' => $request->only(['tipo', 'leida']),
        ]);
    }

    public function marcarLeida(Alerta $alerta): RedirectResponse
    {
        $this->authorize('update', Alerta::class);
        $alerta->update(['leida' => true]);

        return back()->with('success', 'Alerta marcada como leída.');
    }

    public function marcarTodasLeidas(): RedirectResponse
    {
        $this->authorize('update', Alerta::class);
        Alerta::noLeidas()->update(['leida' => true]);

        return back()->with('success', 'Todas las alertas marcadas como leídas.');
    }

    public function bajaAlerta(Request $request, Alerta $alerta): RedirectResponse
    {
        abort_if($alerta->institucion_id !== tenantId(), 403);

        $request->validate([
            'observaciones' => 'required|string|max:500',
        ]);

        $prestamo = $alerta->prestamo()->with('material')->first();

        abort_if(! $prestamo || ! $prestamo->material, 404, 'El préstamo o material no existe.');

        $material = $prestamo->material;

        // Descontar una unidad del material
        $material->decrement('disponibilidad');

        // Registrar la anotación con el motivo
        Anotacion::create([
            'anotacion' => "Baja de material — {$material->titulo}: {$request->observaciones}",
            'fecha' => now(),
            'institucion_id' => auth()->user()->institucion_id,
        ]);

        // Cerrar la alerta
        $alerta->update(['leida' => true]);

        return back()->with('success', "Baja registrada para \"{$material->titulo}\".");
    }
}
