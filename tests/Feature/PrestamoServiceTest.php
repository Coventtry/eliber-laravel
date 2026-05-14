<?php

namespace Tests\Feature;

use App\Models\Configuracion;
use App\Models\Material;
use App\Models\MaterialEjemplar;
use App\Models\Multa;
use App\Models\Prestamo;
use App\Models\Socio;
use App\Models\User;
use App\Services\PrestamoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Support\TestHelpers;
use Tests\TestCase;

class PrestamoServiceTest extends TestCase
{
    use RefreshDatabase, TestHelpers;

    private PrestamoService $service;
    private User $bibliotecario;
    private Socio $socio;
    private Material $material;
    private MaterialEjemplar $ejemplar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service      = app(PrestamoService::class);
        $this->bibliotecario = $this->createBibliotecario();
        $this->actingAs($this->bibliotecario);

        $institucionId = $this->bibliotecario->institucion_id;

        $this->socio = Socio::forceCreate([
            'nombre'        => 'Ana',
            'apellido'      => 'García',
            'email'         => 'ana@test.com',
            'activo'        => true,
            'institucion_id' => $institucionId,
        ]);

        $this->material = Material::forceCreate([
            'titulo'         => 'Libro de prueba',
            'codigo'         => 'TST-001',
            'disponibilidad' => 1,
            'institucion_id' => $institucionId,
        ]);

        $this->ejemplar = MaterialEjemplar::forceCreate([
            'material_id'    => $this->material->id,
            'institucion_id' => $institucionId,
            'codigo_ejemplar' => 'TST-001-E1',
            'estado'         => 'disponible',
        ]);
    }

    public function test_crear_prestamo_reduces_disponibilidad_and_marks_ejemplar_prestado(): void
    {
        $fecha = now()->addDays(7)->toDateString();

        $prestamos = $this->service->crearPrestamo(
            $this->socio->id,
            $this->material->id,
            1,
            $fecha
        );

        $this->assertCount(1, $prestamos);
        $this->assertEquals('activo', $prestamos[0]->estado);
        $this->assertEquals($this->ejemplar->id, $prestamos[0]->ejemplar_id);

        $this->assertEquals(0, $this->material->fresh()->disponibilidad);
        $this->assertEquals('prestado', $this->ejemplar->fresh()->estado);
    }

    public function test_crear_prestamo_falla_si_no_hay_stock(): void
    {
        $this->ejemplar->update(['estado' => 'prestado']);
        $this->material->update(['disponibilidad' => 0]);

        $this->expectException(ValidationException::class);

        $this->service->crearPrestamo(
            $this->socio->id,
            $this->material->id,
            1,
            now()->addDays(7)->toDateString()
        );
    }

    public function test_crear_prestamo_falla_con_socio_inactivo(): void
    {
        $this->socio->update(['activo' => false]);

        $this->expectException(ValidationException::class);

        $this->service->crearPrestamo(
            $this->socio->id,
            $this->material->id,
            1,
            now()->addDays(7)->toDateString()
        );
    }

    public function test_devolver_prestamo_restores_disponibilidad_and_ejemplar(): void
    {
        $prestamo = Prestamo::forceCreate([
            'socio_id'        => $this->socio->id,
            'material_id'     => $this->material->id,
            'ejemplar_id'     => $this->ejemplar->id,
            'fecha_prestamo'  => now()->subDays(3)->toDateString(),
            'fecha_devolucion' => now()->addDays(4)->toDateString(),
            'estado'          => 'activo',
            'cantidad'        => 1,
            'institucion_id'  => $this->bibliotecario->institucion_id,
        ]);
        $this->ejemplar->update(['estado' => 'prestado']);
        $this->material->update(['disponibilidad' => 0]);

        $this->service->devolverPrestamo($prestamo);

        $this->assertEquals('devuelto', $prestamo->fresh()->estado);
        $this->assertEquals(1, $this->material->fresh()->disponibilidad);
        $this->assertEquals('disponible', $this->ejemplar->fresh()->estado);
    }

    public function test_extender_prestamo_adds_days(): void
    {
        $fecha = now()->addDays(3)->toDateString();
        $prestamo = Prestamo::forceCreate([
            'socio_id'        => $this->socio->id,
            'material_id'     => $this->material->id,
            'ejemplar_id'     => $this->ejemplar->id,
            'fecha_prestamo'  => now()->subDays(1)->toDateString(),
            'fecha_devolucion' => $fecha,
            'estado'          => 'activo',
            'cantidad'        => 1,
            'institucion_id'  => $this->bibliotecario->institucion_id,
        ]);
        $this->ejemplar->update(['estado' => 'prestado']);

        $this->service->extenderPrestamo($prestamo, 7);

        $nuevaFecha = now()->addDays(3 + 7)->toDateString();
        $this->assertEquals($nuevaFecha, $prestamo->fresh()->fecha_devolucion->toDateString());
    }

    public function test_marcar_atrasados_updates_estado(): void
    {
        $prestamo = Prestamo::forceCreate([
            'socio_id'        => $this->socio->id,
            'material_id'     => $this->material->id,
            'fecha_prestamo'  => now()->subDays(10)->toDateString(),
            'fecha_devolucion' => now()->subDays(2)->toDateString(),
            'estado'          => 'activo',
            'cantidad'        => 1,
            'institucion_id'  => $this->bibliotecario->institucion_id,
        ]);

        $count = $this->service->marcarAtrasados();

        $this->assertGreaterThanOrEqual(1, $count);
        $this->assertEquals('atrasado', $prestamo->fresh()->estado);
    }

    public function test_marcar_atrasados_generates_multa_when_configured(): void
    {
        Configuracion::forceCreate([
            'institucion_id' => $this->bibliotecario->institucion_id,
            'clave'          => 'monto_multa_por_dia',
            'valor'          => '10',
        ]);

        $prestamo = Prestamo::forceCreate([
            'socio_id'        => $this->socio->id,
            'material_id'     => $this->material->id,
            'fecha_prestamo'  => now()->subDays(10)->toDateString(),
            'fecha_devolucion' => now()->subDays(3)->toDateString(),
            'estado'          => 'activo',
            'cantidad'        => 1,
            'institucion_id'  => $this->bibliotecario->institucion_id,
        ]);

        $this->service->marcarAtrasados();

        $this->assertDatabaseHas('multas', [
            'prestamo_id' => $prestamo->id,
            'pagada'      => false,
        ]);

        $multa = Multa::withoutGlobalScopes()->where('prestamo_id', $prestamo->id)->first();
        $this->assertGreaterThan(0, $multa->monto);
    }

    public function test_marcar_atrasados_does_not_duplicate_multa(): void
    {
        Configuracion::forceCreate([
            'institucion_id' => $this->bibliotecario->institucion_id,
            'clave'          => 'monto_multa_por_dia',
            'valor'          => '10',
        ]);

        $prestamo = Prestamo::forceCreate([
            'socio_id'        => $this->socio->id,
            'material_id'     => $this->material->id,
            'fecha_prestamo'  => now()->subDays(10)->toDateString(),
            'fecha_devolucion' => now()->subDays(3)->toDateString(),
            'estado'          => 'activo',
            'cantidad'        => 1,
            'institucion_id'  => $this->bibliotecario->institucion_id,
        ]);

        $this->service->marcarAtrasados();
        $prestamo->update(['estado' => 'activo']); // reset to trigger again
        $this->service->marcarAtrasados();

        $this->assertEquals(
            1,
            Multa::withoutGlobalScopes()->where('prestamo_id', $prestamo->id)->count()
        );
    }
}
