<?php

namespace App\Http\Controllers;

use App\Models\Anotacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnotacionController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Anotaciones/Index', [
            'anotaciones' => Anotacion::orderByDesc('fecha')->paginate(20)->through(fn($a) => [
                'id'        => $a->id,
                'anotacion' => $a->anotacion,
                'fecha'     => $a->fecha?->format('d/m/Y H:i'),
            ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Anotaciones/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', \App\Models\Anotacion::class);
        $request->validate(['anotacion' => 'required|string|max:255']);
        Anotacion::create([
            'anotacion' => $request->anotacion,
            'institucion_id' => $request->user()->institucion_id,
        ]);
        return redirect()->route('anotaciones.index')->with('success', 'Anotación guardada.');
    }
}
