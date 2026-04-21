<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Noticia;
use Illuminate\Http\JsonResponse;

class NoticiaController extends Controller
{
    public function index(): JsonResponse
    {
        $noticias = Noticia::select('id', 'titulo', 'descripcion', 'fecha')
            ->orderBy('fecha', 'desc')
            ->paginate(10);

        return response()->json($noticias);
    }
}