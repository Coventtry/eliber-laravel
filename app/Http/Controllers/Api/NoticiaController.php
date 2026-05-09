<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NoticiaResource;
use App\Models\Noticia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

class NoticiaController extends Controller
{
    #[OA\Get(
        path: '/noticias',
        summary: 'Listar noticias',
        description: 'Devuelve las noticias ordenadas por fecha descendente, paginadas de a 10. Endpoint público.',
        tags: ['Noticias'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista paginada de noticias',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'titulo', type: 'string', example: 'Nuevos libros disponibles'),
                        new OA\Property(property: 'descripcion', type: 'string', example: 'Se incorporaron 20 nuevos títulos al acervo.'),
                        new OA\Property(property: 'fecha', type: 'string', format: 'date', example: '2026-04-20'),
                    ])),
                    new OA\Property(property: 'current_page', type: 'integer', example: 1),
                    new OA\Property(property: 'last_page', type: 'integer', example: 3),
                    new OA\Property(property: 'total', type: 'integer', example: 25),
                ])
            ),
        ]
    )]
    public function index(): JsonResponse
    {
        $noticias = Noticia::select('id', 'titulo', 'descripcion', 'fecha')
            ->orderBy('fecha', 'desc')
            ->paginate(10);

        return NoticiaResource::collection($noticias);
    }

    #[OA\Post(
        path: '/noticias',
        summary: 'Crear una noticia',
        description: 'Requiere permiso `gestionar-noticias`. Enviar como multipart/form-data si se adjunta imagen.',
        tags: ['Noticias'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['titulo', 'descripcion'],
                    properties: [
                        new OA\Property(property: 'titulo', type: 'string', maxLength: 255, example: 'Nueva adquisición'),
                        new OA\Property(property: 'descripcion', type: 'string', maxLength: 255, example: 'Se incorporaron nuevos títulos.'),
                        new OA\Property(property: 'imagen', type: 'string', format: 'binary', description: 'Imagen (jpg/png, máx 2MB)', nullable: true),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Noticia creada'),
            new OA\Response(response: 422, description: 'Error de validación'),
            new OA\Response(response: 401, description: 'No autenticado'),
            new OA\Response(response: 403, description: 'Sin permiso'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Noticia::class);
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string|max:255',
            'imagen' => 'nullable|image|max:2048',
        ]);

        $nombreImagen = null;
        if ($request->hasFile('imagen')) {
            $nombreImagen = basename($request->file('imagen')->store('noticias', 'public'));
        }

        $noticia = Noticia::create([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'imagen' => $nombreImagen,
            'institucion_id' => $request->user()->institucion_id,
        ]);

        return NoticiaResource::make($noticia)->response()->setStatusCode(201);
    }

    #[OA\Put(
        path: '/noticias/{id}',
        summary: 'Actualizar una noticia',
        description: 'Requiere permiso `gestionar-noticias`. Enviar como multipart/form-data si se adjunta imagen.',
        tags: ['Noticias'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['titulo', 'descripcion'],
                    properties: [
                        new OA\Property(property: 'titulo', type: 'string', maxLength: 255),
                        new OA\Property(property: 'descripcion', type: 'string', maxLength: 255),
                        new OA\Property(property: 'imagen', type: 'string', format: 'binary', nullable: true),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Noticia actualizada'),
            new OA\Response(response: 403, description: 'Sin permiso'),
            new OA\Response(response: 404, description: 'No encontrada'),
        ]
    )]
    public function update(Request $request, int $id): JsonResponse
    {
        $noticia = Noticia::findOrFail($id);
        $this->authorize('update', $noticia);
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string|max:255',
            'imagen' => 'nullable|image|max:2048',
        ]);

        $data = $request->only('titulo', 'descripcion');

        if ($request->hasFile('imagen')) {
            if ($noticia->imagen) {
                Storage::disk('public')->delete('noticias/'.$noticia->imagen);
            }
            $data['imagen'] = basename($request->file('imagen')->store('noticias', 'public'));
        }

        $noticia->update($data);

        return new NoticiaResource($noticia);
    }

    #[OA\Delete(
        path: '/noticias/{id}',
        summary: 'Eliminar una noticia',
        description: 'Requiere permiso `gestionar-noticias`.',
        tags: ['Noticias'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Noticia eliminada'),
            new OA\Response(response: 403, description: 'Sin permiso'),
            new OA\Response(response: 404, description: 'No encontrada'),
        ]
    )]
    public function destroy(int $id): JsonResponse
    {
        $noticia = Noticia::findOrFail($id);
        $this->authorize('delete', $noticia);

        if ($noticia->imagen) {
            Storage::disk('public')->delete('noticias/'.$noticia->imagen);
        }
        $noticia->delete();

        return response()->json(['message' => 'Noticia eliminada']);
    }
}
