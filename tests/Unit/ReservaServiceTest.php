<?php

namespace Tests\Unit;

use App\Models\Area;
use App\Models\Institucion;
use App\Models\Material;
use App\Models\MaterialEjemplar;
use App\Models\Reserva;
use App\Models\Socio;
use App\Models\User;
use App\Services\ReservaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservaServiceTest extends TestCase
{
    use RefreshDatabase;

    private ReservaService $service;
    private Socio $socio;
    private Material $material;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ReservaService::class);

        $institucion = Institucion::create([
            'nombre' => 'Test Inst',
            'slug' => 'test-inst',
            'estado' => 'activa',
        ]);

        $user = User::create([
            'name' => 'Test User',
            'nombre' => 'Test',
            'email' => 'test@test.com',
            'usuario' => 'test-user',
            'password' => 'secret',
            'picture' => '',
            'institucion_id' => $institucion->id,
            'activo' => true,
        ]);
        $this->actingAs($user);

        $area = Area::forceCreate([
            'codigo_dewey' => '200',
            'nombre' => 'Matematica',
            'Abreviado' => 'MAT',
            'institucion_id' => $institucion->id,
        ]);

        $this->socio = Socio::forceCreate([
            'nombre' => 'Maria',
            'apellido' => 'Lopez',
            'email' => 'maria@test.com',
            'telefono' => '1234567890',
            'anio' => 6,
            'division' => 2,
            'activo' => true,
            'institucion_id' => $institucion->id,
        ]);

        $this->material = Material::forceCreate([
            'titulo' => 'Reservable Libro',
            'autor' => 'Autor Test',
            'anio_publicacion' => 2021,
            'area_id' => $area->id,
            'categoria' => 'LIBRO',
            'codigo' => '200-001',
            'disponibilidad' => 2,
            'disponibilidad_reservada' => 0,
            'editorial' => 'Test',
            'clasificacion_fisica' => 'MAT-A-(E)1-1',
            'institucion_id' => $institucion->id,
        ]);

        for ($i = 1; $i <= 2; $i++) {
            MaterialEjemplar::forceCreate([
                'material_id' => $this->material->id,
                'institucion_id' => $institucion->id,
                'codigo_ejemplar' => "200-001-E".str_pad($i, 2, '0', STR_PAD_LEFT),
                'estado' => 'disponible',
            ]);
        }

        $this->service = app(ReservaService::class);
    }

    public function test_crear_reserva_marca_ejemplar_reservado(): void
    {
        $reserva = $this->service->crearReserva($this->socio->id, $this->material->id);

        $this->assertDatabaseHas('reservas', [
            'id' => $reserva->id,
            'estado' => 'pendiente',
        ]);
        $this->assertDatabaseHas('material_ejemplares', [
            'id' => $reserva->ejemplar_id,
            'estado' => 'reservado',
        ]);
        $this->assertDatabaseHas('materiales', [
            'id' => $this->material->id,
            'disponibilidad_reservada' => 1,
        ]);
    }

    public function test_aprobar_reserva_crea_prestamo(): void
    {
        $reserva = $this->service->crearReserva($this->socio->id, $this->material->id);

        $prestamo = $this->service->aprobarReserva($reserva, 10);

        $this->assertDatabaseHas('reservas', [
            'id' => $reserva->id,
            'estado' => 'aprobada',
        ]);
        $this->assertDatabaseHas('prestamos', [
            'id' => $prestamo->id,
            'socio_id' => $this->socio->id,
            'material_id' => $this->material->id,
            'estado' => 'activo',
        ]);
        $this->assertDatabaseHas('material_ejemplares', [
            'id' => $reserva->ejemplar_id,
            'estado' => 'prestado',
        ]);
    }

    public function test_rechazar_reserva_libera_ejemplar(): void
    {
        $reserva = $this->service->crearReserva($this->socio->id, $this->material->id);

        $this->service->rechazarReserva($reserva, 'No disponible');

        $this->assertDatabaseHas('reservas', [
            'id' => $reserva->id,
            'estado' => 'rechazada',
        ]);
        $this->assertDatabaseHas('material_ejemplares', [
            'id' => $reserva->ejemplar_id,
            'estado' => 'disponible',
        ]);
    }

    public function test_expirar_reservas_vencidas(): void
    {
        $reserva = $this->service->crearReserva($this->socio->id, $this->material->id);

        Reserva::where('id', $reserva->id)->update([
            'fecha_vencimiento' => now()->subDay(),
        ]);

        $count = $this->service->expirarReservasVencidas();

        $this->assertSame(1, $count);
        $this->assertDatabaseHas('reservas', [
            'id' => $reserva->id,
            'estado' => 'expirada',
        ]);
    }

    public function test_crear_reserva_duplicada_lanza_error(): void
    {
        $this->service->crearReserva($this->socio->id, $this->material->id);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Ya tienes una reserva activa');

        $this->service->crearReserva($this->socio->id, $this->material->id);
    }

    public function test_aprobar_reserva_no_pendiente_lanza_error(): void
    {
        $reserva = $this->service->crearReserva($this->socio->id, $this->material->id);
        $this->service->rechazarReserva($reserva);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Solo reservas pendientes');

        $this->service->aprobarReserva($reserva);
    }
}
