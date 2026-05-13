<?php

namespace Database\Factories;

use App\Models\Faq;
use App\Models\Institucion;
use Illuminate\Database\Eloquent\Factories\Factory;

class FaqFactory extends Factory
{
    protected $model = Faq::class;

    public function definition(): array
    {
        return [
            'institucion_id' => Institucion::factory(),
            'pregunta' => fake()->sentence() . '?',
            'respuesta' => fake()->paragraph(),
            'orden' => fake()->numberBetween(0, 10),
            'activa' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn() => ['activa' => false]);
    }
}
