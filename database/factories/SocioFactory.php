<?php

namespace Database\Factories;

use App\Models\Institucion;
use App\Models\Socio;
use Illuminate\Database\Eloquent\Factories\Factory;

class SocioFactory extends Factory
{
    protected $model = Socio::class;

    public function definition(): array
    {
        return [
            'institucion_id' => Institucion::factory(),
            'nombre' => fake()->firstName(),
            'apellido' => fake()->lastName(),
            'telefono' => fake()->phoneNumber(),
            'direccion' => fake()->address(),
            'email' => fake()->unique()->safeEmail(),
            'anio' => fake()->numberBetween(1, 6),
            'division' => fake()->numberBetween(1, 6),
            'activo' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn() => ['activo' => false]);
    }
}
