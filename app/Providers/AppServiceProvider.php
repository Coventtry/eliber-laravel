<?php

namespace App\Providers;

use App\Models\Socio;
use App\Models\Material;
use App\Models\Prestamo;
use App\Policies\SocioPolicy;
use App\Policies\MaterialPolicy;
use App\Policies\PrestamoPolicy;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
    }
}
