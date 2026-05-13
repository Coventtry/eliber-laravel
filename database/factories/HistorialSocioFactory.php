<?php

namespace Database\Factories;

use App\Models\HistorialSocio;
use App\Models\Socio;
use Illuminate\Database\Eloquent\Factories\Factory;

class HistorialSocioFactory extends Factory
{
    protected $model = HistorialSocio::class;

    public function definition(): array
    {
        $socio = Socio::factory()->create();

        return [
            'institucion_id' => $socio->institucion_id,
            'id_socio' => $socio->id,
            'accion' => fake()->randomElement(['ALTA', 'BAJA']),
            'fecha' => now(),
            'observaciones' => fake()->sentence(),
        ];
    }
}
