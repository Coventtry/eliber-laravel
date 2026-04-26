<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PerfilController extends Controller
{
    public function edit(): Response
    {
        $usuario  = auth()->user();
        $esAlumno = $usuario->hasRole('alumno');
        return Inertia::render('Perfil/Edit', [
            'perfil' => [
                'nombre'       => $usuario->nombre,
                'apellido'     => $usuario->apellido,
                'usuario'      => $usuario->usuario,
                'email'        => $usuario->email,
                'anio'         => $usuario->anio,
                'division'     => $usuario->division,
                'picture_url'  => $usuario->picture_url,
                'wallpaper_url'=> $usuario->wallpaper_url,
            ],
            'es_alumno' => $esAlumno,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'email'     => 'nullable|email|max:255',
            'apellido'  => 'nullable|string|max:100',
            'anio'      => 'nullable|integer|min:1|max:6',
            'division'  => 'nullable|integer|min:1|max:6',
            'picture'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'wallpaper' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $usuario = auth()->user();
        $datos = [];

        if ($request->filled('email')) {
            $datos['email'] = $request->email;
        }

        if ($request->filled('apellido')) {
            $datos['apellido'] = $request->apellido;
        }


        if ($request->hasFile('picture')) {
            if ($usuario->picture) {
                Storage::disk('public')->delete('uploads/' . $usuario->picture);
            }
            $nombreArchivo = $request->file('picture')->store('uploads', 'public');
            $datos['picture'] = basename($nombreArchivo);
        }

        if ($request->hasFile('wallpaper')) {
            if ($usuario->wallpaper) {
                Storage::disk('public')->delete('wallpapers/' . $usuario->wallpaper);
            }
            $nombreArchivo = $request->file('wallpaper')->store('wallpapers', 'public');
            $datos['wallpaper'] = basename($nombreArchivo);
        }

        if ($datos) {
            $usuario->update($datos);
        }

        return redirect()->route('perfil.edit')->with('success', 'Perfil actualizado correctamente.');
    }
}
