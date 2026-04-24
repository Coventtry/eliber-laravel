<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    private const PERMISOS_ADMINISTRATIVOS = [
        'gestionar-usuarios',
        'gestionar-materiales',
        'gestionar-socios',
        'gestionar-prestamos',
        'gestionar-areas',
        'gestionar-noticias',
        'gestionar-anotaciones',
        'ver-reportes',
    ];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        $usuarios = User::with('roles')
            ->when($request->search, fn($q, $s) =>
                $q->where('nombre', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('usuario', 'like', "%{$s}%")
            )
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString()
            ->through(fn($u) => [
                'id'      => $u->id,
                'nombre'  => $u->nombre,
                'email'   => $u->email,
                'usuario' => $u->usuario,
                'activo'  => $u->activo,
                'rol'     => $u->roles->first()?->name ?? 'sin rol',
            ]);

        return Inertia::render('Usuarios/Index', [
            'usuarios' => $usuarios,
            'filters'  => $request->only(['search']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('Usuarios/Create', [
            'roles' => ['admin', 'bibliotecario'],
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $user = User::create([
            'name'           => $request->nombre,
            'nombre'         => $request->nombre,
            'email'          => $request->email,
            'usuario'        => $request->usuario,
            'password'       => $request->password,
            'institucion_id' => auth()->user()->institucion_id,
            'activo'         => true,
        ]);

        $user->assignRole($request->rol ?? 'bibliotecario');

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user): Response
    {
        $this->authorize('update', $user);

        return Inertia::render('Usuarios/Edit', [
            'usuario'          => $user->only('id', 'nombre', 'email', 'usuario', 'activo'),
            'permisos_usuario' => $user->getDirectPermissions()->pluck('name'),
            'todos_permisos'   => self::PERMISOS_ADMINISTRATIVOS,
            'es_admin_target'  => $user->hasRole('admin'),
            'es_yo'            => $user->id === auth()->id(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $data = $request->only('nombre', 'email', 'usuario');
        $data['name'] = $request->nombre;

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $user->update($data);

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado.');
    }

    public function updatePermisos(Request $request, User $user): RedirectResponse
    {
        abort_if(!auth()->user()->hasRole('admin'), 403);
        abort_if($user->hasRole('admin'), 403, 'No se pueden editar los permisos de un administrador.');

        $request->validate([
            'permisos'   => 'array',
            'permisos.*' => 'string|in:' . implode(',', self::PERMISOS_ADMINISTRATIVOS),
        ]);

        $user->syncPermissions($request->permisos ?? []);

        return back()->with('success', 'Permisos actualizados.');
    }

    public function toggleActivo(User $user): RedirectResponse
    {
        abort_if(!auth()->user()->hasRole('admin'), 403);
        abort_if($user->id === auth()->id(), 403, 'No podés desactivarte a vos mismo.');

        $user->update(['activo' => !$user->activo]);
        $msg = $user->activo ? 'Usuario activado.' : 'Usuario desactivado.';

        return back()->with('success', $msg);
    }
}
