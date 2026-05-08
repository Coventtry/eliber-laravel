<?php

namespace App\Http\Controllers;

use App\Models\CategoriaFisica;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CategoriaFisicaController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Categorias/Index', [
            'categorias' => CategoriaFisica::orderBy('nombre')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Categorias/Create', [
            'categorias' => CategoriaFisica::orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre' => [
                'required', 'string', 'max:100',
                Rule::unique('categorias_fisicas')->where('institucion_id', tenantId()),
            ],
        ], ['nombre.unique' => 'Ya existe una categoría con ese nombre.']);

        CategoriaFisica::create([
            'nombre' => $request->nombre,
            'institucion_id' => tenantId(),
        ]);

        return redirect()->route('categorias.create')->with('success', 'Categoría creada.');
    }

    public function edit(CategoriaFisica $categoria): Response
    {
        return Inertia::render('Categorias/Edit', ['categoria' => $categoria]);
    }

    public function update(Request $request, CategoriaFisica $categoria): RedirectResponse
    {
        $request->validate([
            'nombre' => [
                'required', 'string', 'max:100',
                Rule::unique('categorias_fisicas')->where('institucion_id', tenantId())->ignore($categoria->id),
            ],
        ], ['nombre.unique' => 'Ya existe una categoría con ese nombre.']);

        $categoria->update(['nombre' => $request->nombre]);

        return redirect()->route('categorias.index')->with('success', 'Categoría actualizada.');
    }

    public function destroy(CategoriaFisica $categoria): RedirectResponse
    {
        $categoria->delete();

        return redirect()->route('categorias.create')->with('success', 'Categoría eliminada.');
    }
}
