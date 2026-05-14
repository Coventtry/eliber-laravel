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

class ApiTest extends TestCase
{
    use RefreshDatabase, TestHelpers;

    // ─── Socios API ────────────────────────────────────────────────────────

    public function test_api_lista_socios(): void
    {
        $user = $this->createBibliotecario();
        $this->createSocio($user->institucion_id);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/socios')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_api_crea_socio(): void
    {
        $user = $this->createBibliotecario();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/socios', [
                'nombre' => 'Lucia',
                'apellido' => 'Mendez',
                'email' => 'lucia@test.com',
                'telefono' => '2454111111',
                'anio' => 4,
                'division' => 3,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('socios', ['email' => 'lucia@test.com']);
    }

    public function test_api_muestra_socio(): void
    {
        $user = $this->createBibliotecario();
        $socio = $this->createSocio($user->institucion_id);

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/socios/{$socio->id}")
            ->assertOk()
            ->assertJsonPath('data.nombre', 'Martin');
    }

    public function test_api_actualiza_socio(): void
    {
        $user = $this->createBibliotecario();
        $socio = $this->createSocio($user->institucion_id);

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/socios/{$socio->id}", [
                'nombre' => 'Martin Actualizado',
                'apellido' => 'Perez',
            ])
            ->assertOk();

