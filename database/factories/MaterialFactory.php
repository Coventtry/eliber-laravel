<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\Institucion;
use App\Models\Material;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaterialFactory extends Factory
{
    protected $model = Material::class;

    public function definition(): array
    {
        $area = Area::factory()->create();

        return [
            'institucion_id' => $area->institucion_id,
            'titulo' => fake()->sentence(3),
            'autor' => fake()->name(),
            'anio_publicacion' => fake()->year(),
            'area_id' => $area->id,
            'categoria' => fake()->randomElement(['LIBRO', 'REVISTA', 'DVD', 'MAPA']),
            'codigo' => fake()->unique()->numerify('###-###'),
            'disponibilidad' => fake()->numberBetween(1, 10),
            'disponibilidad_reservada' => fake()->numberBetween(0, 3),
            'editorial' => fake()->company(),
            'clasificacion_fisica' => fake()->regexify('[A-Z]{3}-[A-Z]-\([A-Z]\)[0-9]-[0-9]'),
        ];
    }
}
