<?php

namespace Database\Factories;

use App\Models\Institucion;
use App\Models\Noticia;
use Illuminate\Database\Eloquent\Factories\Factory;

class NoticiaFactory extends Factory
{
    protected $model = Noticia::class;

    public function definition(): array
    {
        return [
            'institucion_id' => Institucion::factory(),
            'titulo' => fake()->sentence(),
            'descripcion' => fake()->paragraph(),
            'imagen' => null,
            'fecha' => fake()->dateTimeThisYear(),
        ];
    }
}
