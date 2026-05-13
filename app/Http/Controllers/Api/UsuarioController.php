<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class UsuarioController extends Controller
{
    private const PERMISOS_ADMINISTRATIVOS = [
        'gestionar-usuarios', 'gestionar-materiales', 'gestionar-socios',
        'gestionar-prestamos', 'gestionar-areas', 'gestionar-noticias',
        'gestionar-anotaciones', 'ver-reportes',
    ];

    #[OA\Get(
        path: '/usuarios',
        summary: 'Listar usuarios de la institución',
        description: 'Solo admin o usuarios con permiso `gestionar-usuarios`.',
        tags: ['Usuarios'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string'), description: 'Filtrar por nombre, email o usuario'),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista paginada de usuarios',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'nombre', type: 'string', example: 'Ana García'),
                        new OA\Property(property: 'email', type: 'string', example: 'ana@escuela.edu.ar'),
                        new OA\Property(property: 'usuario', type: 'string', example: 'anagarcia'),
                        new OA\Property(property: 'activo', type: 'boolean', example: true),
                        new OA\Property(property: 'rol', type: 'string', example: 'bibliotecario'),
                    ])),
                    new OA\Property(property: 'current_page', type: 'integer', example: 1),
                    new OA\Property(property: 'total', type: 'integer', example: 5),
                ])
            ),
            new OA\Response(response: 401, description: 'No autenticado'),
            new OA\Response(response: 403, description: 'Sin permiso'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $usuarios = User::with('roles')
            ->where('institucion_id', auth()->user()->institucion_id)
            ->when($request->search, fn($q, $s) =>
                $q->where('nombre', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('usuario', 'like', "%{$s}%")
            )
            ->orderBy('nombre')
            ->paginate(20);

        return response()->json($usuarios->through(fn($u) => [
            'id'      => $u->id,
            'nombre'  => $u->nombre,
            'email'   => $u->email,
            'usuario' => $u->usuario,
            'activo'  => $u->activo,
            'rol'     => $u->roles->first()?->name ?? 'sin rol',
        ]));
    }

    #[OA\Get(
        path: '/usuarios/{id}',
        summary: 'Detalle de un usuario',
        tags: ['Usuarios'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Usuario encontrado'),
            new OA\Response(response: 403, description: 'Sin permiso'),
            new OA\Response(response: 404, description: 'No encontrado'),
        ]
    )]
    public function show(User $user): JsonResponse
    {
        $user->load('roles');
        $this->authorize('update', $user);

        return response()->json([
            'id'       => $user->id,
            'nombre'   => $user->nombre,
            'email'    => $user->email,
            'usuario'  => $user->usuario,
            'activo'   => $user->activo,
            'rol'      => $user->roles->first()?->name ?? 'sin rol',
            'permisos' => $user->getDirectPermissions()->pluck('name'),
        ]);
    }

    #[OA\Post(
        path: '/usuarios',
        summary: 'Crear un usuario',
        description: 'Solo admin. El usuario queda asignado a la institución del admin autenticado.',
        tags: ['Usuarios'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nombre', 'email', 'usuario', 'password', 'password_confirmation', 'rol'],
                properties: [
                    new OA\Property(property: 'nombre', type: 'string', maxLength: 255, example: 'Ana García'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'ana@escuela.edu.ar'),
                    new OA\Property(property: 'usuario', type: 'string', maxLength: 255, example: 'anagarcia'),
                    new OA\Property(property: 'password', type: 'string', minLength: 8, example: 'secreto123'),
                    new OA\Property(property: 'password_confirmation', type: 'string', example: 'secreto123'),
                    new OA\Property(property: 'rol', type: 'string', enum: ['admin', 'bibliotecario'], example: 'bibliotecario'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Usuario creado'),
            new OA\Response(response: 422, description: 'Error de validación'),
            new OA\Response(response: 403, description: 'Sin permiso'),
        ]
    )]
    public function store(StoreUserRequest $request): JsonResponse
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

        return response()->json([
            'id'      => $user->id,
            'nombre'  => $user->nombre,
            'usuario' => $user->usuario,
            'rol'     => $request->rol,
        ], 201);
    }

    #[OA\Put(
        path: '/usuarios/{id}',
        summary: 'Actualizar datos de un usuario',
        description: 'Requiere permiso `gestionar-usuarios`.',
        tags: ['Usuarios'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nombre', 'email', 'usuario'],
                properties: [
                    new OA\Property(property: 'nombre', type: 'string', maxLength: 255),
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'usuario', type: 'string', maxLength: 255),
                    new OA\Property(property: 'password', type: 'string', minLength: 8, nullable: true, description: 'Omitir si no se quiere cambiar'),
                    new OA\Property(property: 'password_confirmation', type: 'string', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Usuario actualizado'),
            new OA\Response(response: 422, description: 'Error de validación'),
            new OA\Response(response: 403, description: 'Sin permiso'),
            new OA\Response(response: 404, description: 'No encontrado'),
        ]
    )]
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $data = $request->only('nombre', 'email', 'usuario');
        $data['name'] = $request->nombre;
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }
        $user->update($data);

        return response()->json(['id' => $user->id, 'nombre' => $user->nombre, 'usuario' => $user->usuario]);
    }

    #[OA\Delete(
        path: '/usuarios/{id}',
        summary: 'Eliminar un usuario',
        description: 'Solo admin. No se puede eliminar al propio usuario autenticado.',
        tags: ['Usuarios'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Usuario eliminado'),
            new OA\Response(response: 403, description: 'Sin permiso o intento de auto-eliminación'),
            new OA\Response(response: 404, description: 'No encontrado'),
        ]
    )]
    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);
        abort_if($user->id === auth()->id(), 403, 'No podés eliminarte a vos mismo.');
        $user->delete();

        return response()->json(['message' => 'Usuario eliminado']);
    }

    #[OA\Patch(
        path: '/usuarios/{id}/permisos',
        summary: 'Actualizar permisos de un usuario',
        description: 'Solo admin. No aplica a usuarios con rol admin.',
        tags: ['Usuarios'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(
                    property: 'permisos',
                    type: 'array',
                    items: new OA\Items(type: 'string', enum: [
                        'gestionar-usuarios', 'gestionar-materiales', 'gestionar-socios',
                        'gestionar-prestamos', 'gestionar-areas', 'gestionar-noticias',
                        'gestionar-anotaciones', 'ver-reportes',
                    ]),
                    example: ['gestionar-socios', 'gestionar-prestamos']
                ),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'Permisos actualizados'),
            new OA\Response(response: 403, description: 'Sin permiso o usuario es admin'),
            new OA\Response(response: 404, description: 'No encontrado'),
        ]
    )]
    public function updatePermisos(Request $request, User $user): JsonResponse
    {
        abort_if(!auth()->user()->hasRole('admin'), 403);
        abort_if($user->hasRole('admin'), 403, 'No se pueden editar los permisos de un administrador.');

        $request->validate([
            'permisos'   => 'array',
            'permisos.*' => 'string|in:' . implode(',', self::PERMISOS_ADMINISTRATIVOS),
        ]);

        $user->syncPermissions($request->permisos ?? []);

        return response()->json(['message' => 'Permisos actualizados', 'permisos' => $user->getDirectPermissions()->pluck('name')]);
    }

    #[OA\Patch(
        path: '/usuarios/{id}/toggle-activo',
        summary: 'Activar o desactivar un usuario',
        description: 'Solo admin. No aplica al propio usuario autenticado.',
        tags: ['Usuarios'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Estado actualizado'),
            new OA\Response(response: 403, description: 'Sin permiso o auto-modificación'),
            new OA\Response(response: 404, description: 'No encontrado'),
        ]
    )]
    public function toggleActivo(User $user): JsonResponse
    {
        abort_if(!auth()->user()->hasRole('admin'), 403);
        abort_if($user->id === auth()->id(), 403, 'No podés desactivarte a vos mismo.');

        $user->update(['activo' => !$user->activo]);

        return response()->json(['activo' => $user->activo, 'message' => $user->activo ? 'Usuario activado.' : 'Usuario desactivado.']);
    }
}
