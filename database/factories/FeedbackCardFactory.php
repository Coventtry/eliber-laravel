<?php

namespace Database\Factories;

use App\Models\FeedbackCard;
use App\Models\Institucion;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeedbackCardFactory extends Factory
{
    protected $model = FeedbackCard::class;

    public function definition(): array
    {
        return [
            'institucion_id' => Institucion::factory(),
            'creado_por' => null,
            'titulo' => fake()->sentence(4),
            'descripcion' => fake()->paragraph(),
            'tags' => fake()->randomElements(['bug', 'feature', 'mejora', 'ui'], fake()->numberBetween(1, 3)),
            'prioridad' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
            'columna' => fake()->randomElement(['backlog', 'in_progress', 'completed', 'published']),
        ];
    }
}