        $this->assertDatabaseHas('socios', ['id' => $socio->id, 'nombre' => 'Martin Actualizado']);
    }

    public function test_api_da_de_baja_socio(): void
    {
        $user = $this->createBibliotecario();
        $socio = $this->createSocio($user->institucion_id);

        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/socios/{$socio->id}/baja", [
                'observaciones' => 'Se retira de la institucion',
            ])
            ->assertOk();

        $this->assertDatabaseHas('socios', ['id' => $socio->id, 'activo' => false]);
    }

    public function test_api_reactiva_socio(): void
    {
        $user = $this->createBibliotecario();
        $socio = $this->createSocio($user->institucion_id);
        $socio->update(['activo' => false]);

        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/socios/{$socio->id}/reactivar")
            ->assertOk();

        $this->assertDatabaseHas('socios', ['id' => $socio->id, 'activo' => true]);
    }

    public function test_api_elimina_socio(): void
    {
        $user = $this->createBibliotecario();
        $socio = $this->createSocio($user->institucion_id);

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/socios/{$socio->id}")
            ->assertOk();

        $this->assertDatabaseMissing('socios', ['id' => $socio->id]);
    }

    // ─── Materiales API ────────────────────────────────────────────────────

    public function test_api_lista_materiales_publico(): void
    {
        $user = $this->createBibliotecario();
        $area = $this->createArea($user->institucion_id);
        Material::forceCreate([
            'titulo' => 'Libro Publico',
            'autor' => 'Autor',
            'anio_publicacion' => 2020,
            'area_id' => $area->id,
            'categoria' => 'LIBRO',
            'codigo' => '300-001',
            'disponibilidad' => 1,
            'disponibilidad_reservada' => 0,
            'editorial' => 'Edit',
            'clasificacion_fisica' => 'A1',
            'institucion_id' => $user->institucion_id,
        ]);

        $this->getJson('/api/v1/materiales')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_api_crea_material(): void
    {
        $user = $this->createBibliotecario();
        $area = $this->createArea($user->institucion_id);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/materiales', [
                'titulo' => 'Nuevo Libro',
                'autor' => 'Autor Test',
                'anio_publicacion' => 2023,
                'area_id' => $area->id,
                'categoria' => 'LIBRO',
                'disponibilidad' => 5,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('materiales', ['titulo' => 'Nuevo Libro']);
    }

    public function test_api_actualiza_material(): void
    {
        $user = $this->createBibliotecario();
        $area = $this->createArea($user->institucion_id);
        $material = Material::forceCreate([
            'titulo' => 'Original',
            'autor' => 'Autor',
            'anio_publicacion' => 2020,
            'area_id' => $area->id,
            'categoria' => 'LIBRO',
            'codigo' => '300-002',
            'disponibilidad' => 1,
            'disponibilidad_reservada' => 0,
            'editorial' => 'Edit',
            'clasificacion_fisica' => 'A2',
            'institucion_id' => $user->institucion_id,
        ]);

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/materiales/{$material->id}", [
                'titulo' => 'Actualizado',
                'autor' => 'Autor Edit',
                'anio_publicacion' => 2021,
                'area_id' => $area->id,
                'categoria' => 'LIBRO',
                'disponibilidad' => 3,
            ])
            ->assertOk();

        $this->assertDatabaseHas('materiales', ['id' => $material->id, 'titulo' => 'Actualizado']);
    }

    public function test_api_elimina_material(): void
    {
        $user = $this->createBibliotecario();
        $area = $this->createArea($user->institucion_id);
        $material = Material::forceCreate([
            'titulo' => 'ABorrar',
            'autor' => 'Autor',
            'anio_publicacion' => 2020,
            'area_id' => $area->id,
            'categoria' => 'LIBRO',
            'codigo' => '300-003',
            'disponibilidad' => 1,
            'disponibilidad_reservada' => 0,
            'editorial' => 'Edit',
            'clasificacion_fisica' => 'A3',
            'institucion_id' => $user->institucion_id,
        ]);

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/materiales/{$material->id}")
            ->assertOk();

        $this->assertDatabaseMissing('materiales', ['id' => $material->id]);
    }

    // ─── Areas API ─────────────────────────────────────────────────────────

    public function test_api_lista_areas(): void
    {
        $user = $this->createBibliotecario();
        $this->createArea($user->institucion_id);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/areas')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_api_crea_area(): void
    {
        $user = $this->createBibliotecario();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/areas', [
                'codigo_dewey' => '500',
                'nombre' => 'Ciencias',
                'Abreviado' => 'CIE',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('areas', ['codigo_dewey' => '500']);
    }

    public function test_api_actualiza_area(): void
    {
        $user = $this->createBibliotecario();
        $area = $this->createArea($user->institucion_id);

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/areas/{$area->id}", [
                'codigo_dewey' => '250',
                'nombre' => 'Matematica Avanzada',
                'Abreviado' => 'MAT-A',
            ])
            ->assertOk();

        $this->assertDatabaseHas('areas', ['id' => $area->id, 'codigo_dewey' => '250']);
    }

    public function test_api_elimina_area(): void
    {
        $user = $this->createBibliotecario();
        $area = $this->createArea($user->institucion_id);

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/areas/{$area->id}")
            ->assertOk();

        $this->assertDatabaseMissing('areas', ['id' => $area->id]);
    }

    // ─── Noticias API ──────────────────────────────────────────────────────

    public function test_api_lista_noticias_publico(): void
    {
        $user = $this->createBibliotecario();
        \DB::table('noticias')->insert([
            'titulo' => 'Noticia Publica',
            'descripcion' => 'Desc',
            'institucion_id' => $user->institucion_id,
        ]);

        $this->getJson('/api/v1/noticias')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_api_crea_noticia(): void
    {
        $user = $this->createBibliotecario();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/noticias', [
                'titulo' => 'Noticia Test',
                'descripcion' => 'Descripcion de prueba',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('noticias', ['titulo' => 'Noticia Test']);
    }

    public function test_api_actualiza_noticia(): void
    {
        $user = $this->createBibliotecario();
        $id = \DB::table('noticias')->insertGetId([
            'titulo' => 'Original',
            'descripcion' => 'Desc',
            'institucion_id' => $user->institucion_id,
        ]);

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/noticias/{$id}", [
                'titulo' => 'Actualizada',
                'descripcion' => 'Nueva desc',
            ])
            ->assertOk();

        $this->assertDatabaseHas('noticias', ['id' => $id, 'titulo' => 'Actualizada']);
    }

    public function test_api_elimina_noticia(): void
    {
        $user = $this->createBibliotecario();
        $id = \DB::table('noticias')->insertGetId([
            'titulo' => 'ABorrar',
            'descripcion' => 'Desc',
            'institucion_id' => $user->institucion_id,
        ]);

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/noticias/{$id}")
            ->assertOk();

        $this->assertSoftDeleted('noticias', ['id' => $id]);
    }

    // ─── Prestamos API ─────────────────────────────────────────────────────

    public function test_api_lista_prestamos(): void
    {
        $user = $this->createBibliotecario();
        $socio = $this->createSocio($user->institucion_id);
        $area = $this->createArea($user->institucion_id);
        $material = Material::forceCreate([
            'titulo' => 'Prestamo API',
            'autor' => 'A',
            'anio_publicacion' => 2020,
            'area_id' => $area->id,
            'categoria' => 'LIBRO',
            'codigo' => '400-001',
            'disponibilidad' => 2,
            'disponibilidad_reservada' => 0,
            'editorial' => 'Edit',
            'clasificacion_fisica' => 'B1',
            'institucion_id' => $user->institucion_id,
        ]);

        \DB::table('prestamos')->insert([
            'socio_id' => $socio->id,
            'material_id' => $material->id,
            'fecha_prestamo' => now()->toDateString(),
            'fecha_devolucion' => now()->addDays(7)->toDateString(),
            'estado' => 'activo',
            'cantidad' => 1,
            'institucion_id' => $user->institucion_id,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/prestamos')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_api_crea_prestamo(): void
    {
        $user = $this->createBibliotecario();
        $area = $this->createArea($user->institucion_id);
        $socio = $this->createSocio($user->institucion_id);
        $material = Material::forceCreate([
            'titulo' => 'Libro Prestamo',
            'autor' => 'A',
            'anio_publicacion' => 2020,
            'area_id' => $area->id,
            'categoria' => 'LIBRO',
            'codigo' => '400-002',
            'disponibilidad' => 2,
            'disponibilidad_reservada' => 0,
            'editorial' => 'Edit',
            'clasificacion_fisica' => 'B2',
            'institucion_id' => $user->institucion_id,
        ]);
        MaterialEjemplar::forceCreate([
            'material_id' => $material->id,
            'institucion_id' => $user->institucion_id,
            'codigo_ejemplar' => '400-002-E1',
            'estado' => 'disponible',
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/prestamos', [
                'socio_id' => $socio->id,
                'material_id' => $material->id,
                'cantidad' => 1,
                'fecha_devolucion' => now()->addDays(7)->toDateString(),
            ])
            ->assertCreated();

        $this->assertDatabaseHas('prestamos', [
            'socio_id' => $socio->id,
            'material_id' => $material->id,
            'estado' => 'activo',
        ]);
    }

    public function test_api_devuelve_prestamo(): void
    {
        $user = $this->createBibliotecario();
        $socio = $this->createSocio($user->institucion_id);
        $area = $this->createArea($user->institucion_id);
        $material = Material::forceCreate([
            'titulo' => 'Libro Devol',
            'autor' => 'A',
            'anio_publicacion' => 2020,
            'area_id' => $area->id,
            'categoria' => 'LIBRO',
            'codigo' => '400-003',
            'disponibilidad' => 1,
            'disponibilidad_reservada' => 0,
            'editorial' => 'Edit',
            'clasificacion_fisica' => 'B3',
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

        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/prestamos/{$prestamoId}/devolver")
            ->assertOk();

        $this->assertDatabaseHas('prestamos', ['id' => $prestamoId, 'estado' => 'devuelto']);
        $this->assertDatabaseHas('materiales', ['id' => $material->id, 'disponibilidad' => 2]);
    }

    public function test_api_extiende_prestamo(): void
    {
        $user = $this->createBibliotecario();
        $socio = $this->createSocio($user->institucion_id);
        $area = $this->createArea($user->institucion_id);
        $material = Material::forceCreate([
            'titulo' => 'Libro Ext',
            'autor' => 'A',
            'anio_publicacion' => 2020,
            'area_id' => $area->id,
            'categoria' => 'LIBRO',
            'codigo' => '400-004',
            'disponibilidad' => 0,
            'disponibilidad_reservada' => 0,
            'editorial' => 'Edit',
            'clasificacion_fisica' => 'B4',
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

        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/prestamos/{$prestamoId}/extender", ['dias' => 5])
            ->assertOk();

        $fechaDevolucion = \Carbon\Carbon::parse(\DB::table('prestamos')->where('id', $prestamoId)->value('fecha_devolucion'));
        $this->assertEquals(now()->addDays(10)->toDateString(), $fechaDevolucion->toDateString());
    }

    // ─── Multas API ────────────────────────────────────────────────────────

    public function test_api_lista_multas(): void
    {
        $user = $this->createBibliotecario();
        $socio = $this->createSocio($user->institucion_id);

        \DB::table('multas')->insert([
            'socio_id' => $socio->id,
            'monto' => 100,
            'motivo' => 'Test',
            'pagada' => false,
            'fecha_creacion' => now()->toDateString(),
            'institucion_id' => $user->institucion_id,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/multas')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_api_crea_multa(): void
    {
        $user = $this->createBibliotecario();
        $socio = $this->createSocio($user->institucion_id);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/multas', [
                'socio_id' => $socio->id,
                'monto' => 250.00,
                'motivo' => 'Perdida de libro',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('multas', ['socio_id' => $socio->id, 'monto' => 250.00]);
    }

    public function test_api_paga_multa(): void
    {
        $user = $this->createBibliotecario();
        $socio = $this->createSocio($user->institucion_id);
        $multaId = \DB::table('multas')->insertGetId([
            'socio_id' => $socio->id,
            'monto' => 150,
            'motivo' => 'Test',
            'pagada' => false,
            'fecha_creacion' => now()->toDateString(),
            'institucion_id' => $user->institucion_id,
        ]);

        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/multas/{$multaId}/pagar")
            ->assertOk();

        $this->assertDatabaseHas('multas', ['id' => $multaId, 'pagada' => true]);
    }

    public function test_api_perdona_multa(): void
    {
        $user = $this->createBibliotecario();
        $socio = $this->createSocio($user->institucion_id);
        $multaId = \DB::table('multas')->insertGetId([
            'socio_id' => $socio->id,
            'monto' => 200,
            'motivo' => 'Test',
            'pagada' => false,
            'fecha_creacion' => now()->toDateString(),
            'institucion_id' => $user->institucion_id,
        ]);

        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/multas/{$multaId}/perdonar", [
                'observaciones' => 'Se perdona por buen comportamiento',
            ])
            ->assertOk();

        $this->assertDatabaseHas('multas', ['id' => $multaId, 'pagada' => true]);
    }

    // ─── Alertas API ───────────────────────────────────────────────────────

    public function test_api_lista_alertas(): void
    {
        $user = $this->createBibliotecario();

        \DB::table('alertas')->insert([
            'institucion_id' => $user->institucion_id,
            'tipo' => 'proximo_vencer',
            'descripcion' => 'Alerta test',
            'fecha_alerta' => now(),
            'leida' => false,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/alertas')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_api_marca_alerta_leida(): void
    {
        $user = $this->createBibliotecario();
        $alertaId = \DB::table('alertas')->insertGetId([
            'institucion_id' => $user->institucion_id,
            'tipo' => 'vencido',
            'descripcion' => 'Vencido test',
            'fecha_alerta' => now(),
            'leida' => false,
        ]);

        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/alertas/{$alertaId}/leida")
            ->assertOk();

        $this->assertDatabaseHas('alertas', ['id' => $alertaId, 'leida' => true]);
    }

    public function test_api_marca_todas_alertas_leidas(): void
    {
        $user = $this->createBibliotecario();
        \DB::table('alertas')->insert([
            'institucion_id' => $user->institucion_id,
            'tipo' => 'proximo_vencer',
            'descripcion' => 'A1',
            'fecha_alerta' => now(),
            'leida' => false,
        ]);
        \DB::table('alertas')->insert([
            'institucion_id' => $user->institucion_id,
            'tipo' => 'vencido',
            'descripcion' => 'A2',
            'fecha_alerta' => now(),
            'leida' => false,
        ]);

        $this->actingAs($user, 'sanctum')
            ->patchJson('/api/v1/alertas/todas-leidas')
            ->assertOk();

        $this->assertEquals(0, \DB::table('alertas')->where('leida', false)->count());
    }

    // ─── Usuarios API ──────────────────────────────────────────────────────

    public function test_api_lista_usuarios(): void
    {
        $user = $this->createBibliotecario();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/usuarios')
            ->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_api_crea_usuario(): void
    {
        $user = $this->createBibliotecario();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/usuarios', [
                'nombre' => 'Nuevo User',
                'email' => 'nuevo@test.com',
                'usuario' => 'nuevouser',
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
                'rol' => 'alumno',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('users', ['email' => 'nuevo@test.com']);
    }

    public function test_api_toggle_usuario(): void
    {
        $user = $this->createBibliotecario();
        $otro = User::create([
            'name' => 'Otro',
            'nombre' => 'Otro',
            'email' => 'otro@test.com',
            'usuario' => 'otro',
            'password' => 'secret',
            'picture' => '',
            'institucion_id' => $user->institucion_id,
            'activo' => true,
        ]);

        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/usuarios/{$otro->id}/toggle-activo")
            ->assertOk();

        $this->assertDatabaseHas('users', ['id' => $otro->id, 'activo' => false]);
    }

    // ─── Reservas API: cancelar ────────────────────────────────────────────

    public function test_api_cancela_reserva(): void
    {
        $user = $this->createBibliotecario();
        $area = $this->createArea($user->institucion_id);
        $socio = $this->createSocio($user->institucion_id);
        $material = Material::forceCreate([
            'titulo' => 'Reserva Cancelable',
            'autor' => 'A',
            'anio_publicacion' => 2020,
            'area_id' => $area->id,
            'categoria' => 'LIBRO',
            'codigo' => '500-001',
            'disponibilidad' => 2,
            'disponibilidad_reservada' => 1,
            'editorial' => 'Edit',
            'clasificacion_fisica' => 'C1',
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

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/reservas/{$reservaId}")
            ->assertOk();

        $this->assertDatabaseHas('reservas', ['id' => $reservaId, 'estado' => 'expirada']);
    }

    // ─── Rechazar reserva falla sin permiso ────────────────────────────────

    public function test_api_rechazar_reserva_falla_sin_permiso(): void
    {
        $user = $this->createBibliotecario();
        $user->syncPermissions([]);
        $user->syncRoles([]);

        $area = $this->createArea($user->institucion_id);
        $socio = $this->createSocio($user->institucion_id);
        $material = Material::forceCreate([
            'titulo' => 'Reserva Sin Permiso',
            'autor' => 'A',
            'anio_publicacion' => 2020,
            'area_id' => $area->id,
            'categoria' => 'LIBRO',
            'codigo' => '500-002',
            'disponibilidad' => 2,
            'disponibilidad_reservada' => 1,
            'editorial' => 'Edit',
            'clasificacion_fisica' => 'C2',
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

        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/reservas/{$reservaId}/rechazar")
            ->assertForbidden();
    }
}
