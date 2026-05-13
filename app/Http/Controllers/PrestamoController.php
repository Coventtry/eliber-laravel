<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePrestamoRequest;
use App\Models\Area;
use App\Models\Prestamo;
use App\Models\Reserva;
use App\Models\Socio;
use App\Services\PrestamoService;
use App\Services\ReservaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PrestamoController extends Controller
{
    public function __construct(private PrestamoService $prestamoService) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Prestamo::class);
        $this->prestamoService->marcarAtrasados();

        $prestamos = Prestamo::with(['socio', 'material.area', 'ejemplar'])
            ->when($request->estado, fn ($q, $e) => $q->where('estado', $e))
            ->when($request->search, fn ($q, $s) => $q->whereHas('socio', fn ($sq) => $sq->where('nombre', 'like', "%{$s}%")->orWhere('apellido', 'like', "%{$s}%")
            ))
            ->orderByRaw("CASE estado WHEN 'atrasado' THEN 1 WHEN 'pendiente' THEN 2 WHEN 'activo' THEN 3 WHEN 'devuelto' THEN 4 END")
            ->orderBy('fecha_devolucion')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Prestamos/Index', [
            'prestamos' => $prestamos,
            'filters' => $request->only(['estado', 'search']),
        ]);
    }

    public function create(Request $request): Response
    {
        $socioInicial = null;
        if ($request->socio_id) {
            $socio = Socio::find($request->socio_id);
            if ($socio) {
                $socioInicial = [
                    'id' => $socio->id,
                    'nombre' => $socio->nombre,
                    'apellido' => $socio->apellido,
                    'email' => $socio->email,
                ];
            }
        }

        return Inertia::render('Prestamos/Create', [
            'areas' => Area::orderBy('nombre')->get(['id', 'nombre']),
            'socioInicial' => $socioInicial,
        ]);
    }

    public function store(StorePrestamoRequest $request): RedirectResponse
    {
        $this->authorize('create', Prestamo::class);
        try {
            $prestamos = $this->prestamoService->crearPrestamo(
                $request->socio_id,
                $request->material_id,
                $request->cantidad,
                $request->fecha_devolucion,
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        $cantidad = count($prestamos);
        $msg = $cantidad === 1
            ? 'Préstamo registrado correctamente.'
            : "{$cantidad} préstamos registrados correctamente.";

        return redirect()->route('prestamos.index')->with('success', $msg);
    }

    public function showDevolucion(Prestamo $prestamo): Response
    {
        return Inertia::render('Prestamos/Return', [
            'prestamo' => $prestamo->load('socio', 'material', 'ejemplar'),
        ]);
    }

    public function devolver(Prestamo $prestamo): RedirectResponse
    {
        $this->authorize('update', $prestamo);
        if ($prestamo->estado === 'devuelto') {
            return back()->withErrors(['prestamo' => 'Este préstamo ya fue devuelto.']);
        }

        $this->prestamoService->devolverPrestamo($prestamo);

        return redirect()->route('prestamos.index')->with('success', 'Devolución registrada.');
    }

    public function extender(Request $request, Prestamo $prestamo): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $prestamo);
        $request->validate(['dias' => 'required|integer|min:1|max:30']);

        try {
            $this->prestamoService->extenderPrestamo($prestamo, $request->dias);
        } catch (ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json(['errors' => $e->errors()], 422);
            }

            return back()->withErrors($e->errors());
        }

        if ($request->wantsJson()) {
            $prestamo->refresh();

            return response()->json([
                'message' => "Préstamo extendido {$request->dias} días.",
                'fecha_devolucion' => $prestamo->fecha_devolucion?->format('Y-m-d'),
            ]);
        }

        return redirect()->route('prestamos.index')->with('success', "Préstamo extendido {$request->dias} días.");
    }

    public function solicitudes(): Response
    {
        $this->authorize('viewAny', Prestamo::class);

        $solicitudes = Reserva::with('socio', 'material')
            ->where('estado', 'pendiente')
            ->orderByDesc('fecha_reserva')
            ->paginate(20)
            ->withQueryString()
            ->through(fn($r) => [
                'id'       => $r->id,
                'socio'    => $r->socio->full_name,
                'material' => $r->material->titulo,
                'fecha'    => $r->fecha_reserva->format('d/m/Y H:i'),
            ]);

        return Inertia::render('Prestamos/Solicitudes', [
            'solicitudes' => $solicitudes,
        ]);
    }

    public function aprobarSolicitud(Reserva $reserva, ReservaService $reservaService): RedirectResponse
    {
        $this->authorize('create', Prestamo::class);

        if ($reserva->estado !== 'pendiente') {
            return back()->with('error', 'La solicitud ya fue procesada.');
        }

        try {
            $reservaService->aprobarReserva($reserva);
            return redirect()->route('prestamos.solicitudes')->with('success', 'Solicitud aprobada. Préstamo creado.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function rechazarSolicitud(Request $request, Reserva $reserva, ReservaService $reservaService): RedirectResponse
    {
        $this->authorize('create', Prestamo::class);

        $request->validate([
            'motivo' => 'nullable|string|max:500',
        ]);

        if ($reserva->estado !== 'pendiente') {
            return back()->with('error', 'La solicitud ya fue procesada.');
        }

        try {
            $reservaService->rechazarReserva($reserva, $request->motivo);
            return redirect()->route('prestamos.solicitudes')->with('success', 'Solicitud rechazada.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
