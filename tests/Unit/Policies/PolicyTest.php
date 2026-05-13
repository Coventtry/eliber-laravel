<?php

namespace Tests\Unit\Policies;

use App\Models\Alerta;
use App\Models\Anotacion;
use App\Models\Area;
use App\Models\Material;
use App\Models\Multa;
use App\Models\Noticia;
use App\Models\Prestamo;
use App\Models\Reserva;
use App\Models\Socio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PolicyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $alumno;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $alumnoRole = Role::create(['name' => 'alumno', 'guard_name' => 'web']);

        foreach (['gestionar-socios', 'gestionar-materiales', 'gestionar-prestamos', 'gestionar-areas', 'gestionar-noticias', 'gestionar-anotaciones', 'gestionar-usuarios', 'gestionar-multas', 'ver-reportes'] as $p) {
            Permission::create(['name' => $p, 'guard_name' => 'web']);
        }
        $adminRole->givePermissionTo(Permission::all());

        $this->admin = User::factory()->create();
        $this->admin->assignRole($adminRole);

        $this->alumno = User::factory()->create();
        $this->alumno->assignRole($alumnoRole);
    }

    public function test_admin_puede_ver_cualquier_socio(): void
    {
        $this->assertTrue($this->admin->can('viewAny', Socio::class));
    }

    public function test_alumno_no_puede_ver_socios(): void
    {
        $this->assertFalse($this->alumno->can('viewAny', Socio::class));
    }

    public function test_admin_puede_crear_material(): void
    {
        $this->assertTrue($this->admin->can('create', Material::class));
    }

    public function test_alumno_no_puede_crear_material(): void
    {
        $this->assertFalse($this->alumno->can('create', Material::class));
    }

    public function test_admin_puede_ver_areas(): void
    {
        $this->assertTrue($this->admin->can('viewAny', Area::class));
    }

    public function test_admin_puede_gestionar_prestamos(): void
    {
        $this->assertTrue($this->admin->can('viewAny', Prestamo::class));
        $this->assertTrue($this->admin->can('create', Prestamo::class));
        $prestamo = Prestamo::factory()->create(['institucion_id' => $this->admin->institucion_id]);
        $this->assertTrue($this->admin->can('update', $prestamo));
    }

    public function test_admin_puede_gestionar_multas(): void
    {
        $this->assertTrue($this->admin->can('viewAny', Multa::class));
        $this->assertTrue($this->admin->can('create', Multa::class));
    }

    public function test_admin_puede_gestionar_noticias(): void
    {
        $this->assertTrue($this->admin->can('viewAny', Noticia::class));
        $this->assertTrue($this->admin->can('create', Noticia::class));
    }

    public function test_admin_puede_gestionar_anotaciones(): void
    {
        $this->assertTrue($this->admin->can('viewAny', Anotacion::class));
        $this->assertTrue($this->admin->can('create', Anotacion::class));
    }

    public function test_alumno_puede_crear_reserva(): void
    {
        $this->assertTrue($this->alumno->can('create', Reserva::class));
    }

    public function test_admin_puede_ver_alertas(): void
    {
        $this->assertTrue($this->admin->can('viewAny', Alerta::class));
    }
}
