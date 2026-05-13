<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\Institucion;
use Illuminate\Database\Eloquent\Factories\Factory;

class AreaFactory extends Factory
{
    protected $model = Area::class;

    public function definition(): array
    {
        return [
            'institucion_id' => Institucion::factory(),
            'codigo_dewey' => fake()->unique()->numerify('###'),
            'nombre' => fake()->word(),
            'Abreviado' => strtoupper(fake()->lexify('???')),
        ];
    }
}
