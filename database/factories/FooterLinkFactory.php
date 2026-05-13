<?php

namespace Database\Factories;

use App\Models\FooterLink;
use App\Models\Institucion;
use Illuminate\Database\Eloquent\Factories\Factory;

class FooterLinkFactory extends Factory
{
    protected $model = FooterLink::class;

    public function definition(): array
    {
        return [
            'institucion_id' => Institucion::factory(),
            'label' => fake()->word(),
            'url' => fake()->url(),
            'orden' => fake()->numberBetween(0, 10),
        ];
    }
}
