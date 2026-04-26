<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePrestamoRequest;
use App\Models\Area;
use App\Models\Prestamo;
use App\Models\Socio;
use App\Services\PrestamoService;
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
        $this->prestamoService->marcarAtrasados();

        $prestamos = Prestamo::with(['socio', 'material.area'])
            ->when($request->estado, fn($q, $e) => $q->where('estado', $e))
            ->when($request->search, fn($q, $s) => $q->whereHas('socio', fn($sq) =>
                $sq->where('nombre', 'like', "%{$s}%")->orWhere('apellido', 'like', "%{$s}%")
            ))
            ->orderByRaw("FIELD(estado, 'atrasado', 'pendiente', 'activo', 'devuelto')")
            ->orderBy('fecha_devolucion')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Prestamos/Index', [
            'prestamos' => $prestamos,
            'filters'   => $request->only(['estado', 'search']),
        ]);
    }

    public function create(Request $request): Response
    {
        $socioInicial = null;
        if ($request->socio_id) {
            $socio = Socio::find($request->socio_id);
            if ($socio) {
                $socioInicial = [
                    'id'       => $socio->id,
                    'nombre'   => $socio->nombre,
                    'apellido' => $socio->apellido,
                    'email'    => $socio->email,
                ];
            }
        }

        return Inertia::render('Prestamos/Create', [
            'areas'        => Area::orderBy('nombre')->get(['id', 'nombre']),
            'socioInicial' => $socioInicial,
        ]);
    }

    public function store(StorePrestamoRequest $request): RedirectResponse
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
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('prestamos.index')->with('success', 'Préstamo registrado correctamente.');
    }

    public function showDevolucion(Prestamo $prestamo): Response
    {
        return Inertia::render('Prestamos/Return', [
            'prestamo' => $prestamo->load('socio', 'material'),
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
                'message'          => "Préstamo extendido {$request->dias} días.",
                'fecha_devolucion' => $prestamo->fecha_devolucion->format('Y-m-d'),
            ]);
        }

        return redirect()->route('prestamos.index')->with('success', "Préstamo extendido {$request->dias} días.");
    }
}
