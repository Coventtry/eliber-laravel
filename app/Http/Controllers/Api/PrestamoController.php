<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePrestamoRequest;
use App\Models\Prestamo;
use App\Services\PrestamoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class PrestamoController extends Controller
{
    public function __construct(private PrestamoService $prestamoService) {}

    #[OA\Get(
        path: '/prestamos',
        summary: 'Listar préstamos',
        description: 'Requiere permiso `gestionar-prestamos`. Soporta filtros por estado y búsqueda por nombre de socio.',
        tags: ['Préstamos'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'estado', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['activo', 'pendiente', 'atrasado', 'devuelto'])),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string'), description: 'Nombre o apellido del socio'),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista paginada de préstamos',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'socio_id', type: 'integer', example: 7),
                        new OA\Property(property: 'material_id', type: 'integer', example: 3),
                        new OA\Property(property: 'cantidad', type: 'integer', example: 1),
                        new OA\Property(property: 'fecha_prestamo', type: 'string', format: 'date', example: '2026-04-23'),
                        new OA\Property(property: 'fecha_devolucion', type: 'string', format: 'date', example: '2026-05-07'),
                        new OA\Property(property: 'estado', type: 'string', enum: ['activo', 'pendiente', 'atrasado', 'devuelto'], example: 'activo'),
                    ])),
                    new OA\Property(property: 'current_page', type: 'integer', example: 1),
                    new OA\Property(property: 'total', type: 'integer', example: 32),
                ])
            ),
            new OA\Response(response: 401, description: 'No autenticado'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $this->prestamoService->marcarAtrasados();

        $prestamos = Prestamo::with(['socio', 'material'])
            ->when($request->estado, fn ($q, $e) => $q->where('estado', $e))
            ->when($request->search, fn ($q, $s) => $q->whereHas('socio', fn ($sq) => $sq->where('nombre', 'like', "%{$s}%")->orWhere('apellido', 'like', "%{$s}%")
            ))
            ->orderByRaw("FIELD(estado, 'atrasado', 'pendiente', 'activo', 'devuelto')")
            ->orderBy('fecha_devolucion')
            ->paginate(20);

        return response()->json($prestamos);
    }

    #[OA\Get(
        path: '/prestamos/{id}',
        summary: 'Detalle de un préstamo',
        tags: ['Préstamos'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Préstamo encontrado'),
            new OA\Response(response: 404, description: 'No encontrado'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        $prestamo = Prestamo::with(['socio', 'material'])->findOrFail($id);

        return response()->json($prestamo);
    }

    #[OA\Post(
        path: '/prestamos',
        summary: 'Registrar un préstamo',
        description: 'Requiere permiso `gestionar-prestamos`. Valida: socio activo, menos de 3 préstamos activos, material disponible, fecha máx. 14 días.',
        tags: ['Préstamos'],
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['socio_id', 'material_id', 'cantidad', 'fecha_devolucion'],
                properties: [
                    new OA\Property(property: 'socio_id', type: 'integer', example: 7),
                    new OA\Property(property: 'material_id', type: 'integer', example: 3),
                    new OA\Property(property: 'cantidad', type: 'integer', minimum: 1, example: 1),
                    new OA\Property(property: 'fecha_devolucion', type: 'string', format: 'date', example: '2026-05-07', description: 'Máximo 14 días desde hoy'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Préstamo registrado'),
            new OA\Response(response: 422, description: 'Error de validación o regla de negocio'),
            new OA\Response(response: 403, description: 'Sin permiso'),
        ]
    )]
    public function store(StorePrestamoRequest $request): JsonResponse
    {
        $this->authorize('create', Prestamo::class);

        try {
            $prestamo = $this->prestamoService->crearPrestamo(
                $request->socio_id,
                $request->material_id,
                $request->cantidad,
                $request->fecha_devolucion,
            );
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        return response()->json($prestamo, 201);
    }

    #[OA\Patch(
        path: '/prestamos/{id}/devolver',
        summary: 'Registrar devolución de un préstamo',
        description: 'Requiere permiso `gestionar-prestamos`.',
        tags: ['Préstamos'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Devolución registrada'),
            new OA\Response(response: 422, description: 'El préstamo ya fue devuelto'),
            new OA\Response(response: 403, description: 'Sin permiso'),
            new OA\Response(response: 404, description: 'No encontrado'),
        ]
    )]
    public function devolver(int $id): JsonResponse
    {
        $prestamo = Prestamo::findOrFail($id);
        $this->authorize('update', $prestamo);

        if ($prestamo->estado === 'devuelto') {
            return response()->json(['message' => 'Este préstamo ya fue devuelto.'], 422);
        }

        $this->prestamoService->devolverPrestamo($prestamo);

        return response()->json(['message' => 'Devolución registrada']);
    }

    #[OA\Patch(
        path: '/prestamos/{id}/extender',
        summary: 'Extender la fecha de devolución',
        description: 'Requiere permiso `gestionar-prestamos`. Entre 1 y 30 días adicionales.',
        tags: ['Préstamos'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['dias'],
                properties: [
                    new OA\Property(property: 'dias', type: 'integer', minimum: 1, maximum: 30, example: 7),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Préstamo extendido'),
            new OA\Response(response: 422, description: 'Error de negocio o validación'),
            new OA\Response(response: 403, description: 'Sin permiso'),
            new OA\Response(response: 404, description: 'No encontrado'),
        ]
    )]
    public function extender(Request $request, int $id): JsonResponse
    {
        $prestamo = Prestamo::findOrFail($id);
        $this->authorize('update', $prestamo);
        $request->validate(['dias' => 'required|integer|min:1|max:30']);

        try {
            $this->prestamoService->extenderPrestamo($prestamo, $request->dias);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        return response()->json(['message' => "Préstamo extendido {$request->dias} días"]);
    }
}
