<?php

namespace Database\Factories;

use App\Models\Institucion;
use App\Models\Material;
use App\Models\Prestamo;
use App\Models\Socio;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrestamoFactory extends Factory
{
    protected $model = Prestamo::class;

    public function definition(): array
    {
        $socio = Socio::factory()->create();
        $material = Material::factory()->create(['institucion_id' => $socio->institucion_id]);

        return [
            'institucion_id' => $socio->institucion_id,
            'socio_id' => $socio->id,
            'material_id' => $material->id,
            'fecha_prestamo' => now()->subDays(fake()->numberBetween(1, 30))->toDateString(),
            'fecha_devolucion' => now()->addDays(fake()->numberBetween(1, 14))->toDateString(),
            'estado' => 'activo',
            'cantidad' => 1,
        ];
    }

    public function returned(): static
    {
        return $this->state(fn() => ['estado' => 'devuelto']);
    }

    public function overdue(): static
    {
        return $this->state(fn() => [
            'fecha_devolucion' => now()->subDays(fake()->numberBetween(1, 10))->toDateString(),
            'estado' => 'atrasado',
        ]);
    }
}
