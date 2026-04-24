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
        $user = $request->user();

        if ($user) {
            $vencimientos = app(PrestamoService::class)->obtenerVencimientosProximos(4)->count();
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id'          => $user->id,
                    'nombre'      => $user->nombre,
                    'usuario'    => $user->usuario,
                    'picture_url' => $user->picture_url ?? null,
                ] : null,
                'permisos' => $user ? $user->getAllPermissions()->pluck('name')->toArray() : [],
                'es_admin' => $user ? $user->hasRole('admin') : false,
            ],
            'menu' => $this->buildMenu($user),
            'flash' => [
                'success' => session('success'),
                'error'   => session('error'),
            ],
            'vencimientos_proximos' => $vencimientos,
        ]);
    }

    private function buildMenu($user): array
    {
        if (!$user) return [];

        $items = [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'permission' => null],
            ['label' => 'Socios', 'route' => 'socios.index', 'permission' => 'gestionar-socios'],
            ['label' => 'Materiales', 'route' => 'materiales.index', 'permission' => 'gestionar-materiales'],
            ['label' => 'Préstamos', 'route' => 'prestamos.index', 'permission' => 'gestionar-prestamos'],
            ['label' => 'Áreas', 'route' => 'areas.index', 'permission' => 'gestionar-areas'],
            ['label' => 'Noticias', 'route' => 'noticias.index', 'permission' => 'gestionar-noticias'],
            ['label' => 'Anotaciones', 'route' => 'anotaciones.index', 'permission' => 'gestionar-anotaciones'],
            ['label' => 'Usuarios',    'route' => 'usuarios.index',    'permission' => 'gestionar-usuarios'],
        ];

        return collect($items)
            ->filter(fn($item) => $item['permission'] === null || $user->can($item['permission']))
            ->values()
            ->toArray();
    }
}
