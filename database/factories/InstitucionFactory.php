<?php

namespace Database\Factories;

use App\Models\Institucion;
use Illuminate\Database\Eloquent\Factories\Factory;

class InstitucionFactory extends Factory
{
    protected $model = Institucion::class;

    public function definition(): array
    {
        return [
            'nombre' => 'Institución ' . fake()->city(),
            'slug' => fake()->slug(),
            'estado' => 'activa',
        ];
    }
}
