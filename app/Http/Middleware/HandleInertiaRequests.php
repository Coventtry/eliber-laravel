<?php

namespace App\Http\Middleware;

use App\Models\Alerta;
use App\Models\Configuracion;
use App\Models\FooterLink;
use App\Models\Institucion;
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
        $alertasNoLeidas = 0;
        $anuncio = null;
        $footerLinks = [];
        $institucionActiva = null;
        $instituciones = [];
        $logoUrl = '';
        $user = $request->user();

        if ($user) {
            $diasAlerta = (int) Configuracion::get(tenantId(), 'dias_alerta_previa', 4);
            $logoUrl = Configuracion::get(tenantId(), 'logo_url', '');
            $vencimientos = app(PrestamoService::class)->obtenerVencimientosProximos($diasAlerta)->count();
            $alertasNoLeidas = Alerta::noLeidas()->count();

            $institucion = $user->hasRole('admin')
                ? Institucion::find(tenantId())
                : $user->institucion;

            if ($institucion) {
                $institucionActiva = [
                    'id' => $institucion->id,
                    'nombre' => $institucion->nombre,
                    'slug' => $institucion->slug,
                ];
                if ($institucion->anuncio_activo && $institucion->anuncio_texto) {
                    $anuncio = [
                        'texto' => $institucion->anuncio_texto,
                        'estilo' => $institucion->anuncio_estilo ?? 'info',
                    ];
                }
                $footerLinks = FooterLink::where('institucion_id', $institucion->id)
                    ->orderBy('orden')->orderBy('id')
                    ->get(['id', 'label', 'url'])
                    ->toArray();
            }

            if ($user->hasRole('admin')) {
                $instituciones = Institucion::where('estado', 'activa')
                    ->orderBy('nombre')
                    ->get(['id', 'nombre'])
                    ->toArray();
            }
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'nombre' => $user->nombre,
                    'usuario' => $user->usuario,
                    'picture_url' => $user->picture_url ?? null,
                ] : null,
                'permisos' => $user ? $user->getAllPermissions()->pluck('name')->toArray() : [],
                'roles' => $user ? $user->getRoleNames()->toArray() : [],
                'es_admin' => $user ? $user->hasRole('admin') : false,
            ],
            'menu' => $this->buildMenu($user),
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
                'info' => session('info'),
            ],
            'vencimientos_proximos' => $vencimientos,
            'alertas_no_leidas' => $alertasNoLeidas,
            'anuncio' => $anuncio,
            'footer_links' => $footerLinks,
            'institucion_activa' => $institucionActiva,
            'instituciones' => $instituciones,
            'logo_url' => $logoUrl,
        ]);
    }

    private function buildMenu($user): array
    {
        if (! $user) {
            return [];
        }

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
            ->filter(fn ($item) => $item['permission'] === null || $user->can($item['permission']))
            ->values()
            ->toArray();
    }
}
