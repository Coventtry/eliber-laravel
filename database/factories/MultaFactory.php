<?php

namespace Database\Factories;

use App\Models\Institucion;
use App\Models\Multa;
use App\Models\Prestamo;
use App\Models\Socio;
use Illuminate\Database\Eloquent\Factories\Factory;

class MultaFactory extends Factory
{
    protected $model = Multa::class;

    public function definition(): array
    {
        $socio = Socio::factory()->create();

        return [
            'institucion_id' => $socio->institucion_id,
            'socio_id' => $socio->id,
            'prestamo_id' => null,
            'monto' => fake()->randomFloat(2, 10, 500),
            'motivo' => 'Devolución tardía',
            'observaciones' => fake()->sentence(),
            'pagada' => false,
            'fecha_pago' => null,
            'fecha_creacion' => now()->toDateString(),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn() => [
            'pagada' => true,
            'fecha_pago' => now()->toDateString(),
        ]);
    }
}
