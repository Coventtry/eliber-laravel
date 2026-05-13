<?php

namespace Tests\Unit\Services;

use App\Models\HistorialSocio;
use App\Models\Socio;
use App\Services\SocioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocioServiceTest extends TestCase
{
    use RefreshDatabase;

    private SocioService $service;
    private Socio $socio;

    protected function setUp(): void
    {
        parent::setUp();

        $this->socio = Socio::factory()->create();
        $this->service = $this->app->make(SocioService::class);
    }

    public function test_da_de_baja(): void
    {
        $this->service->darDeBaja($this->socio, 'Baja por prueba');

        $this->socio->refresh();
        $this->assertFalse($this->socio->activo);

        $this->assertDatabaseHas('historial_socios', [
            'id_socio' => $this->socio->id,
            'accion' => 'BAJA',
            'observaciones' => 'Baja por prueba',
        ]);
    }

    public function test_da_de_baja_con_observaciones_default(): void
    {
        $this->service->darDeBaja($this->socio);

        $this->assertDatabaseHas('historial_socios', [
            'id_socio' => $this->socio->id,
            'accion' => 'BAJA',
            'observaciones' => 'Baja registrada.',
        ]);
    }

    public function test_reactivar(): void
    {
        $this->socio->update(['activo' => 0]);

        $this->service->reactivar($this->socio);

        $this->socio->refresh();
        $this->assertTrue($this->socio->activo);

        $this->assertDatabaseHas('historial_socios', [
            'id_socio' => $this->socio->id,
            'accion' => 'ALTA',
        ]);
    }

    public function test_baja_y_reactivar_crean_dos_registros(): void
    {
        $this->service->darDeBaja($this->socio);
        $this->service->reactivar($this->socio);

        $this->assertEquals(2, HistorialSocio::where('id_socio', $this->socio->id)->count());
    }
}
