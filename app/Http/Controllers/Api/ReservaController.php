<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReservaResource;
use App\Models\Reserva;
use App\Services\ReservaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class ReservaController extends Controller
{
    public function __construct(
        private ReservaService $reservaService
    ) {}

    #[OA\Get(
        path: '/reservas',
        summary: 'Listar reservas',
        description: 'Admin/bibliotecario: devuelve todas las reservas con material y socio. Alumno: sólo las propias.',
        tags: ['Reservas'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista paginada de reservas',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'material_id', type: 'integer', example: 3),
                            new OA\Property(property: 'socio_id', type: 'integer', example: 7),
                            new OA\Property(property: 'estado', type: 'string', enum: ['pendiente', 'aprobada', 'rechazada', 'cancelada'], example: 'pendiente'),
                            new OA\Property(property: 'fecha_reserva', type: 'string', format: 'date', example: '2026-04-23'),
                            new OA\Property(property: 'fecha_vencimiento', type: 'string', format: 'date', nullable: true, example: '2026-05-07'),
                        ]
                    )),
                    new OA\Property(property: 'current_page', type: 'integer', example: 1),
                    new OA\Property(property: 'total', type: 'integer', example: 12),
                ])
            ),
            new OA\Response(response: 401, description: 'No autenticado'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        if ($user->hasRole('admin') || $user->can('gestionar-prestamos')) {
            $reservas = Reserva::with(['material', 'socio'])
                ->orderBy('fecha_reserva', 'desc')
                ->paginate(20);
        } else {
            $reservas = Reserva::with(['material'])
                ->where('socio_id', $user->socio_id ?? 0)
                ->orderBy('fecha_reserva', 'desc')
                ->paginate(20);
        }

        return ReservaResource::collection($reservas);
    }

    #[OA\Post(
        path: '/reservas',
        summary: 'Crear una reserva',
        tags: ['Reservas'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['material_id', 'socio_id'],
                properties: [
                    new OA\Property(property: 'material_id', type: 'integer', example: 3),
                    new OA\Property(property: 'socio_id', type: 'integer', example: 7),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Reserva creada'),
            new OA\Response(response: 422, description: 'Error de validación o regla de negocio'),
            new OA\Response(response: 401, description: 'No autenticado'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');
        if (!$user->hasPermissionTo('gestionar-prestamos', 'web') && !$user->socio_id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }
        $request->validate([
            'material_id' => 'required|exists:materiales,id',
            'socio_id' => 'required|exists:socios,id',
        ]);

        try {
            $reserva = $this->reservaService->crearReserva(
                $request->socio_id,
                $request->material_id
            );

            return ReservaResource::make($reserva)->response()->setStatusCode(201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    #[OA\Delete(
        path: '/reservas/{id}',
        summary: 'Cancelar una reserva',
        tags: ['Reservas'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Reserva cancelada'),
            new OA\Response(response: 403, description: 'No autorizado'),
            new OA\Response(response: 422, description: 'No se puede cancelar'),
        ]
    )]
    public function destroy(int $reserva): JsonResponse
    {
        $reserva = Reserva::findOrFail($reserva);
        $user = Auth::user();

        if (! $user->can('gestionar-prestamos') && $reserva->socio_id !== ($user->socio_id ?? 0)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        try {
            $this->reservaService->cancelarReserva($reserva);

            return response()->json(['message' => 'Reserva cancelada']);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    #[OA\Patch(
        path: '/reservas/{id}/aprobar',
        summary: 'Aprobar una reserva y crear préstamo',
        tags: ['Reservas'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'dias', type: 'integer', minimum: 1, maximum: 30, example: 14, description: 'Duración del préstamo en días (default: 14)'),
            ])
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Reserva aprobada y préstamo creado',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'Reserva aprobada y prestamo creado'),
                    new OA\Property(property: 'prestamo_id', type: 'integer', example: 42),
                ])
            ),
            new OA\Response(response: 422, description: 'Error de negocio'),
        ]
    )]
    public function aprobar(Request $request, int $reserva): JsonResponse
    {
        if (!$request->user('sanctum')->hasPermissionTo('gestionar-prestamos', 'web')) {
            return response()->json(['message' => 'No autorizado'], 403);
        }
        $reserva = Reserva::with('material')->findOrFail($reserva);

        $request->validate([
            'dias' => 'nullable|integer|min:1|max:30',
        ]);

        $dias = $request->input('dias', 14);

        try {
            $prestamo = $this->reservaService->aprobarReserva($reserva, $dias);

            return response()->json([
                'message' => 'Reserva aprobada y prestamo creado',
                'prestamo_id' => $prestamo->id,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    #[OA\Patch(
        path: '/reservas/{id}/rechazar',
        summary: 'Rechazar una reserva',
        tags: ['Reservas'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'motivo', type: 'string', maxLength: 500, nullable: true, example: 'Material no disponible'),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'Reserva rechazada'),
            new OA\Response(response: 422, description: 'Error de negocio'),
        ]
    )]
    public function rechazar(Request $request, int $reserva): JsonResponse
    {
        if (!$request->user('sanctum')->hasPermissionTo('gestionar-prestamos', 'web')) {
            return response()->json(['message' => 'No autorizado'], 403);
        }
        $reserva = Reserva::findOrFail($reserva);

        $request->validate([
            'motivo' => 'nullable|string|max:500',
        ]);

        try {
            $this->reservaService->rechazarReserva(
                $reserva,
                $request->motivo
            );

            return response()->json(['message' => 'Reserva rechazada']);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }
}
