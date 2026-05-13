<?php

namespace Tests\Unit\Services;

use App\Models\Area;
use App\Models\Configuracion;
use App\Models\Institucion;
use App\Models\Material;
use App\Models\Multa;
use App\Models\Prestamo;
use App\Models\Socio;
use App\Models\User;
use App\Services\MultaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultaServiceTest extends TestCase
{
    use RefreshDatabase;

    private MultaService $service;
    private Socio $socio;

    protected function setUp(): void
    {
        parent::setUp();

        $institucion = Institucion::create(['nombre' => 'Test', 'slug' => 'test', 'estado' => 'activa']);
        $user = User::create([
            'name' => 'Test', 'nombre' => 'Test', 'email' => 't@t.com',
            'usuario' => 'test', 'password' => 'x', 'picture' => '',
            'institucion_id' => $institucion->id, 'activo' => true,
        ]);
        $this->actingAs($user);

        Configuracion::set($institucion->id, 'monto_multa_diaria', 100);

        $this->socio = Socio::forceCreate([
            'nombre' => 'Juan', 'apellido' => 'Lopez',
            'email' => 'juan@test.com', 'telefono' => '1234567890',
            'anio' => 5, 'division' => 1, 'activo' => 1,
            'institucion_id' => $institucion->id,
        ]);

        $this->service = $this->app->make(MultaService::class);
    }

    public function test_registrar_multa(): void
    {
        $multa = $this->service->registrar($this->socio->id, 500, 'Devolución tardía');

        $this->assertDatabaseHas('multas', [
            'id' => $multa->id, 'socio_id' => $this->socio->id,
            'monto' => 500, 'motivo' => 'Devolución tardía', 'pagada' => false,
        ]);
    }

    public function test_pagar_multa(): void
    {
        $multa = $this->service->registrar($this->socio->id, 300, 'Daño material');

        $this->service->pagar($multa);

        $this->assertDatabaseHas('multas', ['id' => $multa->id, 'pagada' => true]);
        $this->assertNotNull($multa->fresh()->fecha_pago);
    }

    public function test_perdonar_multa(): void
    {
        $multa = $this->service->registrar($this->socio->id, 200, 'Demora');

        $this->service->perdonar($multa, 'Primera vez');

        $this->assertDatabaseHas('multas', ['id' => $multa->id, 'pagada' => true]);
    }

    public function test_generar_multa_por_vencimiento(): void
    {
        $area = Area::forceCreate([
            'codigo_dewey' => '100', 'nombre' => 'Filo',
            'Abreviado' => 'FIL', 'institucion_id' => $this->socio->institucion_id,
        ]);
        $material = Material::forceCreate([
            'titulo' => 'Libro', 'autor' => 'A', 'anio_publicacion' => 2020,
            'area_id' => $area->id, 'categoria' => 'LIBRO',
            'codigo' => '100-001', 'disponibilidad' => 3, 'disponibilidad_reservada' => 0,
            'editorial' => 'Ed', 'clasificacion_fisica' => 'FIL-A-(E)1-1',
            'institucion_id' => $this->socio->institucion_id,
        ]);

        $material->decrement('disponibilidad', 1);
        $prestamo = Prestamo::forceCreate([
            'socio_id'         => $this->socio->id,
            'material_id'      => $material->id,
            'fecha_prestamo'   => now()->subDays(10)->toDateString(),
            'fecha_devolucion' => now()->subDays(5)->toDateString(),
            'estado'           => 'activo',
            'cantidad'         => 1,
            'institucion_id'   => $this->socio->institucion_id,
        ]);

        $multa = $this->service->generarMultaPorVencimiento($prestamo);

        $this->assertNotNull($multa);
        $this->assertDatabaseHas('multas', [
            'id' => $multa->id, 'prestamo_id' => $prestamo->id,
            'pagada' => false,
        ]);
        $this->assertGreaterThan(0, $multa->monto);
    }

    public function test_generar_multa_no_duplicate(): void
    {
        $area = Area::forceCreate([
            'codigo_dewey' => '100', 'nombre' => 'Filo',
            'Abreviado' => 'FIL', 'institucion_id' => $this->socio->institucion_id,
        ]);
        $material = Material::forceCreate([
            'titulo' => 'Libro', 'autor' => 'A', 'anio_publicacion' => 2020,
            'area_id' => $area->id, 'categoria' => 'LIBRO',
            'codigo' => '100-002', 'disponibilidad' => 3, 'disponibilidad_reservada' => 0,
            'editorial' => 'Ed', 'clasificacion_fisica' => 'FIL-A-(E)1-1',
            'institucion_id' => $this->socio->institucion_id,
        ]);

        $material->decrement('disponibilidad', 1);
        $prestamo = Prestamo::forceCreate([
            'socio_id'         => $this->socio->id,
            'material_id'      => $material->id,
            'fecha_prestamo'   => now()->subDays(3)->toDateString(),
            'fecha_devolucion' => now()->subDays(3)->toDateString(),
            'estado'           => 'activo',
            'cantidad'         => 1,
            'institucion_id'   => $this->socio->institucion_id,
        ]);

        $this->service->generarMultaPorVencimiento($prestamo);
        $result = $this->service->generarMultaPorVencimiento($prestamo);

        $this->assertNull($result);
        $this->assertEquals(1, Multa::where('prestamo_id', $prestamo->id)->count());
    }
}
