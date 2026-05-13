<?php

namespace App\Providers;

use App\Models\Material;
use App\Models\Prestamo;
use App\Models\Socio;
use App\Policies\MaterialPolicy;
use App\Policies\PrestamoPolicy;
use App\Policies\SocioPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Socio::class, SocioPolicy::class);
        Gate::policy(Material::class, MaterialPolicy::class);
        Gate::policy(Prestamo::class, PrestamoPolicy::class);
        Gate::policy(Multa::class, MultaPolicy::class);
        Gate::policy(Alerta::class, AlertaPolicy::class);
        Gate::policy(Reserva::class, ReservaPolicy::class);
    }
}
