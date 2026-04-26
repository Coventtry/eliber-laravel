<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Institucion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    public function create(): Response
    {
        $instituciones = Institucion::where('estado', 'activa')
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return Inertia::render('Auth/Login', ['instituciones' => $instituciones]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'usuario'  => 'required|string',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt(['usuario' => $request->usuario, 'password' => $request->password], $request->boolean('remember'))) {
            return back()->withErrors(['usuario' => 'Las credenciales no son correctas.'])->onlyInput('usuario');
        }

        $usuario = Auth::user();

        if (!$usuario->activo) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return back()
                ->withErrors(['usuario' => 'Tu cuenta está pendiente de aprobación por el bibliotecario.'])
                ->onlyInput('usuario');
        }

        $request->session()->regenerate();

        if ($usuario->hasRole('admin')) {
            return redirect()->intended(route('admin.dashboard'));
        }
        if ($usuario->hasRole('alumno')) {
            return redirect()->intended(route('alumno.dashboard'));
        }
        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing');
    }
}
