<?php

namespace Database\Factories;

use App\Models\Alerta;
use App\Models\Institucion;
use Illuminate\Database\Eloquent\Factories\Factory;

class AlertaFactory extends Factory
{
    protected $model = Alerta::class;

    public function definition(): array
    {
        return [
            'institucion_id' => Institucion::factory(),
            'prestamo_id' => null,
            'tipo' => fake()->randomElement(['proximo_vencer', 'vencido', 'renovacion', 'solicitud_reserva']),
            'descripcion' => fake()->sentence(),
            'fecha_alerta' => now(),
            'leida' => false,
        ];
    }

    public function read(): static
    {
        return $this->state(fn() => ['leida' => true]);
    }
}
