<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class PasswordResetController extends Controller
{
    public function showForm()
    {
        return inertia('Auth/ResetPassword');
    }

    public function reset(Request $request)
    {
        $request->validate([
            'usuario' => 'required|string|exists:users,usuario',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'usuario.exists' => 'El usuario no existe.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $user = User::where('usuario', $request->usuario)->first();

        if (!$user) {
            return back()->withErrors(['usuario' => 'El usuario no existe.']);
        }

        $user->password = $request->password;
        $user->save();

        return redirect()->route('login')->with('success', 'Contraseña restablecida correctamente. Podés iniciar sesión.');
    }
}