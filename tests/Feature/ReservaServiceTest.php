<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\MaterialEjemplar;
use App\Models\Prestamo;
use App\Models\Reserva;
use App\Models\Socio;
use App\Models\User;
use App\Services\ReservaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\Support\TestHelpers;
use Tests\TestCase;

class ReservaServiceTest extends TestCase
{
    use RefreshDatabase, TestHelpers;

    private ReservaService $service;
    private User $bibliotecario;
    private Socio $socio;
    private Material $material;
    private MaterialEjemplar $ejemplar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service       = app(ReservaService::class);
        $this->bibliotecario = $this->createBibliotecario();
        $this->actingAs($this->bibliotecario);

        $institucionId = $this->bibliotecario->institucion_id;

        $this->socio = Socio::forceCreate([
            'nombre'         => 'Carlos',
            'apellido'       => 'López',
            'email'          => 'carlos@test.com',
            'activo'         => true,
            'institucion_id' => $institucionId,
        ]);

        $this->material = Material::forceCreate([
            'titulo'                  => 'Material reservable',
            'codigo'                  => 'RES-001',
            'disponibilidad'          => 1,
            'disponibilidad_reservada' => 0,
            'institucion_id'          => $institucionId,
        ]);

        $this->ejemplar = MaterialEjemplar::forceCreate([
            'material_id'    => $this->material->id,
            'institucion_id' => $institucionId,
            'codigo_ejemplar' => 'RES-001-E1',
            'estado'         => 'disponible',
        ]);
    }

    public function test_crear_reserva_reserves_ejemplar_and_increments_counter(): void
    {
        $reserva = $this->service->crearReserva($this->socio->id, $this->material->id);

        $this->assertInstanceOf(Reserva::class, $reserva);
        $this->assertEquals('pendiente', $reserva->estado);
        $this->assertEquals('reservado', $this->ejemplar->fresh()->estado);
        $this->assertEquals(1, $this->material->fresh()->disponibilidad_reservada);
    }

    public function test_crear_reserva_fails_if_no_ejemplar_available(): void
    {
        $this->ejemplar->update(['estado' => 'prestado']);

        $this->expectException(\Exception::class);

        $this->service->crearReserva($this->socio->id, $this->material->id);
    }

    public function test_crear_reserva_fails_if_duplicate_active_reserva(): void
    {
        $this->service->crearReserva($this->socio->id, $this->material->id);

        // Need a second disponible ejemplar to trigger the duplicate check (not stock check)
        MaterialEjemplar::forceCreate([
            'material_id'    => $this->material->id,
            'institucion_id' => $this->bibliotecario->institucion_id,
            'codigo_ejemplar' => 'RES-001-E2',
            'estado'         => 'disponible',
        ]);

        $this->expectException(ValidationException::class);

        $this->service->crearReserva($this->socio->id, $this->material->id);
    }

    public function test_aprobar_reserva_creates_prestamo_and_marks_aprobada(): void
    {
        $reserva = Reserva::forceCreate([
            'socio_id'         => $this->socio->id,
            'material_id'      => $this->material->id,
            'ejemplar_id'      => $this->ejemplar->id,
            'estado'           => 'pendiente',
            'fecha_reserva'    => now(),
            'fecha_vencimiento' => now()->addDays(2),
            'institucion_id'   => $this->bibliotecario->institucion_id,
        ]);
        $this->ejemplar->update(['estado' => 'reservado']);

        $prestamo = $this->service->aprobarReserva($reserva, 14);

        $this->assertInstanceOf(Prestamo::class, $prestamo);
        $this->assertEquals('activo', $prestamo->estado);
        $this->assertEquals('aprobada', $reserva->fresh()->estado);
        $this->assertEquals('prestado', $this->ejemplar->fresh()->estado);
    }

    public function test_aprobar_reserva_fails_if_not_pendiente(): void
    {
        $reserva = Reserva::forceCreate([
            'socio_id'         => $this->socio->id,
            'material_id'      => $this->material->id,
            'estado'           => 'aprobada',
            'fecha_reserva'    => now(),
            'fecha_vencimiento' => now()->addDays(2),
            'institucion_id'   => $this->bibliotecario->institucion_id,
        ]);

        $this->expectException(ValidationException::class);

        $this->service->aprobarReserva($reserva, 14);
    }

    public function test_rechazar_reserva_frees_ejemplar_and_decrements_counter(): void
    {
        DB::table('materiales')->where('id', $this->material->id)->update(['disponibilidad_reservada' => 1]);
        $this->ejemplar->update(['estado' => 'reservado']);

        $reserva = Reserva::forceCreate([
            'socio_id'         => $this->socio->id,
            'material_id'      => $this->material->id,
            'ejemplar_id'      => $this->ejemplar->id,
            'estado'           => 'pendiente',
            'fecha_reserva'    => now(),
            'fecha_vencimiento' => now()->addDays(2),
            'institucion_id'   => $this->bibliotecario->institucion_id,
        ]);

        $this->service->rechazarReserva($reserva);

        $this->assertEquals('rechazada', $reserva->fresh()->estado);
        $this->assertEquals('disponible', $this->ejemplar->fresh()->estado);
        $this->assertEquals(0, $this->material->fresh()->disponibilidad_reservada);
    }

    public function test_expirar_reservas_vencidas_expires_old_reservas(): void
    {
        DB::table('materiales')->where('id', $this->material->id)->update(['disponibilidad_reservada' => 1]);
        $this->ejemplar->update(['estado' => 'reservado']);

        Reserva::forceCreate([
            'socio_id'         => $this->socio->id,
            'material_id'      => $this->material->id,
            'ejemplar_id'      => $this->ejemplar->id,
            'estado'           => 'pendiente',
            'fecha_reserva'    => now()->subDays(5),
            'fecha_vencimiento' => now()->subDay(),
            'institucion_id'   => $this->bibliotecario->institucion_id,
        ]);

        $count = $this->service->expirarReservasVencidas();

        $this->assertEquals(1, $count);
        $this->assertEquals('disponible', $this->ejemplar->fresh()->estado);
        $this->assertEquals(0, $this->material->fresh()->disponibilidad_reservada);
    }
}
