<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMaterialRequest;
use App\Models\Area;
use App\Models\CategoriaFisica;
use App\Models\Material;
use App\Services\MaterialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MaterialController extends Controller
{
    public function __construct(private MaterialService $materialService) {}

    public function index(Request $request): Response
    {
        $materiales = Material::with('area')
            ->when($request->busqueda, fn($q, $s) =>
                $q->where('titulo', 'like', "%{$s}%")->orWhere('autor', 'like', "%{$s}%")->orWhere('codigo', 'like', "%{$s}%")
            )
            ->when($request->area_id, fn($q, $a) => $q->where('area_id', $a))
            ->orderBy('titulo')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Materiales/Index', [
            'materiales' => $materiales,
            'areas'      => Area::orderBy('nombre')->get(['id', 'nombre']),
            'filters'    => $request->only(['busqueda', 'area_id']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Materiales/Create', [
            'areas'      => Area::orderBy('codigo_dewey')->get(['id', 'nombre', 'codigo_dewey', 'Abreviado']),
            'categorias' => CategoriaFisica::orderBy('nombre')->pluck('nombre'),
        ]);
    }

    public function store(StoreMaterialRequest $request): RedirectResponse
    {
        $datos    = $request->validated();
        $area    = Area::findOrFail($datos['area_id']);
        $datos['codigo'] = $this->materialService->generarCodigo($area);
        $datos['institucion_id'] = $request->user()->institucion_id;

        if ($request->filled('pasillo')) {
            $datos['clasificacion_fisica'] = $this->materialService->generarClasificacionFisica(
                $area,
                $request->pasillo,
                $request->tipo_almacenamiento,
                $request->estante,
                $request->nivel
            );
        }

        $material = Material::create($datos);
        $this->materialService->generarQR($material);

        return redirect()->route('materiales.index')->with('success', 'Material registrado correctamente.');
    }

    public function edit(Material $material): Response
    {
        return Inertia::render('Materiales/Edit', [
            'material'   => $material->load('area'),
            'areas'      => Area::orderBy('codigo_dewey')->get(['id', 'nombre', 'codigo_dewey', 'Abreviado']),
            'categorias' => CategoriaFisica::orderBy('nombre')->pluck('nombre'),
            'qrUrl'      => $this->materialService->urlCodigoQr($material),
        ]);
    }

    public function update(StoreMaterialRequest $request, Material $material): RedirectResponse
    {
        $this->authorize('update', $material);
        $datos = $request->validated();

        if ($request->filled('pasillo')) {
            $area = Area::findOrFail($datos['area_id']);
            $datos['clasificacion_fisica'] = $this->materialService->generarClasificacionFisica(
                $area,
                $request->pasillo,
                $request->tipo_almacenamiento,
                $request->estante,
                $request->nivel
            );
        }

        $material->update($datos);
        $this->materialService->generarQR($material);

        return redirect()->route('materiales.index')->with('success', 'Material actualizado.');
    }

    public function destroy(Material $material): RedirectResponse
    {
        $this->authorize('delete', $material);
        $material->delete();
        return redirect()->route('materiales.index')->with('success', 'Material eliminado.');
    }

    public function fichaPublica(int $id): Response
    {
        $material = Material::withoutGlobalScopes()->with('area', 'ubicacion')->findOrFail($id);
        return Inertia::render('Materiales/Ficha', ['material' => $material]);
    }

    public function qrCode(Material $material): Response
    {
        $urlQr = $this->materialService->generarQR($material);

        return Inertia::render('Materiales/QrCode', [
            'material' => $material->only('id', 'titulo', 'codigo', 'autor', 'clasificacion_fisica'),
            'qrUrl'    => $urlQr,
        ]);
    }

    public function disponibles(Request $request)
    {
        $materiales = Material::disponible()
            ->when($request->area_id, fn($q, $a) => $q->where('area_id', $a))
            ->when($request->busqueda, fn($q, $s) => $q->where(function ($sq) use ($s) {
                $sq->where('titulo', 'like', "%{$s}%")
                   ->orWhere('codigo', 'like', "%{$s}%");
            }))
            ->select('id', 'titulo', 'codigo', 'disponibilidad')
            ->limit(20)
            ->get();

        return response()->json($materiales);
    }

    public function ultimoCodigo(Request $request)
    {
        $area   = Area::findOrFail($request->area_id);
        $codigo = $this->materialService->generarCodigo($area);
        return response()->json(['codigo' => $codigo]);
    }
}
