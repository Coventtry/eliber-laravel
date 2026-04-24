<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMaterialRequest;
use App\Models\Area;
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
            ->when($request->search, fn($q, $s) =>
                $q->where('titulo', 'like', "%{$s}%")->orWhere('autor', 'like', "%{$s}%")
            )
            ->when($request->area_id, fn($q, $a) => $q->where('area_id', $a))
            ->orderBy('titulo')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Materiales/Index', [
            'materiales' => $materiales,
            'areas'      => Area::orderBy('nombre')->get(['id', 'nombre']),
            'filters'    => $request->only(['search', 'area_id']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Materiales/Create', [
            'areas' => Area::orderBy('nombre')->get(['id', 'nombre', 'codigo_dewey', 'Abreviado']),
        ]);
    }

    public function store(StoreMaterialRequest $request): RedirectResponse
    {
        $data    = $request->validated();
        $area    = Area::findOrFail($data['area_id']);
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

        return redirect()->route('materiales.index')->with('success', 'Material registrado correctamente.');
    }

    public function edit(Material $material): Response
    {
        return Inertia::render('Materiales/Edit', [
            'material' => $material->load('area', 'ubicacion'),
            'areas'    => Area::orderBy('nombre')->get(['id', 'nombre', 'codigo_dewey', 'Abreviado']),
            'qrUrl'    => $this->materialService->urlQR($material),
        ]);
    }

    public function update(StoreMaterialRequest $request, Material $material): RedirectResponse
    {
        $this->authorize('update', $material);
        $material->update($request->validated());
        return redirect()->route('materiales.index')->with('success', 'Material actualizado.');
    }

    public function destroy(Material $material): RedirectResponse
    {
        $this->authorize('delete', $material);
        $material->delete();
        return redirect()->route('materiales.index')->with('success', 'Material eliminado.');
    }

    public function qrCode(Material $material): Response
    {
        $qrUrl = $this->materialService->urlQR($material)
            ?? $this->materialService->generarQR($material);

        return Inertia::render('Materiales/QrCode', [
            'material' => $material->only('id', 'titulo', 'codigo'),
            'qrUrl'    => $qrUrl,
        ]);
    }

    public function disponibles(Request $request)
    {
        $materiales = Material::disponible()
            ->when($request->area_id, fn($q, $a) => $q->where('area_id', $a))
            ->select('id', 'titulo', 'codigo', 'disponibilidad')
            ->limit(50)
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
