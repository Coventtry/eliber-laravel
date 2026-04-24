<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Institucion;
use App\Models\Material;
use App\Models\Socio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_bibliotecario_can_log_in_with_usuario_and_password(): void
    {
        $institucion = $this->createInstitucion();

        User::create([
            'name'           => 'Admin Test',
            'nombre'         => 'Admin Test',
            'email'          => 'admin@test.com',
            'usuario'        => 'admin-test',
            'password'       => 'secret123',
            'picture'        => '',
            'institucion_id' => $institucion->id,
            'activo'         => true,
        ]);

        $response = $this->post('/login', [
            'usuario' => 'admin-test',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_authenticated_bibliotecario_can_open_main_pages(): void
    {
        $user = $this->createBibliotecario();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk();

        $this->actingAs($user)
            ->get('/socios')
            ->assertOk();

        $this->actingAs($user)
            ->get('/materiales')
            ->assertOk();

        $this->actingAs($user)
            ->get('/prestamos')
            ->assertOk();
    }

    public function test_authenticated_bibliotecario_can_create_a_socio(): void
    {
        $user = $this->createBibliotecario();

        $response = $this->actingAs($user)->post('/socios', [
            'nombre' => 'Ana',
            'apellido' => 'Gomez',
            'email' => 'ana.gomez@test.com',
            'telefono' => '2454123456',
            'direccion' => 'Calle 123',
            'anio' => 5,
            'division' => 2,
        ]);

        $response->assertRedirect(route('socios.index'));
        $this->assertDatabaseHas('socios', [
            'nombre' => 'Ana',
            'apellido' => 'Gomez',
            'email' => 'ana.gomez@test.com',
        ]);
    }

    public function test_authenticated_bibliotecario_can_create_a_material(): void
    {
        Storage::fake('public');

        $user = $this->createBibliotecario();
        $area = $this->createArea($user->institucion_id);

        $response = $this->actingAs($user)->post('/materiales', [
            'titulo' => 'El Principito',
            'autor' => 'Antoine de Saint-Exupery',
            'anio_publicacion' => 1943,
            'area_id' => $area->id,
            'categoria' => 'LIBRO',
            'disponibilidad' => 3,
            'editorial' => 'Salamandra',
        ]);

        $response->assertRedirect(route('materiales.index'));
        $this->assertDatabaseHas('materiales', [
            'titulo' => 'El Principito',
            'area_id' => $area->id,
            'disponibilidad' => 3,
        ]);
    }

    public function test_authenticated_bibliotecario_can_register_a_prestamo(): void
    {
        $user = $this->createBibliotecario();
        $area = $this->createArea($user->institucion_id);
        $socio = $this->createSocio($user->institucion_id);
        $material = Material::forceCreate([
            'titulo' => 'Matematica 6',
            'autor' => 'Equipo Docente',
            'anio_publicacion' => 2022,
            'area_id' => $area->id,
            'categoria' => 'LIBRO',
            'codigo' => '200-001',
            'disponibilidad' => 3,
            'disponibilidad_reservada' => 0,
            'editorial' => 'Puerto de Palos',
            'clasificacion_fisica' => 'MAT-A-(E)1-1',
            'institucion_id' => $user->institucion_id,
        ]);

        $response = $this->actingAs($user)->post('/prestamos', [
            'socio_id' => $socio->id,
            'material_id' => $material->id,
            'cantidad' => 1,
            'fecha_devolucion' => now()->addDays(7)->toDateString(),
        ]);

        $response->assertRedirect(route('prestamos.index'));
        $this->assertDatabaseHas('prestamos', [
            'socio_id' => $socio->id,
            'material_id' => $material->id,
            'estado' => 'activo',
            'cantidad' => 1,
        ]);
        $this->assertDatabaseHas('materiales', [
            'id' => $material->id,
            'disponibilidad' => 2,
        ]);
    }

    public function test_authenticated_bibliotecario_can_create_a_noticia(): void
    {
        $user = $this->createBibliotecario();

        $response = $this->actingAs($user)->post('/noticias', [
            'titulo' => 'Nueva noticia',
            'descripcion' => 'Contenido breve de prueba',
        ]);

        $response->assertRedirect(route('noticias.index'));
        $this->assertDatabaseHas('noticias', [
            'titulo' => 'Nueva noticia',
            'descripcion' => 'Contenido breve de prueba',
            'institucion_id' => $user->institucion_id,
        ]);
    }

    public function test_authenticated_bibliotecario_can_create_an_anotacion(): void
    {
        $user = $this->createBibliotecario();

        $response = $this->actingAs($user)->post('/anotaciones', [
            'anotacion' => 'Recordatorio de prueba',
        ]);

        $response->assertRedirect(route('anotaciones.index'));
        $this->assertDatabaseHas('anotaciones', [
            'anotacion' => 'Recordatorio de prueba',
            'institucion_id' => $user->institucion_id,
        ]);
    }

    public function test_authenticated_api_user_can_create_a_reserva(): void
    {
        $user = $this->createBibliotecario();
        $area = $this->createArea($user->institucion_id);
        $socio = $this->createSocio($user->institucion_id);
        $material = Material::forceCreate([
            'titulo' => 'Libro Reservable',
            'autor' => 'Autor Test',
            'anio_publicacion' => 2020,
            'area_id' => $area->id,
            'categoria' => 'LIBRO',
            'codigo' => '200-002',
            'disponibilidad' => 2,
            'disponibilidad_reservada' => 0,
            'editorial' => 'Editorial Test',
            'clasificacion_fisica' => 'MAT-A-(E)1-2',
            'institucion_id' => $user->institucion_id,
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/reservas', [
            'material_id' => $material->id,
            'socio_id' => $socio->id,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('reservas', [
            'material_id' => $material->id,
            'socio_id' => $socio->id,
            'estado' => 'pendiente',
        ]);
        $this->assertDatabaseHas('materiales', [
            'id' => $material->id,
            'disponibilidad_reservada' => 1,
        ]);
    }

    public function test_authenticated_api_user_can_approve_a_reserva_and_create_prestamo(): void
    {
        $user = $this->createBibliotecario();
        $area = $this->createArea($user->institucion_id);
        $socio = $this->createSocio($user->institucion_id);
        $material = Material::forceCreate([
            'titulo' => 'Libro Aprobable',
            'autor' => 'Autor Test',
            'anio_publicacion' => 2021,
            'area_id' => $area->id,
            'categoria' => 'LIBRO',
            'codigo' => '200-003',
            'disponibilidad' => 2,
            'disponibilidad_reservada' => 1,
            'editorial' => 'Editorial Test',
            'clasificacion_fisica' => 'MAT-A-(E)1-3',
            'institucion_id' => $user->institucion_id,
        ]);

        $reservaId = \DB::table('reservas')->insertGetId([
            'material_id'    => $material->id,
            'socio_id'       => $socio->id,
            'estado'         => 'pendiente',
            'fecha_reserva'  => now(),
            'fecha_vencimiento' => now()->addDays(2),
            'institucion_id' => $user->institucion_id,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $response = $this->actingAs($user, 'sanctum')->patchJson("/api/v1/reservas/{$reservaId}/aprobar", [
            'dias' => 10,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('reservas', [
            'id' => $reservaId,
            'estado' => 'aprobada',
        ]);
        $this->assertDatabaseHas('prestamos', [
            'socio_id' => $socio->id,
            'material_id' => $material->id,
            'estado' => 'activo',
            'institucion_id' => $user->institucion_id,
        ]);
        $this->assertDatabaseHas('materiales', [
            'id' => $material->id,
            'disponibilidad' => 1,
            'disponibilidad_reservada' => 0,
        ]);
    }

    public function test_authenticated_api_user_can_reject_a_reserva(): void
    {
        $user = $this->createBibliotecario();
        $area = $this->createArea($user->institucion_id);
        $socio = $this->createSocio($user->institucion_id);
        $material = Material::forceCreate([
            'titulo' => 'Libro Rechazable',
            'autor' => 'Autor Test',
            'anio_publicacion' => 2021,
            'area_id' => $area->id,
            'categoria' => 'LIBRO',
            'codigo' => '200-004',
            'disponibilidad' => 2,
            'disponibilidad_reservada' => 1,
            'editorial' => 'Editorial Test',
            'clasificacion_fisica' => 'MAT-A-(E)1-4',
            'institucion_id' => $user->institucion_id,
        ]);

        $reservaId = \DB::table('reservas')->insertGetId([
            'material_id'    => $material->id,
            'socio_id'       => $socio->id,
            'estado'         => 'pendiente',
            'fecha_reserva'  => now(),
            'fecha_vencimiento' => now()->addDays(2),
            'institucion_id' => $user->institucion_id,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $response = $this->actingAs($user, 'sanctum')->patchJson("/api/v1/reservas/{$reservaId}/rechazar", [
            'motivo' => 'No disponible para esta fecha',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('reservas', [
            'id' => $reservaId,
            'estado' => 'rechazada',
        ]);
        $this->assertDatabaseHas('materiales', [
            'id' => $material->id,
            'disponibilidad' => 2,
            'disponibilidad_reservada' => 0,
        ]);
    }

    public function test_authenticated_bibliotecario_can_devolver_a_prestamo(): void
    {
        $user = $this->createBibliotecario();
        $area = $this->createArea($user->institucion_id);
        $socio = $this->createSocio($user->institucion_id);
        $material = Material::forceCreate([
            'titulo' => 'Libro Devolucion',
            'autor' => 'Autor Test',
            'anio_publicacion' => 2021,
            'area_id' => $area->id,
            'categoria' => 'LIBRO',
            'codigo' => '200-005',
            'disponibilidad' => 1,
            'disponibilidad_reservada' => 0,
            'editorial' => 'Editorial Test',
            'clasificacion_fisica' => 'MAT-A-(E)1-5',
            'institucion_id' => $user->institucion_id,
        ]);

        $prestamoId = \DB::table('prestamos')->insertGetId([
            'socio_id' => $socio->id,
            'material_id' => $material->id,
            'fecha_prestamo' => now()->subDays(2)->toDateString(),
            'fecha_devolucion' => now()->addDays(5)->toDateString(),
            'estado' => 'activo',
            'cantidad' => 1,
            'institucion_id' => $user->institucion_id,
        ]);

        $response = $this->actingAs($user)->patch("/prestamos/{$prestamoId}/devolver");

        $response->assertRedirect(route('prestamos.index'));
        $this->assertDatabaseHas('prestamos', [
            'id' => $prestamoId,
            'estado' => 'devuelto',
        ]);
        $this->assertDatabaseHas('materiales', [
            'id' => $material->id,
            'disponibilidad' => 2,
        ]);
    }

    public function test_authenticated_bibliotecario_can_extender_a_prestamo(): void
    {
        $user = $this->createBibliotecario();
        $area = $this->createArea($user->institucion_id);
        $socio = $this->createSocio($user->institucion_id);
        $material = Material::forceCreate([
            'titulo' => 'Libro Extension',
            'autor' => 'Autor Test',
            'anio_publicacion' => 2021,
            'area_id' => $area->id,
            'categoria' => 'LIBRO',
            'codigo' => '200-006',
            'disponibilidad' => 1,
            'disponibilidad_reservada' => 0,
            'editorial' => 'Editorial Test',
            'clasificacion_fisica' => 'MAT-A-(E)1-6',
            'institucion_id' => $user->institucion_id,
        ]);

        $fechaOriginal = now()->addDays(5)->toDateString();

        $prestamoId = \DB::table('prestamos')->insertGetId([
            'socio_id' => $socio->id,
            'material_id' => $material->id,
            'fecha_prestamo' => now()->subDays(2)->toDateString(),
            'fecha_devolucion' => $fechaOriginal,
            'estado' => 'activo',
            'cantidad' => 1,
            'institucion_id' => $user->institucion_id,
        ]);

        $response = $this->actingAs($user)->patch("/prestamos/{$prestamoId}/extender", [
            'dias' => 3,
        ]);

        $response->assertRedirect(route('prestamos.index'));
        $fechaDevolucion = (string) \DB::table('prestamos')->where('id', $prestamoId)->value('fecha_devolucion');

        $this->assertSame(now()->addDays(8)->toDateString(), substr($fechaDevolucion, 0, 10));
    }

    private function createInstitucion(): Institucion
    {
        return Institucion::create([
            'nombre' => 'Institucion Test',
            'slug' => 'institucion-test',
            'estado' => 'activa',
        ]);
    }

    private function createBibliotecario(): User
    {
        $institucion = $this->createInstitucion();

        $user = User::create([
            'name'           => 'Bibliotecario Test',
            'nombre'         => 'Bibliotecario Test',
            'email'          => 'bibliotecario@test.com',
            'usuario'        => 'bibliotecario-test',
            'password'       => 'secret123',
            'picture'        => '',
            'institucion_id' => $institucion->id,
            'activo'         => true,
        ]);

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        foreach (['gestionar-socios','gestionar-materiales','gestionar-prestamos','gestionar-areas','gestionar-noticias','gestionar-anotaciones','gestionar-usuarios','ver-reportes'] as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        $adminRole->givePermissionTo(Permission::all());
        $user->assignRole($adminRole);

        return $user;
    }

    private function createArea(int $institucionId): Area
    {
        return Area::forceCreate([
            'codigo_dewey' => '200',
            'nombre' => 'Matematica',
            'Abreviado' => 'MAT',
            'institucion_id' => $institucionId,
        ]);
    }

    private function createSocio(int $institucionId): Socio
    {
        return Socio::forceCreate([
            'nombre' => 'Martin',
            'apellido' => 'Perez',
            'telefono' => '2454987654',
            'direccion' => 'Calle 456',
            'email' => 'martin.perez@test.com',
            'anio' => 6,
            'division' => 1,
            'activo' => 1,
            'institucion_id' => $institucionId,
        ]);
    }
}
