<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Material;
use App\Models\Prestamo;
use App\Models\Reserva;
use App\Services\ReservaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AlumnoController extends Controller
{
    public function dashboard(): Response
    {
        $user = auth()->user();
        $socioId = $user->socio_id;

        $reservas = $socioId
            ? Reserva::with('material')
                ->where('socio_id', $socioId)
                ->orderByDesc('fecha_reserva')
                ->take(5)
                ->get()
                ->map(fn ($r) => [
                    'id' => $r->id,
                    'material' => $r->material->titulo,
                    'estado' => $r->estado,
                    'fecha' => $r->fecha_reserva->format('d/m/Y'),
                ])
            : collect();

        return Inertia::render('Alumno/Dashboard', [
            'reservas_recientes' => $reservas,
            'tiene_socio' => (bool) $socioId,
        ]);
    }

    public function misPrestamos(): Response
    {
        $user    = auth()->user();
        $socioId = $user->socio_id;

        $activos = $socioId
            ? Prestamo::with('material')
                ->where('socio_id', $socioId)
                ->whereIn('estado', ['activo', 'atrasado'])
                ->orderByDesc('fecha_devolucion')
                ->get()
                ->map(fn($p) => [
                    'id'           => $p->id,
                    'material'     => $p->material->titulo,
                    'estado'       => $p->estado,
                    'fecha_inicio' => $p->fecha_prestamo->format('d/m/Y'),
                    'fecha_venc'   => $p->fecha_devolucion->format('d/m/Y'),
                ])
            : collect();

        $historial = $socioId
            ? Prestamo::with('material')
                ->where('socio_id', $socioId)
                ->whereIn('estado', ['devuelto'])
                ->orderByDesc('fecha_devolucion')
                ->paginate(15)
                ->through(fn($p) => [
                    'id'           => $p->id,
                    'material'     => $p->material->titulo,
                    'estado'       => $p->estado,
                    'fecha_inicio' => $p->fecha_prestamo->format('d/m/Y'),
                    'fecha_venc'   => $p->fecha_devolucion->format('d/m/Y'),
                ])
            : collect();

        return Inertia::render('Alumno/MisPrestamos', [
            'activos'    => $activos,
            'historial'  => $historial,
            'tiene_socio' => (bool) $socioId,
        ]);
    }

    public function misReservas(): Response
    {
        $user = auth()->user();
        $socioId = $user->socio_id;

        $reservas = $socioId
            ? Reserva::with('material')
                ->where('socio_id', $socioId)
                ->orderByDesc('fecha_reserva')
                ->paginate(15)
                ->through(fn ($r) => [
                    'id' => $r->id,
                    'material' => $r->material->titulo,
                    'estado' => $r->estado,
                    'fecha_reserva' => $r->fecha_reserva->format('d/m/Y'),
                    'fecha_vencimiento' => $r->fecha_vencimiento?->format('d/m/Y'),
                ])
            : collect();

        return Inertia::render('Alumno/MisReservas', [
            'reservas' => $reservas,
            'tiene_socio' => (bool) $socioId,
        ]);
    }

    public function catalogo(Request $request): Response
    {
        $areas = Area::orderBy('nombre')->get(['id', 'nombre']);

        $materiales = Material::with('area')
            ->where('disponibilidad', '>', 0)
            ->when($request->search, fn ($q, $s) => $q->where(fn ($q2) => $q2->where('titulo', 'like', "%{$s}%")
                ->orWhere('autor', 'like', "%{$s}%")
            )
            )
            ->when($request->area_id, fn ($q, $a) => $q->where('area_id', $a))
            ->orderBy('titulo')
            ->paginate(24)
            ->withQueryString()
            ->through(fn ($m) => [
                'id' => $m->id,
                'titulo' => $m->titulo,
                'autor' => $m->autor,
                'area' => $m->area?->nombre,
                'area_id' => $m->area_id,
                'categoria' => $m->categoria,
                'disponibilidad' => $m->disponibilidad,
                'anio' => $m->anio_publicacion,
            ]);

        return Inertia::render('Alumno/Catalogo', [
            'materiales' => $materiales,
            'areas' => $areas,
            'filters' => $request->only(['search', 'area_id']),
            'tiene_socio' => (bool) auth()->user()->socio_id,
        ]);
    }

    public function reservar(Request $request, ReservaService $service): RedirectResponse
    {
        $request->validate([
            'material_id' => 'required|exists:materiales,id',
        ]);

        $user = auth()->user();

        if (! $user->socio_id) {
            return back()->with('error', 'Tu cuenta no está vinculada a un socio. Contactá al bibliotecario.');
        }

        try {
            $service->crearReserva($user->socio_id, $request->material_id);

            return back()->with('success', 'Reserva realizada correctamente.');
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first() ?? 'Error de validación';
            return back()->with('error', $message);
        }
    }

    public function cancelarReserva(Reserva $reserva, ReservaService $service): RedirectResponse
    {
        $user = auth()->user();

        if ($reserva->socio_id !== $user->socio_id) {
            abort(403);
        }

        if (! in_array($reserva->estado, ['pendiente', 'aprobada'])) {
            return back()->with('error', 'Esta reserva no se puede cancelar.');
        }

        $service->cancelarReserva($reserva);

        return back()->with('success', 'Reserva cancelada.');
    }
}
