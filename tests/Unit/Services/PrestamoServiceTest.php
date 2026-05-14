<?php

namespace Tests\Unit\Services;

use App\Models\Area;
use App\Models\Configuracion;
use App\Models\Institucion;
use App\Models\Material;
use App\Models\MaterialEjemplar;
use App\Models\Prestamo;
use App\Models\Socio;
use App\Models\User;
use App\Services\PrestamoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PrestamoServiceTest extends TestCase
{
    use RefreshDatabase;

    private PrestamoService $service;
    private Socio $socio;
    private Material $material;

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

        Configuracion::set($institucion->id, 'dias_prestamo', 14);

        $area = Area::forceCreate([
            'codigo_dewey' => '100', 'nombre' => 'Filosofia',
            'Abreviado' => 'FIL', 'institucion_id' => $institucion->id,
        ]);

        $this->socio = Socio::forceCreate([
            'nombre' => 'Juan', 'apellido' => 'Lopez',
            'email' => 'juan@test.com', 'telefono' => '1234567890',
            'anio' => 5, 'division' => 1, 'activo' => 1,
            'institucion_id' => $institucion->id,
        ]);

        $this->material = Material::forceCreate([
            'titulo' => 'Libro Test', 'autor' => 'Autor', 'anio_publicacion' => 2020,
            'area_id' => $area->id, 'categoria' => 'LIBRO',
            'codigo' => '100-001', 'disponibilidad' => 3, 'disponibilidad_reservada' => 0,
            'editorial' => 'Ed Test', 'clasificacion_fisica' => 'FIL-A-(E)1-1',
            'institucion_id' => $institucion->id,
        ]);

        foreach (range(1, 3) as $i) {
            MaterialEjemplar::forceCreate([
                'material_id'    => $this->material->id,
                'institucion_id' => $institucion->id,
                'codigo_ejemplar' => "100-001-E{$i}",
                'estado'         => 'disponible',
            ]);
        }

        $this->service = $this->app->make(PrestamoService::class);
    }

    public function test_crear_prestamo_success(): void
    {
        $prestamos = $this->service->crearPrestamo(
            $this->socio->id, $this->material->id, 1, now()->addDays(7)->toDateString()
        );

        $this->assertDatabaseHas('prestamos', ['id' => $prestamos[0]->id, 'estado' => 'activo']);
        $this->assertDatabaseHas('materiales', ['id' => $this->material->id, 'disponibilidad' => 2]);
    }

    public function test_crear_prestamo_fails_when_socio_inactive(): void
    {
        $this->socio->update(['activo' => 0]);

        $this->expectException(ValidationException::class);
        $this->service->crearPrestamo(
            $this->socio->id, $this->material->id, 1, now()->addDays(7)->toDateString()
        );
    }

    public function test_crear_prestamo_fails_when_exceeds_3_active(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            $m = Material::forceCreate([
                'titulo' => "Material $i", 'autor' => 'A', 'anio_publicacion' => 2020,
                'area_id' => $this->material->area_id, 'categoria' => 'LIBRO',
                'codigo' => "200-00$i", 'disponibilidad' => 5, 'disponibilidad_reservada' => 0,
                'editorial' => 'Ed', 'clasificacion_fisica' => 'FIL-A-(E)1-1',
                'institucion_id' => $this->material->institucion_id,
            ]);
            Prestamo::forceCreate([
                'socio_id'        => $this->socio->id,
                'material_id'     => $m->id,
                'fecha_prestamo'  => now()->toDateString(),
                'fecha_devolucion' => now()->addDays(7)->toDateString(),
                'estado'          => 'activo',
                'cantidad'        => 1,
                'institucion_id'  => $this->material->institucion_id,
            ]);
        }

        $this->expectException(ValidationException::class);
        $this->service->crearPrestamo(
            $this->socio->id, $this->material->id, 1, now()->addDays(7)->toDateString()
        );
    }

    public function test_crear_prestamo_fails_on_duplicate_material(): void
    {
        $this->service->crearPrestamo(
            $this->socio->id, $this->material->id, 1, now()->addDays(7)->toDateString()
        );

        $this->expectException(ValidationException::class);
        $this->service->crearPrestamo(
            $this->socio->id, $this->material->id, 1, now()->addDays(7)->toDateString()
        );
    }

    public function test_crear_prestamo_fails_when_insufficient_stock(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->crearPrestamo(
            $this->socio->id, $this->material->id, 99, now()->addDays(7)->toDateString()
        );
    }

    public function test_crear_prestamo_fails_on_date_out_of_range(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->crearPrestamo(
            $this->socio->id, $this->material->id, 1, now()->addDays(30)->toDateString()
        );
    }

    public function test_devolver_prestamo(): void
    {
        $prestamos = $this->service->crearPrestamo(
            $this->socio->id, $this->material->id, 1, now()->addDays(7)->toDateString()
        );
        $prestamo = $prestamos[0];

        $this->service->devolverPrestamo($prestamo);

        $this->assertDatabaseHas('prestamos', ['id' => $prestamo->id, 'estado' => 'devuelto']);
        $this->assertDatabaseHas('materiales', ['id' => $this->material->id, 'disponibilidad' => 3]);
    }

    public function test_extender_prestamo(): void
    {
        $prestamos = $this->service->crearPrestamo(
            $this->socio->id, $this->material->id, 1, now()->addDays(7)->toDateString()
        );
        $prestamo = $prestamos[0];

        $this->service->extenderPrestamo($prestamo, 5);

        $expected = now()->addDays(12)->toDateString();
        $this->assertEquals($expected, $prestamo->fresh()->fecha_devolucion->format('Y-m-d'));
    }

    public function test_extender_prestamo_within_range(): void
    {
        $prestamos = $this->service->crearPrestamo(
            $this->socio->id, $this->material->id, 1, now()->addDays(7)->toDateString()
        );
        $prestamo = $prestamos[0];

        $this->service->extenderPrestamo($prestamo, 30);

        $this->assertEquals(now()->addDays(37)->toDateString(), $prestamo->fresh()->fecha_devolucion->format('Y-m-d'));
    }

    public function test_marcar_atrasados(): void
    {
        $this->material->decrement('disponibilidad', 1);
        $prestamo = Prestamo::forceCreate([
            'socio_id'         => $this->socio->id,
            'material_id'      => $this->material->id,
            'fecha_prestamo'   => now()->subDays(5)->toDateString(),
            'fecha_devolucion' => now()->subDay()->toDateString(),
            'estado'           => 'activo',
            'cantidad'         => 1,
            'institucion_id'   => $this->socio->institucion_id,
        ]);

        $count = $this->service->marcarAtrasados();

        $this->assertEquals(1, $count);
        $this->assertDatabaseHas('prestamos', ['id' => $prestamo->id, 'estado' => 'atrasado']);
    }
}
