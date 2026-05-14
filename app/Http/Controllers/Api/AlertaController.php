<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlertaResource;
use App\Models\Alerta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AlertaController extends Controller
{
    #[OA\Get(
        path: '/alertas',
        summary: 'Listar alertas de la institución',
        description: 'Devuelve el registro de alertas de préstamos. Tipos: `proximo_vencer` (vence en ≤4 días), `vencido` (préstamo atrasado), `renovacion` (préstamo extendido).',
        tags: ['Alertas'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'tipo',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['proximo_vencer', 'vencido', 'renovacion']),
                description: 'Filtrar por tipo de alerta'
            ),
            new OA\Parameter(
                name: 'leida',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', enum: [0, 1]),
                description: '0 = no leídas, 1 = leídas'
            ),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista paginada de alertas',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(
                            property: 'tipo',
                            type: 'string',
                            enum: ['proximo_vencer', 'vencido', 'renovacion'],
                            description: 'proximo_vencer: vence en ≤4 días · vencido: ya venció · renovacion: se extendió el plazo',
                            example: 'renovacion'
                        ),
                        new OA\Property(property: 'descripcion', type: 'string', example: 'Renovación +7 días — Don Quijote (nuevo vencimiento: 30/04/2026)'),
                        new OA\Property(property: 'fecha_alerta', type: 'string', format: 'date-time', example: '2026-04-24T10:30:00'),
                        new OA\Property(property: 'leida', type: 'boolean', example: false),
                        new OA\Property(property: 'prestamo_id', type: 'integer', nullable: true, example: 5),
                    ])),
                    new OA\Property(property: 'current_page', type: 'integer', example: 1),
                    new OA\Property(property: 'total', type: 'integer', example: 12),
                ])
            ),
            new OA\Response(response: 401, description: 'No autenticado'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $alertas = Alerta::when($request->tipo, fn ($q, $t) => $q->where('tipo', $t))
            ->when($request->has('leida') && $request->leida !== '', fn ($q) => $q->where('leida', $request->leida))
            ->orderByDesc('fecha_alerta')
            ->paginate(20);

        return AlertaResource::collection($alertas)->response();
    }

    #[OA\Patch(
        path: '/alertas/{id}/leida',
        summary: 'Marcar una alerta como leída',
        tags: ['Alertas'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Alerta marcada como leída'),
            new OA\Response(response: 404, description: 'No encontrada'),
        ]
    )]
    public function marcarLeida(Request $request, Alerta $alerta): JsonResponse
    {
        if (!$request->user('sanctum')->hasPermissionTo('gestionar-prestamos', 'web')) {
            return response()->json(['message' => 'No autorizado'], 403);
        }
        $alerta->update(['leida' => true]);

        return response()->json(['message' => 'Alerta marcada como leída']);
    }

    #[OA\Patch(
        path: '/alertas/todas-leidas',
        summary: 'Marcar todas las alertas como leídas',
        tags: ['Alertas'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Todas las alertas marcadas como leídas'),
        ]
    )]
    public function marcarTodasLeidas(Request $request): JsonResponse
    {
        if (!$request->user('sanctum')->hasPermissionTo('gestionar-prestamos', 'web')) {
            return response()->json(['message' => 'No autorizado'], 403);
        }
        $count = Alerta::noLeidas()->update(['leida' => true]);

        return response()->json(['message' => 'Alertas marcadas como leídas', 'total' => $count]);
    }
}
