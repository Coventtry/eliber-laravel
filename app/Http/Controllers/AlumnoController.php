<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Reserva;
use Inertia\Inertia;
use Inertia\Response;

class AlumnoController extends Controller
{
    public function dashboard(): Response
    {
        $user    = auth()->user();
        $socioId = $user->socio_id;

        $reservas = $socioId
            ? Reserva::with('material')
                ->where('socio_id', $socioId)
                ->orderByDesc('fecha_reserva')
                ->take(5)
                ->get()
                ->map(fn($r) => [
                    'id'       => $r->id,
                    'material' => $r->material->titulo,
                    'estado'   => $r->estado,
                    'fecha'    => $r->fecha_reserva->format('d/m/Y'),
                ])
            : collect();

        return Inertia::render('Alumno/Dashboard', [
            'reservas_recientes' => $reservas,
            'tiene_socio'        => (bool) $socioId,
        ]);
    }

    public function misReservas(): Response
    {
        $user    = auth()->user();
        $socioId = $user->socio_id;

        $reservas = $socioId
            ? Reserva::with('material')
                ->where('socio_id', $socioId)
                ->orderByDesc('fecha_reserva')
                ->paginate(15)
                ->through(fn($r) => [
                    'id'                => $r->id,
                    'material'          => $r->material->titulo,
                    'estado'            => $r->estado,
                    'fecha_reserva'     => $r->fecha_reserva->format('d/m/Y'),
                    'fecha_vencimiento' => $r->fecha_vencimiento?->format('d/m/Y'),
                ])
            : collect();

        return Inertia::render('Alumno/MisReservas', [
            'reservas'    => $reservas,
            'tiene_socio' => (bool) $socioId,
        ]);
    }

    public function catalogo(): Response
    {
        $materiales = Material::with('area')
            ->where('disponibilidad', '>', 0)
            ->orderBy('titulo')
            ->paginate(24)
            ->through(fn($m) => [
                'id'             => $m->id,
                'titulo'         => $m->titulo,
                'autor'          => $m->autor,
                'area'           => $m->area?->nombre,
                'categoria'      => $m->categoria,
                'disponibilidad' => $m->disponibilidad,
                'anio'           => $m->anio_publicacion,
            ]);

        return Inertia::render('Alumno/Catalogo', [
            'materiales' => $materiales,
        ]);
    }
}
