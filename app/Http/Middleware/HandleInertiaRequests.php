<?php

namespace App\Http\Middleware;

use App\Services\PrestamoService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $vencimientos = 0;
        if ($request->user()) {
            $vencimientos = app(PrestamoService::class)->obtenerVencimientosProximos(4)->count();
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id'          => $request->user()->id,
                    'nombre'      => $request->user()->nombre,
                    'usuario'     => $request->user()->usuario,
                    'picture_url' => $request->user()->picture_url,
                ] : null,
            ],
            'flash' => [
                'success' => session('success'),
                'error'   => session('error'),
            ],
            'vencimientos_proximos' => $vencimientos,
        ]);
    }
}
