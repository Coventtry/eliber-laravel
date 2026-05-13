<?php

namespace Tests\Unit;

use App\Models\HistorialSocio;
use App\Models\Institucion;
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
        $this->service = app(SocioService::class);

        $institucion = Institucion::create([
            'nombre' => 'Test Inst',
            'slug' => 'test-inst',
            'estado' => 'activa',
        ]);

        $this->socio = Socio::forceCreate([
            'nombre' => 'Pedro',
            'apellido' => 'Garcia',
            'email' => 'pedro@test.com',
            'telefono' => '1234567890',
            'anio' => 4,
            'division' => 3,
            'activo' => true,
            'institucion_id' => $institucion->id,
        ]);
    }

    public function test_dar_de_baja_desactiva_socio_y_registra_historial(): void
    {
        $this->service->darDeBaja($this->socio, 'Baja por prueba');

        $this->assertDatabaseHas('socios', [
            'id' => $this->socio->id,
            'activo' => 0,
        ]);
        $this->assertDatabaseHas('historial_socios', [
            'id_socio' => $this->socio->id,
            'accion' => 'BAJA',
        ]);
    }

    public function test_reactivar_activa_socio_y_registra_historial(): void
    {
        $this->socio->update(['activo' => 0]);

        $this->service->reactivar($this->socio);

        $this->assertDatabaseHas('socios', [
            'id' => $this->socio->id,
            'activo' => 1,
        ]);
        $this->assertDatabaseHas('historial_socios', [
            'id_socio' => $this->socio->id,
            'accion' => 'ALTA',
        ]);
    }
}
