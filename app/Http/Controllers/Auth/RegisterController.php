<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Institucion;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre'                => 'required|string|max:255',
            'apellido'              => 'required|string|max:100',
            'email'                 => 'required|email|max:255|unique:users,email',
            'usuario'               => 'required|string|max:50|unique:users,usuario|regex:/^[a-zA-Z0-9._-]+$/',
            'password'              => ['required', 'confirmed', Password::min(8)],
            'password_confirmation' => 'required',
            'rol'                   => 'required|in:alumno,bibliotecario',
            'institucion_id'        => 'required|exists:instituciones,id,estado,activa,deleted_at,NULL',
            'anio'                  => 'required_if:rol,alumno|nullable|integer|min:1|max:6',
            'division'              => 'required_if:rol,alumno|nullable|integer|min:1|max:6',
        ], [
            'usuario.regex'          => 'El usuario solo puede contener letras, números, puntos, guiones y guiones bajos.',
            'institucion_id.required'=> 'Seleccioná la institución a la que pertenecés.',
            'institucion_id.exists'  => 'La institución seleccionada no es válida.',
            'anio.required_if'       => 'El año es obligatorio para alumnos.',
            'division.required_if'   => 'La división es obligatoria para alumnos.',
        ]);

        $usuario = User::create([
            'name'           => $request->nombre,
            'nombre'         => $request->nombre,
            'apellido'       => $request->apellido,
            'email'          => $request->email,
            'usuario'        => $request->usuario,
            'password'       => $request->password,
            'institucion_id' => $request->institucion_id,
            'activo'         => false,
            'anio'           => $request->rol === 'alumno' ? $request->anio : null,
            'division'       => $request->rol === 'alumno' ? $request->division : null,
        ]);

        $usuario->assignRole($request->rol);

        $mensaje = $request->rol === 'bibliotecario'
            ? 'Tu cuenta fue creada. Un administrador revisará tu solicitud y te habilitará el acceso.'
            : 'Tu cuenta fue creada. Un bibliotecario revisará tu solicitud y te habilitará el acceso.';

        return redirect()->route('login')->with('info', $mensaje);
    }
}
