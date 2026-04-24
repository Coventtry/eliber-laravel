<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSocioRequest;
use App\Models\Socio;
use App\Services\SocioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class SocioController extends Controller
{
    public function __construct(private SocioService $socioService) {}

    #[OA\Get(
        path: '/socios',
        summary: 'Listar socios',
        description: 'Requiere permiso `gestionar-socios`. Soporta filtros por búsqueda y estado activo.',
        tags: ['Socios'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string'), description: 'Filtrar por nombre, apellido o email'),
            new OA\Parameter(name: 'activo', in: 'query', required: false, schema: new OA\Schema(type: 'integer', enum: [0, 1]), description: '1 = activos, 0 = inactivos'),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista paginada de socios',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'nombre', type: 'string', example: 'Juan'),
                        new OA\Property(property: 'apellido', type: 'string', example: 'Pérez'),
                        new OA\Property(property: 'email', type: 'string', nullable: true, example: 'juan@escuela.edu.ar'),
                        new OA\Property(property: 'telefono', type: 'string', nullable: true),
                        new OA\Property(property: 'anio', type: 'integer', nullable: true, example: 3),
                        new OA\Property(property: 'division', type: 'integer', nullable: true, example: 2),
                        new OA\Property(property: 'activo', type: 'boolean', example: true),
                    ])),
                    new OA\Property(property: 'current_page', type: 'integer', example: 1),
                    new OA\Property(property: 'total', type: 'integer', example: 45),
                ])
            ),
            new OA\Response(response: 401, description: 'No autenticado'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $socios = Socio::query()
            ->when($request->search, fn($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('nombre', 'like', "%{$s}%")
                  ->orWhere('apellido', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            }))
            ->when($request->activo !== null, fn($q) => $q->where('activo', $request->activo))
            ->orderBy('apellido')
            ->paginate(20);

        return response()->json($socios);
    }

    #[OA\Get(
        path: '/socios/{id}',
        summary: 'Detalle de un socio',
        tags: ['Socios'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Socio encontrado'),
            new OA\Response(response: 404, description: 'No encontrado'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $socio = Socio::findOrFail($id);

        return response()->json($socio);
    }

    #[OA\Post(
        path: '/socios',
        summary: 'Registrar un socio',
        description: 'Requiere permiso `gestionar-socios`.',
        tags: ['Socios'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nombre', 'apellido'],
                properties: [
                    new OA\Property(property: 'nombre', type: 'string', maxLength: 255, example: 'Juan'),
                    new OA\Property(property: 'apellido', type: 'string', maxLength: 255, example: 'Pérez'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', nullable: true, example: 'juan@escuela.edu.ar'),
                    new OA\Property(property: 'telefono', type: 'string', nullable: true),
                    new OA\Property(property: 'direccion', type: 'string', nullable: true),
                    new OA\Property(property: 'anio', type: 'integer', minimum: 1, maximum: 6, nullable: true, example: 3),
                    new OA\Property(property: 'division', type: 'integer', minimum: 1, maximum: 6, nullable: true, example: 2),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Socio registrado'),
            new OA\Response(response: 422, description: 'Error de validación'),
            new OA\Response(response: 403, description: 'Sin permiso'),
        ]
    )]
    public function store(StoreSocioRequest $request): JsonResponse
    {
        $this->authorize('create', Socio::class);
        $socio = Socio::create([
            ...$request->validated(),
            'institucion_id' => $request->user()->institucion_id,
        ]);

        return response()->json($socio, 201);
    }

    #[OA\Put(
        path: '/socios/{id}',
        summary: 'Actualizar un socio',
        description: 'Requiere permiso `gestionar-socios`.',
        tags: ['Socios'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nombre', 'apellido'],
                properties: [
                    new OA\Property(property: 'nombre', type: 'string', maxLength: 255),
                    new OA\Property(property: 'apellido', type: 'string', maxLength: 255),
                    new OA\Property(property: 'email', type: 'string', format: 'email', nullable: true),
                    new OA\Property(property: 'telefono', type: 'string', nullable: true),
                    new OA\Property(property: 'direccion', type: 'string', nullable: true),
                    new OA\Property(property: 'anio', type: 'integer', nullable: true),
                    new OA\Property(property: 'division', type: 'integer', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Socio actualizado'),
            new OA\Response(response: 422, description: 'Error de validación'),
            new OA\Response(response: 403, description: 'Sin permiso'),
            new OA\Response(response: 404, description: 'No encontrado'),
        ]
    )]
    public function update(StoreSocioRequest $request, int $id): JsonResponse
    {
        $socio = Socio::findOrFail($id);
        $this->authorize('update', $socio);
        $socio->update($request->validated());

        return response()->json($socio);
    }

    #[OA\Delete(
        path: '/socios/{id}',
        summary: 'Eliminar un socio',
        description: 'Requiere permiso `gestionar-socios`. Elimina permanentemente el registro.',
        tags: ['Socios'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Socio eliminado'),
            new OA\Response(response: 403, description: 'Sin permiso'),
            new OA\Response(response: 404, description: 'No encontrado'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        $socio = Socio::findOrFail($id);
        $this->authorize('delete', $socio);
        $socio->delete();

        return response()->json(['message' => 'Socio eliminado']);
    }

    #[OA\Patch(
        path: '/socios/{id}/baja',
        summary: 'Dar de baja un socio',
        description: 'Marca el socio como inactivo y registra en el historial. Requiere permiso `gestionar-socios`.',
        tags: ['Socios'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'observaciones', type: 'string', nullable: true, example: 'Egresó de la institución'),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'Socio dado de baja'),
            new OA\Response(response: 403, description: 'Sin permiso'),
            new OA\Response(response: 404, description: 'No encontrado'),
        ]
    )]
    public function baja(Request $request, int $id): JsonResponse
    {
        $socio = Socio::findOrFail($id);
        $this->authorize('update', $socio);
        $this->socioService->darDeBaja($socio, $request->input('observaciones', ''));

        return response()->json(['message' => 'Socio dado de baja']);
    }

    #[OA\Patch(
        path: '/socios/{id}/reactivar',
        summary: 'Reactivar un socio',
        description: 'Requiere permiso `gestionar-socios`.',
        tags: ['Socios'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Socio reactivado'),
            new OA\Response(response: 403, description: 'Sin permiso'),
            new OA\Response(response: 404, description: 'No encontrado'),
        ]
    )]
    public function reactivar(int $id): JsonResponse
    {
        $socio = Socio::findOrFail($id);
        $this->authorize('update', $socio);
        $this->socioService->reactivar($socio);

        return response()->json(['message' => 'Socio reactivado']);
    }
}
