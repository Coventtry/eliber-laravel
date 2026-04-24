<?php

namespace App\Http\Controllers;

use App\Models\Alerta;
use App\Models\Material;
use App\Models\Prestamo;
use App\Models\Reserva;
use App\Models\Socio;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    private const ROLES_DISPONIBLES = ['admin', 'bibliotecario', 'alumno'];

    // ──────────────────────────── Dashboard ────────────────────────────

    public function dashboard(): Response
    {
        $instId = auth()->user()->institucion_id;

        $stats = [
            'socios_activos'   => Socio::where('activo', true)->count(),
            'prestamos_mes'    => Prestamo::where('institucion_id', $instId)
                                    ->whereMonth('fecha_prestamo', now()->month)
                                    ->whereYear('fecha_prestamo', now()->year)
                                    ->count(),
            'materiales_total' => Material::sum('disponibilidad'),
            'alertas_sin_leer' => Alerta::noLeidas()->count(),
        ];

        $reservas_pendientes = Reserva::with(['material', 'socio'])
            ->where('estado', 'pendiente')
            ->orderByDesc('fecha_reserva')
            ->take(5)
            ->get()
            ->map(fn($r) => [
                'id'       => $r->id,
                'material' => $r->material->titulo,
                'socio'    => $r->socio->full_name,
                'fecha'    => $r->fecha_reserva->format('d/m/Y'),
            ]);

        $proximos_vencer = Prestamo::with(['socio', 'material'])
            ->vencimientoProximo(4)
            ->get()
            ->map(fn($p) => [
                'id'              => $p->id,
                'socio'           => $p->socio->full_name,
                'material'        => $p->material->titulo,
                'fecha_devolucion' => $p->fecha_devolucion->format('d/m/Y'),
            ]);

        return Inertia::render('Admin/Dashboard', compact(
            'stats', 'reservas_pendientes', 'proximos_vencer'
        ));
    }

    // ──────────────────────────── Usuarios ─────────────────────────────

    public function usuarios(Request $request): Response
    {
        $instId = auth()->user()->institucion_id;

        $usuarios = User::with('roles')
            ->where('institucion_id', $instId)
            ->when($request->search, fn($q, $s) =>
                $q->where(fn($q2) =>
                    $q2->where('nombre', 'like', "%{$s}%")
                       ->orWhere('email',   'like', "%{$s}%")
                       ->orWhere('usuario', 'like', "%{$s}%")
                )
            )
            ->when($request->rol, fn($q, $r) =>
                $q->whereHas('roles', fn($q2) => $q2->where('name', $r))
            )
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString()
            ->through(fn($u) => [
                'id'        => $u->id,
                'nombre'    => $u->nombre,
                'email'     => $u->email,
                'usuario'   => $u->usuario,
                'activo'    => $u->activo,
                'rol'       => $u->roles->first()?->name ?? 'sin rol',
                'socio_id'  => $u->socio_id,
                'picture_url' => $u->picture_url,
            ]);

        $socios = Socio::orderBy('apellido')
            ->get(['id', 'nombre', 'apellido'])
            ->map(fn($s) => ['id' => $s->id, 'nombre' => $s->full_name]);

        return Inertia::render('Admin/Usuarios/Index', [
            'usuarios' => $usuarios,
            'socios'   => $socios,
            'roles'    => self::ROLES_DISPONIBLES,
            'filters'  => $request->only(['search', 'rol']),
        ]);
    }

    public function storeUsuario(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre'   => 'required|string|max:120',
            'email'    => 'required|email|unique:users,email',
            'usuario'  => 'required|string|max:60|unique:users,usuario',
            'password' => 'required|string|min:6',
            'rol'      => 'required|in:admin,bibliotecario,alumno',
            'socio_id' => 'nullable|exists:socios,id',
            'picture'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $data = [
            'name'           => $request->nombre,
            'nombre'         => $request->nombre,
            'email'          => $request->email,
            'usuario'        => $request->usuario,
            'password'       => $request->password,
            'activo'         => true,
            'institucion_id' => auth()->user()->institucion_id,
            'socio_id'       => $request->rol === 'alumno' ? $request->socio_id : null,
        ];

        if ($request->hasFile('picture')) {
            $data['picture'] = basename($request->file('picture')->store('uploads', 'public'));
        }

        $user = User::create($data);
        $user->assignRole($request->rol);

        return back()->with('success', "Usuario «{$request->nombre}» creado correctamente.");
    }

    public function updateUsuario(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'nombre'   => 'required|string|max:120',
            'email'    => "required|email|unique:users,email,{$user->id}",
            'usuario'  => "required|string|max:60|unique:users,usuario,{$user->id}",
            'password' => 'nullable|string|min:6',
            'rol'      => 'required|in:admin,bibliotecario,alumno',
            'socio_id' => 'nullable|exists:socios,id',
            'picture'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $data = [
            'name'     => $request->nombre,
            'nombre'   => $request->nombre,
            'email'    => $request->email,
            'usuario'  => $request->usuario,
            'socio_id' => $request->rol === 'alumno' ? $request->socio_id : null,
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        if ($request->hasFile('picture')) {
            if ($user->picture) {
                Storage::disk('public')->delete('uploads/' . $user->picture);
            }
            $data['picture'] = basename($request->file('picture')->store('uploads', 'public'));
        }

        $user->update($data);
        $user->syncRoles([$request->rol]);

        return back()->with('success', "Usuario «{$request->nombre}» actualizado.");
    }

    public function destroyUsuario(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No podés eliminar tu propio usuario.');
        }
        $nombre = $user->nombre;
        if ($user->picture) {
            Storage::disk('public')->delete('uploads/' . $user->picture);
        }
        $user->delete();
        return back()->with('success', "Usuario «{$nombre}» eliminado.");
    }

    public function toggleActivoUsuario(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No podés desactivar tu propio usuario.');
        }
        $user->update(['activo' => !$user->activo]);
        return redirect()->route('admin.usuarios')
            ->with('success', $user->activo ? 'Usuario activado.' : 'Usuario desactivado.');
    }
}
