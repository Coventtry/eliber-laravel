<?php

namespace Tests\Unit\Services;

use App\Models\Area;
use App\Models\Configuracion;
use App\Models\Institucion;
use App\Models\Material;
use App\Models\Prestamo;
use App\Models\Socio;
use App\Models\User;
use App\Notifications\PrestamoProximoVencer;
use App\Notifications\PrestamoVencido;
use App\Notifications\ReservaAprobada;
use App\Services\MultaService;
use App\Services\PrestamoService;
use App\Services\ReservaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificacionTest extends TestCase
{
    use RefreshDatabase;

    private PrestamoService $prestamoService;
    private ReservaService $reservaService;
    private Socio $socio;
    private User $user;
    private int $areaId;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $institucion = Institucion::create(['nombre' => 'Test', 'slug' => 'test', 'estado' => 'activa']);
        $this->user = User::create([
            'name' => 'Test', 'nombre' => 'Test', 'email' => 't@t.com',
            'usuario' => 'test', 'password' => 'x', 'picture' => '',
            'institucion_id' => $institucion->id, 'activo' => true,
        ]);
        $this->actingAs($this->user);

        Configuracion::set($institucion->id, 'dias_prestamo', 14);
        Configuracion::set($institucion->id, 'monto_multa_diaria', 100);

        $area = Area::forceCreate([
            'codigo_dewey' => '100', 'nombre' => 'Filo',
            'Abreviado' => 'FIL', 'institucion_id' => $institucion->id,
        ]);
        $this->areaId = $area->id;

        $this->socio = Socio::forceCreate([
            'nombre' => 'Juan', 'apellido' => 'Lopez',
            'email' => 'juan@test.com', 'telefono' => '1234567890',
            'anio' => 5, 'division' => 1, 'activo' => 1,
            'institucion_id' => $institucion->id,
        ]);

        $this->user->update(['socio_id' => $this->socio->id]);

        $this->prestamoService = $this->app->make(PrestamoService::class);
        $this->reservaService = $this->app->make(ReservaService::class);
    }

    public function test_envia_notificacion_vencido_al_marcar_atrasados(): void
    {
        $material = Material::forceCreate([
            'titulo' => 'Libro', 'autor' => 'A', 'anio_publicacion' => 2020,
            'area_id' => $this->areaId, 'categoria' => 'LIBRO',
            'codigo' => '100-001', 'disponibilidad' => 3, 'disponibilidad_reservada' => 0,
            'editorial' => 'Ed', 'clasificacion_fisica' => 'FIL-A-(E)1-1',
            'institucion_id' => $this->socio->institucion_id,
        ]);
        $material->decrement('disponibilidad', 1);
        Prestamo::forceCreate([
            'socio_id' => $this->socio->id,
            'material_id' => $material->id,
            'fecha_prestamo' => now()->subDays(5)->toDateString(),
            'fecha_devolucion' => now()->subDay()->toDateString(),
            'estado' => 'activo',
            'cantidad' => 1,
            'institucion_id' => $this->socio->institucion_id,
        ]);

        $this->prestamoService->marcarAtrasados();

        Notification::assertSentTo($this->user, PrestamoVencido::class);
    }

    public function test_envia_notificacion_proximo_vencer(): void
    {
        $material = Material::forceCreate([
            'titulo' => 'Libro', 'autor' => 'A', 'anio_publicacion' => 2020,
            'area_id' => $this->areaId, 'categoria' => 'LIBRO',
            'codigo' => '100-002', 'disponibilidad' => 3, 'disponibilidad_reservada' => 0,
            'editorial' => 'Ed', 'clasificacion_fisica' => 'FIL-A-(E)1-1',
            'institucion_id' => $this->socio->institucion_id,
        ]);
        $material->decrement('disponibilidad', 1);
        Prestamo::forceCreate([
            'socio_id' => $this->socio->id,
            'material_id' => $material->id,
            'fecha_prestamo' => now()->toDateString(),
            'fecha_devolucion' => now()->addDays(2)->toDateString(),
            'estado' => 'activo',
            'cantidad' => 1,
            'institucion_id' => $this->socio->institucion_id,
        ]);

        $this->prestamoService->obtenerVencimientosProximos(4);

        Notification::assertSentTo($this->user, PrestamoProximoVencer::class);
    }

    public function test_envia_notificacion_reserva_aprobada(): void
    {
        $material = Material::forceCreate([
            'titulo' => 'Libro Test', 'autor' => 'A', 'anio_publicacion' => 2020,
            'area_id' => $this->areaId, 'categoria' => 'LIBRO',
            'codigo' => '100-003', 'disponibilidad' => 3, 'disponibilidad_reservada' => 0,
            'editorial' => 'Ed', 'clasificacion_fisica' => 'FIL-A-(E)1-1',
            'institucion_id' => $this->socio->institucion_id,
        ]);

        $reserva = $this->reservaService->crearReserva($this->socio->id, $material->id);

        $this->reservaService->aprobarReserva($reserva);

        Notification::assertSentTo($this->user, ReservaAprobada::class);
    }

    public function test_no_envia_notificacion_sin_usuario_vinculado(): void
    {
        $this->user->update(['socio_id' => null]);

        $material = Material::forceCreate([
            'titulo' => 'Libro', 'autor' => 'A', 'anio_publicacion' => 2020,
            'area_id' => $this->areaId, 'categoria' => 'LIBRO',
            'codigo' => '100-004', 'disponibilidad' => 3, 'disponibilidad_reservada' => 0,
            'editorial' => 'Ed', 'clasificacion_fisica' => 'FIL-A-(E)1-1',
            'institucion_id' => $this->socio->institucion_id,
        ]);
        $material->decrement('disponibilidad', 1);
        Prestamo::forceCreate([
            'socio_id' => $this->socio->id,
            'material_id' => $material->id,
            'fecha_prestamo' => now()->subDays(5)->toDateString(),
            'fecha_devolucion' => now()->subDay()->toDateString(),
            'estado' => 'activo',
            'cantidad' => 1,
            'institucion_id' => $this->socio->institucion_id,
        ]);

        $this->prestamoService->marcarAtrasados();

        Notification::assertNothingSent();
    }
}
