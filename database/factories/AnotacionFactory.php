<?php

namespace Database\Factories;

use App\Models\Anotacion;
use App\Models\Institucion;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnotacionFactory extends Factory
{
    protected $model = Anotacion::class;

    public function definition(): array
    {
        return [
            'institucion_id' => Institucion::factory(),
            'anotacion' => fake()->paragraph(),
            'fecha' => now(),
        ];
    }
}
