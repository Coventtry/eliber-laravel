<?php

namespace Database\Factories;

use App\Models\Configuracion;
use App\Models\Institucion;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConfiguracionFactory extends Factory
{
    protected $model = Configuracion::class;

    public function definition(): array
    {
        return [
            'institucion_id' => Institucion::factory(),
            'clave' => fake()->word(),
            'valor' => fake()->word(),
        ];
    }

    public function withClave(string $clave, string $valor): static
    {
        return $this->state(fn() => ['clave' => $clave, 'valor' => $valor]);
    }
}
