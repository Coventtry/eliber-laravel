<?php

namespace Database\Factories;

use App\Models\Institucion;
use App\Models\Material;
use App\Models\Reserva;
use App\Models\Socio;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservaFactory extends Factory
{
    protected $model = Reserva::class;

    public function definition(): array
    {
        $socio = Socio::factory()->create();
        $material = Material::factory()->create(['institucion_id' => $socio->institucion_id]);

        return [
            'institucion_id' => $socio->institucion_id,
            'material_id' => $material->id,
            'socio_id' => $socio->id,
            'estado' => 'pendiente',
            'fecha_reserva' => now(),
            'fecha_vencimiento' => now()->addDays(2),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn() => ['estado' => 'aprobada']);
    }

    public function rejected(): static
    {
        return $this->state(fn() => ['estado' => 'rechazada']);
    }
}
