<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AreaController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Areas/Index', [
            'areas' => Area::orderBy('nombre')->paginate(30),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Areas/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Area::class);
        $request->validate([
            'codigo_dewey' => 'required|string|max:50|unique:areas',
            'nombre'       => 'required|string|max:255|unique:areas',
            'Abreviado'    => 'nullable|string|max:50',
        ]);

        Area::create([
            ...$request->only('codigo_dewey', 'nombre', 'Abreviado'),
            'institucion_id' => $request->user()->institucion_id,
        ]);
        return redirect()->route('areas.index')->with('success', 'Área creada.');
    }

    public function edit(Area $area): Response
    {
        return Inertia::render('Areas/Edit', ['area' => $area]);
    }

    public function update(Request $request, Area $area): RedirectResponse
    {
        $this->authorize('update', $area);
        $request->validate([
            'codigo_dewey' => 'required|string|max:50|unique:areas,codigo_dewey,' . $area->id,
            'nombre'       => 'required|string|max:255|unique:areas,nombre,' . $area->id,
            'Abreviado'    => 'nullable|string|max:50',
        ]);

        $area->update($request->only('codigo_dewey', 'nombre', 'Abreviado'));
        return redirect()->route('areas.index')->with('success', 'Área actualizada.');
    }

    public function destroy(Area $area): RedirectResponse
    {
        $this->authorize('delete', $area);
        $area->delete();
        return redirect()->route('areas.index')->with('success', 'Área eliminada.');
    }
}
