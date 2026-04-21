<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\JsonResponse;

class MaterialController extends Controller
{
    public function index(): JsonResponse
    {
        $materiales = Material::select('id', 'titulo', 'autor', 'anio_publicacion', 'categoria', 'codigo', 'disponibilidad')
            ->paginate(20);

        return response()->json($materiales);
    }

    public function show(int $material): JsonResponse
    {
        $material = Material::select('id', 'titulo', 'autor', 'anio_publicacion', 'categoria', 'codigo', 'disponibilidad', 'editorial', 'area_id')
            ->findOrFail($material);

        return response()->json($material);
    }
}