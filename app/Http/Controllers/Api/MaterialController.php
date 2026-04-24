<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMaterialRequest;
use App\Models\Area;
use App\Models\Material;
use App\Services\MaterialService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class MaterialController extends Controller
{
    public function __construct(private MaterialService $materialService) {}

    #[OA\Get(
        path: '/materiales',
        summary: 'Listar materiales disponibles',
        description: 'Devuelve los materiales con stock > 0, paginados de a 20. Endpoint público.',
        tags: ['Materiales'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista paginada de materiales',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'titulo', type: 'string', example: 'El Principito'),
                        new OA\Property(property: 'autor', type: 'string', example: 'Antoine de Saint-Exupéry'),
                        new OA\Property(property: 'anio_publicacion', type: 'integer', example: 1943),
                        new OA\Property(property: 'categoria', type: 'string', example: 'Literatura'),
                        new OA\Property(property: 'codigo', type: 'string', example: '800-001'),
                        new OA\Property(property: 'disponibilidad', type: 'integer', example: 3),
                    ])),
                    new OA\Property(property: 'current_page', type: 'integer', example: 1),
                    new OA\Property(property: 'last_page', type: 'integer', example: 5),
                    new OA\Property(property: 'total', type: 'integer', example: 98),
                ])
            ),
        ]
    )]
    public function index(): JsonResponse
    {
        $materiales = Material::select('id', 'titulo', 'autor', 'anio_publicacion', 'categoria', 'codigo', 'disponibilidad')
            ->where('disponibilidad', '>', 0)
            ->paginate(20);

        return response()->json($materiales);
    }

    #[OA\Get(
        path: '/materiales/{id}',
        summary: 'Detalle de un material',
        description: 'Endpoint público.',
        tags: ['Materiales'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Material encontrado',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'titulo', type: 'string', example: 'El Principito'),
                    new OA\Property(property: 'autor', type: 'string', example: 'Antoine de Saint-Exupéry'),
                    new OA\Property(property: 'anio_publicacion', type: 'integer', example: 1943),
                    new OA\Property(property: 'categoria', type: 'string', example: 'Literatura'),
                    new OA\Property(property: 'codigo', type: 'string', example: '800-001'),
                    new OA\Property(property: 'disponibilidad', type: 'integer', example: 3),
                    new OA\Property(property: 'editorial', type: 'string', nullable: true, example: 'Salamandra'),
                    new OA\Property(property: 'area_id', type: 'integer', nullable: true, example: 8),
                ])
            ),
            new OA\Response(response: 404, description: 'No encontrado'),
        ]
    )]
    public function show(int $material): JsonResponse
    {
        $material = Material::select('id', 'titulo', 'autor', 'anio_publicacion', 'categoria', 'codigo', 'disponibilidad', 'editorial', 'area_id')
            ->findOrFail($material);

        return response()->json($material);
    }

    #[OA\Post(
        path: '/materiales',
        summary: 'Crear un material',
        description: 'Requiere permiso `gestionar-materiales`. El código se genera automáticamente según el área (Dewey).',
        tags: ['Materiales'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['titulo', 'area_id', 'disponibilidad'],
                properties: [
                    new OA\Property(property: 'titulo', type: 'string', maxLength: 255, example: 'Don Quijote de la Mancha'),
                    new OA\Property(property: 'autor', type: 'string', maxLength: 255, nullable: true, example: 'Miguel de Cervantes'),
                    new OA\Property(property: 'anio_publicacion', type: 'integer', example: 1605),
                    new OA\Property(property: 'area_id', type: 'integer', example: 8),
                    new OA\Property(property: 'categoria', type: 'string', nullable: true, example: 'Literatura clásica'),
                    new OA\Property(property: 'disponibilidad', type: 'integer', minimum: 0, example: 2),
                    new OA\Property(property: 'editorial', type: 'string', nullable: true, example: 'Planeta'),
                    new OA\Property(property: 'pasillo', type: 'string', nullable: true, example: 'A'),
                    new OA\Property(property: 'tipo_almacenamiento', type: 'string', enum: ['E', 'M'], nullable: true),
                    new OA\Property(property: 'estante', type: 'integer', nullable: true, example: 3),
                    new OA\Property(property: 'nivel', type: 'integer', nullable: true, example: 2),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Material creado'),
            new OA\Response(response: 422, description: 'Error de validación'),
            new OA\Response(response: 401, description: 'No autenticado'),
            new OA\Response(response: 403, description: 'Sin permiso'),
        ]
    )]
    public function store(StoreMaterialRequest $request): JsonResponse
    {
        $this->authorize('create', Material::class);

        $data = $request->validated();
        $area = Area::findOrFail($data['area_id']);
        $data['codigo'] = $this->materialService->generarCodigo($area);
        $data['institucion_id'] = $request->user()->institucion_id;

        if ($request->filled('pasillo')) {
            $data['clasificacion_fisica'] = $this->materialService->generarClasificacionFisica(
                $area,
                $request->pasillo,
                $request->tipo_almacenamiento,
                $request->estante,
                $request->nivel
            );
        }

        $material = Material::create($data);
        $this->materialService->generarQR($material);

        return response()->json($material, 201);
    }

    #[OA\Put(
        path: '/materiales/{id}',
        summary: 'Actualizar un material',
        description: 'Requiere permiso `gestionar-materiales`.',
        tags: ['Materiales'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['titulo', 'area_id', 'disponibilidad'],
                properties: [
                    new OA\Property(property: 'titulo', type: 'string', maxLength: 255, example: 'Don Quijote de la Mancha'),
                    new OA\Property(property: 'autor', type: 'string', nullable: true),
                    new OA\Property(property: 'anio_publicacion', type: 'integer', nullable: true),
                    new OA\Property(property: 'area_id', type: 'integer', example: 8),
                    new OA\Property(property: 'categoria', type: 'string', nullable: true),
                    new OA\Property(property: 'disponibilidad', type: 'integer', minimum: 0, example: 2),
                    new OA\Property(property: 'editorial', type: 'string', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Material actualizado'),
            new OA\Response(response: 422, description: 'Error de validación'),
            new OA\Response(response: 403, description: 'Sin permiso'),
            new OA\Response(response: 404, description: 'No encontrado'),
        ]
    )]
    public function update(StoreMaterialRequest $request, int $id): JsonResponse
    {
        $material = Material::findOrFail($id);
        $this->authorize('update', $material);
        $material->update($request->validated());

        return response()->json($material);
    }

    #[OA\Delete(
        path: '/materiales/{id}',
        summary: 'Eliminar un material',
        description: 'Requiere permiso `gestionar-materiales`.',
        tags: ['Materiales'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Material eliminado'),
            new OA\Response(response: 403, description: 'Sin permiso'),
            new OA\Response(response: 404, description: 'No encontrado'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        $material = Material::findOrFail($id);
        $this->authorize('delete', $material);
        $material->delete();

        return response()->json(['message' => 'Material eliminado']);
    }
}
