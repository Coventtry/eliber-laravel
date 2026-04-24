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
        $user = auth()->user();
        return Inertia::render('Perfil/Edit', [
            'perfil' => [
                'nombre'       => $user->nombre,
                'usuario'      => $user->usuario,
                'email'        => $user->email,
                'picture_url'  => $user->picture_url,
                'wallpaper_url'=> $user->wallpaper_url,
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'email'     => 'nullable|email|max:255',
            'picture'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'wallpaper' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $user = auth()->user();
        $data = [];

        if ($request->filled('email')) {
            $data['email'] = $request->email;
        }

        if ($request->hasFile('picture')) {
            if ($user->picture) {
                Storage::disk('public')->delete('uploads/' . $user->picture);
            }
            $filename = $request->file('picture')->store('uploads', 'public');
            $data['picture'] = basename($filename);
        }

        if ($request->hasFile('wallpaper')) {
            if ($user->wallpaper) {
                Storage::disk('public')->delete('wallpapers/' . $user->wallpaper);
            }
            $filename = $request->file('wallpaper')->store('wallpapers', 'public');
            $data['wallpaper'] = basename($filename);
        }

        if ($data) {
            $user->update($data);
        }

        return redirect()->route('perfil.edit')->with('success', 'Perfil actualizado correctamente.');
    }
}
