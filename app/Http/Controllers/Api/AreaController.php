<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AreaController extends Controller
{
    #[OA\Get(
        path: '/areas',
        summary: 'Listar áreas temáticas (Dewey)',
        description: 'Requiere autenticación. Devuelve todas las áreas de la institución.',
        tags: ['Áreas'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de áreas',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'codigo_dewey', type: 'string', example: '800'),
                        new OA\Property(property: 'nombre', type: 'string', example: 'Literatura'),
                        new OA\Property(property: 'Abreviado', type: 'string', nullable: true, example: 'LIT'),
                    ])),
                    new OA\Property(property: 'total', type: 'integer', example: 10),
                ])
            ),
            new OA\Response(response: 401, description: 'No autenticado'),
        ]
    )]
    public function index(): JsonResponse
    {
        return response()->json(Area::orderBy('nombre')->paginate(30));
    }

    #[OA\Get(
        path: '/areas/{id}',
        summary: 'Detalle de un área',
        tags: ['Áreas'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Área encontrada'),
            new OA\Response(response: 404, description: 'No encontrada'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        return response()->json(Area::findOrFail($id));
    }

    #[OA\Post(
        path: '/areas',
        summary: 'Crear un área temática',
        description: 'Requiere permiso `gestionar-areas`.',
        tags: ['Áreas'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['codigo_dewey', 'nombre'],
                properties: [
                    new OA\Property(property: 'codigo_dewey', type: 'string', maxLength: 50, example: '900'),
                    new OA\Property(property: 'nombre', type: 'string', maxLength: 255, example: 'Historia y Geografía'),
                    new OA\Property(property: 'Abreviado', type: 'string', maxLength: 50, nullable: true, example: 'HIS'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Área creada'),
            new OA\Response(response: 422, description: 'Error de validación'),
            new OA\Response(response: 403, description: 'Sin permiso'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Area::class);
        $request->validate([
            'codigo_dewey' => 'required|string|max:50|unique:areas',
            'nombre'       => 'required|string|max:255|unique:areas',
            'Abreviado'    => 'nullable|string|max:50',
        ]);

        $area = Area::create($request->only('codigo_dewey', 'nombre', 'Abreviado'));

        return response()->json($area, 201);
    }

    #[OA\Put(
        path: '/areas/{id}',
        summary: 'Actualizar un área temática',
        description: 'Requiere permiso `gestionar-areas`.',
        tags: ['Áreas'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['codigo_dewey', 'nombre'],
                properties: [
                    new OA\Property(property: 'codigo_dewey', type: 'string', maxLength: 50),
                    new OA\Property(property: 'nombre', type: 'string', maxLength: 255),
                    new OA\Property(property: 'Abreviado', type: 'string', maxLength: 50, nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Área actualizada'),
            new OA\Response(response: 422, description: 'Error de validación'),
            new OA\Response(response: 403, description: 'Sin permiso'),
            new OA\Response(response: 404, description: 'No encontrada'),
        ]
    )]
    public function update(Request $request, int $id): JsonResponse
    {
        $area = Area::findOrFail($id);
        $this->authorize('update', $area);
        $request->validate([
            'codigo_dewey' => 'required|string|max:50|unique:areas,codigo_dewey,' . $area->id,
            'nombre'       => 'required|string|max:255|unique:areas,nombre,' . $area->id,
            'Abreviado'    => 'nullable|string|max:50',
        ]);

        $area->update($request->only('codigo_dewey', 'nombre', 'Abreviado'));

        return response()->json($area);
    }

    #[OA\Delete(
        path: '/areas/{id}',
        summary: 'Eliminar un área temática',
        description: 'Requiere permiso `gestionar-areas`.',
        tags: ['Áreas'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Área eliminada'),
            new OA\Response(response: 403, description: 'Sin permiso'),
            new OA\Response(response: 404, description: 'No encontrada'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        $area = Area::findOrFail($id);
        $this->authorize('delete', $area);
        $area->delete();

        return response()->json(['message' => 'Área eliminada']);
    }
}
