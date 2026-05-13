<?php

namespace Tests\Unit\Services;

use App\Models\Alerta;
use App\Models\Area;
use App\Models\Institucion;
use App\Models\Material;
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

        $institucion = Institucion::create(['nombre' => 'Test', 'slug' => 'test', 'estado' => 'activa']);
        $user = User::create([
            'name' => 'Test', 'nombre' => 'Test', 'email' => 't@t.com',
            'usuario' => 'test', 'password' => 'x', 'picture' => '',
            'institucion_id' => $institucion->id, 'activo' => true,
        ]);
        $this->actingAs($user);

        $area = Area::forceCreate([
            'codigo_dewey' => '100', 'nombre' => 'Filosofia',
            'Abreviado' => 'FIL', 'institucion_id' => $institucion->id,
        ]);

        $this->socio = Socio::forceCreate([
            'nombre' => 'Ana', 'apellido' => 'Garcia',
            'email' => 'ana@test.com', 'telefono' => '1234567890',
            'anio' => 4, 'division' => 2, 'activo' => 1,
            'institucion_id' => $institucion->id,
        ]);

        $this->material = Material::forceCreate([
            'titulo' => 'Libro Reserva', 'autor' => 'Autor R', 'anio_publicacion' => 2021,
            'area_id' => $area->id, 'categoria' => 'LIBRO',
            'codigo' => '100-001', 'disponibilidad' => 2, 'disponibilidad_reservada' => 0,
            'editorial' => 'Ed R', 'clasificacion_fisica' => 'FIL-A-(E)1-1',
            'institucion_id' => $institucion->id,
        ]);

        $this->service = $this->app->make(ReservaService::class);
    }

    public function test_crear_reserva(): void
    {
        $reserva = $this->service->crearReserva($this->socio->id, $this->material->id);

        $this->assertDatabaseHas('reservas', ['id' => $reserva->id, 'estado' => 'pendiente']);
        $this->assertDatabaseHas('materiales', [
            'id' => $this->material->id, 'disponibilidad_reservada' => 1,
        ]);
    }

    public function test_crear_reserva_crea_alerta_para_bibliotecario(): void
    {
        $this->service->crearReserva($this->socio->id, $this->material->id);

        $this->assertDatabaseHas('alertas', [
            'tipo' => 'solicitud_reserva',
            'leida' => false,
        ]);
    }

    public function test_crear_reserva_fails_when_no_stock(): void
    {
        $this->material->update(['disponibilidad' => 0, 'disponibilidad_reservada' => 0]);

        $this->expectException(\Exception::class);
        $this->service->crearReserva($this->socio->id, $this->material->id);
    }

    public function test_crear_reserva_fails_on_duplicate(): void
    {
        $this->service->crearReserva($this->socio->id, $this->material->id);

        $this->expectException(\Exception::class);
        $this->service->crearReserva($this->socio->id, $this->material->id);
    }

    public function test_aprobar_reserva(): void
    {
        $reserva = $this->service->crearReserva($this->socio->id, $this->material->id);

        $prestamo = $this->service->aprobarReserva($reserva, 10);

        $this->assertDatabaseHas('reservas', ['id' => $reserva->id, 'estado' => 'aprobada']);
        $this->assertDatabaseHas('prestamos', ['id' => $prestamo->id, 'estado' => 'activo']);
        $this->assertDatabaseHas('materiales', [
            'id' => $this->material->id, 'disponibilidad' => 1, 'disponibilidad_reservada' => 0,
        ]);
    }

    public function test_aprobar_reserva_fails_if_not_pendiente(): void
    {
        $reserva = $this->service->crearReserva($this->socio->id, $this->material->id);
        $this->service->rechazarReserva($reserva);

        $this->expectException(\Exception::class);
        $this->service->aprobarReserva($reserva->fresh(), 10);
    }

    public function test_rechazar_reserva(): void
    {
        $reserva = $this->service->crearReserva($this->socio->id, $this->material->id);

        $this->service->rechazarReserva($reserva, 'Material no disponible para esta fecha');

        $this->assertDatabaseHas('reservas', ['id' => $reserva->id, 'estado' => 'rechazada']);
        $this->assertDatabaseHas('materiales', [
            'id' => $this->material->id, 'disponibilidad_reservada' => 0,
        ]);
    }

    public function test_cancelar_reserva(): void
    {
        $reserva = $this->service->crearReserva($this->socio->id, $this->material->id);

        $this->service->cancelarReserva($reserva);

        $this->assertDatabaseHas('reservas', ['id' => $reserva->id, 'estado' => 'expirada']);
    }

    public function test_expirar_reservas_vencidas(): void
    {
        $reserva = $this->service->crearReserva($this->socio->id, $this->material->id);

        $reserva->update(['fecha_vencimiento' => now()->subDay()]);

        $count = $this->service->expirarReservasVencidas();

        $this->assertEquals(1, $count);
        $this->assertDatabaseHas('reservas', ['id' => $reserva->id, 'estado' => 'expirada']);
    }
}
