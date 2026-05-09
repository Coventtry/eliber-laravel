<?php

namespace Tests\Unit;

use App\Models\Area;
use App\Models\Institucion;
use App\Models\Material;
use App\Models\MaterialEjemplar;
use App\Models\Socio;
use App\Models\User;
use App\Services\PrestamoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
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
        $this->service = app(PrestamoService::class);

        $institucion = Institucion::create([
            'nombre' => 'Test Inst',
            'slug' => 'test-inst',
            'estado' => 'activa',
        ]);

        $area = Area::forceCreate([
            'codigo_dewey' => '100',
            'nombre' => 'Filosofia',
            'Abreviado' => 'FIL',
            'institucion_id' => $institucion->id,
        ]);

        $this->socio = Socio::forceCreate([
            'nombre' => 'Juan',
            'apellido' => 'Perez',
            'email' => 'juan@test.com',
            'telefono' => '1234567890',
            'anio' => 5,
            'division' => 1,
            'activo' => true,
            'institucion_id' => $institucion->id,
        ]);

        $this->material = Material::forceCreate([
            'titulo' => 'Test Libro',
            'autor' => 'Autor Test',
            'anio_publicacion' => 2020,
            'area_id' => $area->id,
            'categoria' => 'LIBRO',
            'codigo' => '100-001',
            'disponibilidad' => 3,
            'disponibilidad_reservada' => 0,
            'editorial' => 'Test',
            'clasificacion_fisica' => 'FIL-A-(E)1-1',
            'institucion_id' => $institucion->id,
        ]);

        for ($i = 1; $i <= 3; $i++) {
            MaterialEjemplar::forceCreate([
                'material_id' => $this->material->id,
                'institucion_id' => $institucion->id,
                'codigo_ejemplar' => "100-001-E".str_pad($i, 2, '0', STR_PAD_LEFT),
                'estado' => 'disponible',
            ]);
        }
    }

    public function test_crear_prestamo_descuenta_stock(): void
    {
        $this->service->crearPrestamo(
            $this->socio->id,
            $this->material->id,
            1,
            now()->addDays(7)->toDateString()
        );

        $this->assertDatabaseHas('materiales', [
            'id' => $this->material->id,
            'disponibilidad' => 2,
        ]);
        $this->assertDatabaseHas('prestamos', [
            'socio_id' => $this->socio->id,
            'material_id' => $this->material->id,
            'estado' => 'activo',
        ]);
    }

    public function test_crear_prestamo_con_socio_inactivo_lanza_error(): void
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

    public function test_crear_prestamo_sin_stock_lanza_error(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->crearPrestamo(
            $this->socio->id,
            $this->material->id,
            10,
            now()->addDays(7)->toDateString()
        );
    }

    public function test_devolver_prestamo_restaura_stock_y_ejemplar(): void
    {
        $prestamos = $this->service->crearPrestamo(
            $this->socio->id,
            $this->material->id,
            1,
            now()->addDays(7)->toDateString()
        );

        $prestamo = $prestamos[0];
        $this->service->devolverPrestamo($prestamo);

        $this->assertDatabaseHas('prestamos', [
            'id' => $prestamo->id,
            'estado' => 'devuelto',
        ]);
        $this->assertDatabaseHas('material_ejemplares', [
            'id' => $prestamo->ejemplar_id,
            'estado' => 'disponible',
        ]);
        $this->assertDatabaseHas('materiales', [
            'id' => $this->material->id,
            'disponibilidad' => 3,
        ]);
    }

    public function test_extender_prestamo_aumenta_fecha(): void
    {
        $prestamos = $this->service->crearPrestamo(
            $this->socio->id,
            $this->material->id,
            1,
            now()->addDays(7)->toDateString()
        );

        $prestamo = $prestamos[0];
        $fechaOriginal = $prestamo->fecha_devolucion->toDateString();

        $this->service->extenderPrestamo($prestamo, 5);

        $this->assertNotSame($fechaOriginal, $prestamo->fresh()->fecha_devolucion->toDateString());
    }

    public function test_extender_prestamo_con_dias_invalidos_lanza_error(): void
    {
        $prestamos = $this->service->crearPrestamo(
            $this->socio->id,
            $this->material->id,
            1,
            now()->addDays(7)->toDateString()
        );

        $this->expectException(ValidationException::class);

        $this->service->extenderPrestamo($prestamos[0], 31);
    }
}
