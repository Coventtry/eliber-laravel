<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\MaterialEjemplar;
use App\Models\Socio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Support\TestHelpers;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase, TestHelpers;

    public function test_bibliotecario_can_log_in_with_usuario_and_password(): void
    {
        $institucion = $this->createInstitucion();

        User::create([
            'name' => 'Admin Test',
            'nombre' => 'Admin Test',
            'email' => 'admin@test.com',
            'usuario' => 'admin-test',
            'password' => 'secret123',
            'picture' => '',
            'institucion_id' => $institucion->id,
            'activo' => true,
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

        // El rol admin es redirigido a su propio dashboard
        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($user)
            ->get('/admin/dashboard')
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

        for ($i = 1; $i <= 3; $i++) {
            MaterialEjemplar::forceCreate([
                'material_id' => $material->id,
                'institucion_id' => $user->institucion_id,
                'codigo_ejemplar' => '200-001-E'.str_pad($i, 2, '0', STR_PAD_LEFT),
                'estado' => 'disponible',
            ]);
        }

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

        for ($i = 1; $i <= 2; $i++) {
            MaterialEjemplar::forceCreate([
                'material_id' => $material->id,
                'institucion_id' => $user->institucion_id,
                'codigo_ejemplar' => '200-002-E'.str_pad($i, 2, '0', STR_PAD_LEFT),
                'estado' => 'disponible',
            ]);
        }

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
            'material_id' => $material->id,
            'socio_id' => $socio->id,
            'estado' => 'pendiente',
            'fecha_reserva' => now(),
            'fecha_vencimiento' => now()->addDays(2),
            'institucion_id' => $user->institucion_id,
            'created_at' => now(),
            'updated_at' => now(),
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
        // disponibilidad se gestiona via ExemplarService (no se decrementa en reserva, solo en prestamo real)
        $this->assertDatabaseHas('materiales', [
            'id' => $material->id,
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
            'material_id' => $material->id,
            'socio_id' => $socio->id,
            'estado' => 'pendiente',
            'fecha_reserva' => now(),
            'fecha_vencimiento' => now()->addDays(2),
            'institucion_id' => $user->institucion_id,
            'created_at' => now(),
            'updated_at' => now(),
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

    public function test_authenticated_bibliotecario_can_see_solicitudes_page(): void
    {
        $user = $this->createBibliotecario();

        $response = $this->actingAs($user)->get('/prestamos/solicitudes');

        $response->assertOk();
    }

    public function test_authenticated_bibliotecario_can_approve_solicitud(): void
    {
        $user = $this->createBibliotecario();
        $area = $this->createArea($user->institucion_id);
        $socio = $this->createSocio($user->institucion_id);
        $material = Material::forceCreate([
            'titulo' => 'Libro Solicitud',
            'autor' => 'Autor Test',
            'anio_publicacion' => 2021,
            'area_id' => $area->id,
            'categoria' => 'LIBRO',
            'codigo' => '200-007',
            'disponibilidad' => 2,
            'disponibilidad_reservada' => 1,
            'editorial' => 'Editorial Test',
            'clasificacion_fisica' => 'MAT-A-(E)1-7',
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

        $response = $this->actingAs($user)->patch("/prestamos/solicitudes/{$reservaId}/aprobar");

        $response->assertRedirect(route('prestamos.solicitudes'));
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
    }

    public function test_authenticated_bibliotecario_can_reject_solicitud(): void
    {
        $user = $this->createBibliotecario();
        $area = $this->createArea($user->institucion_id);
        $socio = $this->createSocio($user->institucion_id);
        $material = Material::forceCreate([
            'titulo' => 'Libro Rechazar Solicitud',
            'autor' => 'Autor Test',
            'anio_publicacion' => 2021,
            'area_id' => $area->id,
            'categoria' => 'LIBRO',
            'codigo' => '200-008',
            'disponibilidad' => 2,
            'disponibilidad_reservada' => 1,
            'editorial' => 'Editorial Test',
            'clasificacion_fisica' => 'MAT-A-(E)1-8',
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

        $response = $this->actingAs($user)->patch("/prestamos/solicitudes/{$reservaId}/rechazar", [
            'motivo' => 'Material reservado para otro socio',
        ]);

        $response->assertRedirect(route('prestamos.solicitudes'));
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

    public function test_authenticated_bibliotecario_can_manage_multas(): void
    {
        $user = $this->createBibliotecario();
        $socio = $this->createSocio($user->institucion_id);

        $response = $this->actingAs($user)->post('/multas', [
            'socio_id' => $socio->id,
            'monto' => 500.00,
            'motivo' => 'Devolución tardía',
        ]);

        $response->assertRedirect(route('multas.index'));
        $this->assertDatabaseHas('multas', [
            'socio_id' => $socio->id,
            'monto' => 500.00,
            'motivo' => 'Devolución tardía',
            'pagada' => false,
        ]);

        $multaId = \DB::table('multas')->where('socio_id', $socio->id)->value('id');

        $response = $this->actingAs($user)->patch("/multas/{$multaId}/pagar");
        $response->assertRedirect(route('multas.index'));
        $this->assertDatabaseHas('multas', ['id' => $multaId, 'pagada' => true]);
    }

    public function test_authenticated_bibliotecario_can_export_multas_csv(): void
    {
        $user = $this->createBibliotecario();

        $response = $this->actingAs($user)->get('/exportar/multas/csv');
        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }

    public function test_authenticated_bibliotecario_can_export_multas_pdf(): void
    {
        $user = $this->createBibliotecario();

        $response = $this->actingAs($user)->get('/exportar/multas/pdf');
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_authenticated_user_can_view_and_update_perfil(): void
    {
        $user = $this->createBibliotecario();

        $this->actingAs($user)->get('/perfil')->assertOk();

        $response = $this->actingAs($user)->put('/perfil', [
            'email' => 'nuevo@test.com',
        ]);

        $response->assertRedirect(route('perfil.edit'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'nuevo@test.com',
        ]);
    }

    public function test_alumno_can_access_alumno_pages(): void
    {
        $alumno = $this->createAlumno();

        $this->actingAs($alumno)->get('/alumno/dashboard')->assertOk();
        $this->actingAs($alumno)->get('/alumno/catalogo')->assertOk();
        $this->actingAs($alumno)->get('/alumno/mis-reservas')->assertOk();
        $this->actingAs($alumno)->get('/alumno/mis-prestamos')->assertOk();
    }

    public function test_non_alumno_cannot_access_alumno_pages(): void
    {
        $bibliotecario = $this->createBibliotecario();

        // El middleware role:alumno redirige si no tiene el rol
        $this->actingAs($bibliotecario)->get('/alumno/dashboard')->assertStatus(403);
    }

    public function test_non_admin_cannot_access_admin_pages(): void
    {
        $alumno = $this->createAlumno();

        $this->actingAs($alumno)->get('/admin/dashboard')->assertStatus(403);
    }

    public function test_cannot_baja_prestado_ejemplar(): void
    {
        $user = $this->createBibliotecario();
        $area = $this->createArea($user->institucion_id);
        $socio = $this->createSocio($user->institucion_id);
        $material = Material::forceCreate([
            'titulo' => 'Libro Baja Test',
            'autor' => 'Autor Test',
            'anio_publicacion' => 2020,
            'area_id' => $area->id,
            'categoria' => 'LIBRO',
            'codigo' => '200-007',
            'disponibilidad' => 1,
            'disponibilidad_reservada' => 0,
            'editorial' => 'Editorial Test',
            'clasificacion_fisica' => 'MAT-A-(E)1-7',
            'institucion_id' => $user->institucion_id,
        ]);

        $ejemplar = MaterialEjemplar::forceCreate([
            'material_id' => $material->id,
            'institucion_id' => $user->institucion_id,
            'codigo_ejemplar' => '200-007-E01',
            'estado' => 'prestado',
        ]);

        $prestamoId = \DB::table('prestamos')->insertGetId([
            'socio_id' => $socio->id,
            'material_id' => $material->id,
            'ejemplar_id' => $ejemplar->id,
            'fecha_prestamo' => now()->subDays(2)->toDateString(),
            'fecha_devolucion' => now()->addDays(5)->toDateString(),
            'estado' => 'activo',
            'cantidad' => 1,
            'institucion_id' => $user->institucion_id,
        ]);

        $response = $this->actingAs($user)->patchJson(
            "/materiales/{$material->id}/ejemplares/{$ejemplar->id}/baja"
        );

        $response->assertStatus(422);
        $this->assertDatabaseHas('material_ejemplares', [
            'id' => $ejemplar->id,
            'estado' => 'prestado',
        ]);
    }

    public function test_ejemplar_baja_removes_from_available(): void
    {
        $user = $this->createBibliotecario();
        $area = $this->createArea($user->institucion_id);
        $material = Material::forceCreate([
            'titulo' => 'Libro Disponible Test',
            'autor' => 'Autor Test',
            'anio_publicacion' => 2021,
            'area_id' => $area->id,
            'categoria' => 'LIBRO',
            'codigo' => '200-008',
            'disponibilidad' => 1,
            'disponibilidad_reservada' => 0,
            'editorial' => 'Editorial Test',
            'clasificacion_fisica' => 'MAT-A-(E)1-8',
            'institucion_id' => $user->institucion_id,
        ]);

        $ejemplar = MaterialEjemplar::forceCreate([
            'material_id' => $material->id,
            'institucion_id' => $user->institucion_id,
            'codigo_ejemplar' => '200-008-E01',
            'estado' => 'disponible',
        ]);

        $response = $this->actingAs($user)->patchJson(
            "/materiales/{$material->id}/ejemplares/{$ejemplar->id}/baja"
        );

        $response->assertOk();
        $this->assertDatabaseHas('material_ejemplares', [
            'id' => $ejemplar->id,
            'estado' => 'baja',
        ]);
        $this->assertDatabaseHas('materiales', [
            'id' => $material->id,
            'disponibilidad' => 0,
        ]);
    }

    public function test_reserva_approval_transitions_ejemplar_to_prestado(): void
    {
        $user = $this->createBibliotecario();
        $area = $this->createArea($user->institucion_id);
        $socio = $this->createSocio($user->institucion_id);
        $material = Material::forceCreate([
            'titulo' => 'Libro Reserva Aprob Test',
            'autor' => 'Autor Test',
            'anio_publicacion' => 2022,
            'area_id' => $area->id,
            'categoria' => 'LIBRO',
            'codigo' => '200-009',
            'disponibilidad' => 1,
            'disponibilidad_reservada' => 1,
            'editorial' => 'Editorial Test',
            'clasificacion_fisica' => 'MAT-A-(E)1-9',
            'institucion_id' => $user->institucion_id,
        ]);

        $ejemplar = MaterialEjemplar::forceCreate([
            'material_id' => $material->id,
            'institucion_id' => $user->institucion_id,
            'codigo_ejemplar' => '200-009-E01',
            'estado' => 'reservado',
        ]);

        $reservaId = \DB::table('reservas')->insertGetId([
            'material_id' => $material->id,
            'ejemplar_id' => $ejemplar->id,
            'socio_id' => $socio->id,
            'estado' => 'pendiente',
            'fecha_reserva' => now(),
            'fecha_vencimiento' => now()->addDays(2),
            'institucion_id' => $user->institucion_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user, 'sanctum')->patchJson(
            "/api/v1/reservas/{$reservaId}/aprobar",
            ['dias' => 10]
        );

        $response->assertOk();
        $this->assertDatabaseHas('material_ejemplares', [
            'id' => $ejemplar->id,
            'estado' => 'prestado',
        ]);
        $this->assertDatabaseHas('prestamos', [
            'socio_id' => $socio->id,
            'material_id' => $material->id,
            'ejemplar_id' => $ejemplar->id,
            'estado' => 'activo',
        ]);
        $this->assertDatabaseHas('reservas', [
            'id' => $reservaId,
            'estado' => 'aprobada',
        ]);
        $this->assertDatabaseHas('materiales', [
            'id' => $material->id,
            'disponibilidad_reservada' => 0,
        ]);
    }

}
