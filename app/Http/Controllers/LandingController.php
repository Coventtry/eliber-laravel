<?php

namespace App\Http\Controllers;

use App\Models\Noticia;
use Inertia\Inertia;
use Inertia\Response;

class LandingController extends Controller
{
    public function __invoke(): Response
    {
        $noticias = Noticia::latest()->take(6)->get(['id', 'titulo', 'cuerpo', 'created_at']);

        return Inertia::render('Landing', [
            'noticias' => $noticias,
        ]);
    }
}