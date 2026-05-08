<?php

namespace App\Http\Controllers;

use App\Models\Noticia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class NoticiaController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Noticias/Index', [
            'noticias' => Noticia::orderByDesc('id')->paginate(12)->through(fn ($n) => [
                'id' => $n->id,
                'titulo' => $n->titulo,
                'descripcion' => $n->descripcion,
                'imagen_url' => $n->imagen_url,
                'fecha' => $n->fecha?->format('d/m/Y'),
            ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Noticias/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Noticia::class);
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string|max:255',
            'imagen' => 'nullable|image|max:2048',
        ]);

        $nombreImagen = null;
        if ($request->hasFile('imagen')) {
            $nombreImagen = $request->file('imagen')->store('noticias', 'public');
            $nombreImagen = basename($nombreImagen);
        }

        Noticia::create([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'imagen' => $nombreImagen,
            'institucion_id' => $request->user()->institucion_id,
        ]);

        return redirect()->route('noticias.index')->with('success', 'Noticia publicada.');
    }

    public function edit(Noticia $noticia): Response
    {
        return Inertia::render('Noticias/Edit', [
            'noticia' => array_merge($noticia->toArray(), ['imagen_url' => $noticia->imagen_url]),
        ]);
    }

    public function update(Request $request, Noticia $noticia): RedirectResponse
    {
        $this->authorize('update', $noticia);
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string|max:255',
            'imagen' => 'nullable|image|max:2048',
        ]);

        $data = $request->only('titulo', 'descripcion');

        if ($request->hasFile('imagen')) {
            if ($noticia->imagen) {
                Storage::disk('public')->delete('noticias/'.$noticia->imagen);
            }
            $data['imagen'] = basename($request->file('imagen')->store('noticias', 'public'));
        }

        $noticia->update($data);

        return redirect()->route('noticias.index')->with('success', 'Noticia actualizada.');
    }

    public function destroy(Noticia $noticia): RedirectResponse
    {
        $this->authorize('delete', $noticia);
        if ($noticia->imagen) {
            Storage::disk('public')->delete('noticias/'.$noticia->imagen);
        }
        $noticia->delete();

        return redirect()->route('noticias.index')->with('success', 'Noticia eliminada.');
    }
}
