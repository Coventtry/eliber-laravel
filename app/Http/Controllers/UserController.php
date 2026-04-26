<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Socio;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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

        $usuarios = User::with(['roles', 'socio'])
            ->where('institucion_id', auth()->user()->institucion_id)
            ->whereHas('roles', fn($q) => $q->where('name', 'alumno'))
            ->when($request->busqueda, fn($q, $s) =>
                $q->where('nombre', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('usuario', 'like', "%{$s}%")
            )
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString()
            ->through(fn($u) => [
                'id'       => $u->id,
                'nombre'   => $u->nombre,
                'email'    => $u->email,
                'usuario'  => $u->usuario,
                'activo'   => $u->activo,
                'rol'      => $u->roles->first()?->name ?? 'sin rol',
                'socio_id' => $u->socio_id,
                'socio'    => $u->socio ? $u->socio->nombre . ' ' . $u->socio->apellido : null,
            ]);

        $pendientes = User::with('roles')
            ->where('institucion_id', auth()->user()->institucion_id)
            ->where('activo', false)
            ->whereHas('roles', fn($q) => $q->where('name', 'alumno'))
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($u) => [
                'id'        => $u->id,
                'nombre'    => $u->nombre,
                'email'     => $u->email,
                'usuario'   => $u->usuario,
                'rol'       => $u->roles->first()?->name ?? 'sin rol',
                'creado_at' => $u->created_at->format('d/m/Y H:i'),
            ]);

        return Inertia::render('Usuarios/Index', [
            'usuarios'   => $usuarios,
            'filters'    => $request->only(['busqueda']),
            'pendientes' => $pendientes,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', User::class);

        $vinculados = User::whereNotNull('socio_id')->pluck('socio_id')->toArray();

        $socios = Socio::where('institucion_id', auth()->user()->institucion_id)
            ->orderBy('apellido')->orderBy('nombre')
            ->get(['id', 'nombre', 'apellido', 'activo'])
            ->map(fn($s) => [
                'id'    => $s->id,
                'label' => $s->apellido . ', ' . $s->nombre
                    . (!$s->activo ? ' (inactivo)' : '')
                    . (in_array($s->id, $vinculados) ? ' ⚠ ya vinculado' : ''),
            ]);

        return Inertia::render('Usuarios/Create', [
            'roles'  => ['admin', 'bibliotecario', 'alumno'],
            'socios' => $socios,
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
            'socio_id'       => $request->rol === 'alumno' ? $request->socio_id : null,
        ]);

        $user->assignRole($request->rol ?? 'bibliotecario');

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user): Response
    {
        $this->authorize('update', $user);

        $esAlumno = $user->hasRole('alumno');

        $vinculadosOtros = User::whereNotNull('socio_id')
            ->where('id', '!=', $user->id)
            ->pluck('socio_id')->toArray();

        $socios = $esAlumno
            ? Socio::where('institucion_id', auth()->user()->institucion_id)
                ->orderBy('apellido')->orderBy('nombre')
                ->get(['id', 'nombre', 'apellido', 'activo'])
                ->map(fn($s) => [
                    'id'    => $s->id,
                    'label' => $s->apellido . ', ' . $s->nombre
                        . (!$s->activo ? ' (inactivo)' : '')
                        . (in_array($s->id, $vinculadosOtros) ? ' ⚠ ya vinculado' : ''),
                ])
            : collect();

        return Inertia::render('Usuarios/Edit', [
            'usuario'          => array_merge(
                $user->only('id', 'nombre', 'apellido', 'email', 'usuario', 'activo', 'socio_id', 'anio', 'division'),
                ['rol' => $user->roles->first()?->name ?? 'sin rol']
            ),
            'permisos_usuario' => $user->getDirectPermissions()->pluck('name'),
            'todos_permisos'   => self::PERMISOS_ADMINISTRATIVOS,
            'es_admin_target'  => $user->hasRole('admin'),
            'es_alumno_target' => $esAlumno,
            'es_yo'            => $user->id === auth()->id(),
            'socios'           => $socios,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $datos = $request->only('nombre', 'apellido', 'email', 'usuario');
        $datos['name'] = $request->nombre;

        if ($request->filled('password')) {
            $datos['password'] = $request->password;
        }

        if ($user->hasRole('alumno')) {
            $datos['anio']    = $request->anio;
            $datos['division'] = $request->division;
        }

        $user->update($datos);

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

    public function aprobar(User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $socioCreado = null;

        DB::transaction(function () use ($user, &$socioCreado) {
            $datos = ['activo' => true];

            if ($user->hasRole('alumno') && !$user->socio_id) {
                $socioCreado = Socio::create([
                    'nombre'         => $user->nombre,
                    'apellido'       => $user->apellido ?? '',
                    'email'          => $user->email,
                    'anio'           => $user->anio,
                    'division'       => $user->division,
                    'activo'         => true,
                    'institucion_id' => $user->institucion_id,
                ]);
                $datos['socio_id'] = $socioCreado->id;
            }

            $user->update($datos);
        });

        if ($socioCreado) {
            return redirect()->route('socios.edit', $socioCreado->id)
                ->with('success', "Cuenta de {$user->nombre} habilitada. Completá los datos del socio (apellido, año, división).");
        }

        return back()->with('success', "Usuario {$user->nombre} activado.");
    }

    public function toggleActivo(User $user): RedirectResponse
    {
        $usuarioActual = auth()->user();

        if (!$usuarioActual->hasRole('admin') && !$usuarioActual->hasRole('bibliotecario')) {
            return back()->with('error', 'No tenés permiso para realizar esta acción.');
        }
        if ($user->id === $usuarioActual->id) {
            return back()->with('error', 'No podés desactivar tu propio usuario.');
        }
        if ($user->hasRole('admin') && !$usuarioActual->hasRole('admin')) {
            return back()->with('error', 'Solo un administrador puede desactivar a otro administrador.');
        }

        $user->update(['activo' => !$user->activo]);
        return redirect()->route('usuarios.edit', $user->id)
            ->with('success', $user->activo ? 'Usuario activado.' : 'Usuario desactivado.');
    }
}
