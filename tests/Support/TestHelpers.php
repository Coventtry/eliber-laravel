<?php

namespace Tests\Support;

use App\Models\Area;
use App\Models\Institucion;
use App\Models\Socio;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

trait TestHelpers
{
    private function createInstitucion(): Institucion
    {
        return Institucion::create([
            'nombre' => fake()->company(),
            'slug' => fake()->slug(),
            'estado' => 'activa',
        ]);
    }

    private function createBibliotecario(): User
    {
        $institucion = $this->createInstitucion();

        $user = User::create([
            'name'           => 'Bibliotecario Test',
            'nombre'         => 'Bibliotecario Test',
            'email'          => fake()->unique()->safeEmail(),
            'usuario'        => fake()->unique()->userName(),
            'password'       => 'secret123',
            'picture'        => '',
            'institucion_id' => $institucion->id,
            'activo'         => true,
        ]);

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionNames = ['gestionar-socios','gestionar-materiales','gestionar-prestamos','gestionar-areas','gestionar-noticias','gestionar-anotaciones','gestionar-usuarios','gestionar-multas','ver-reportes'];

        foreach ($permissionNames as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'sanctum']);
        }

        foreach (['admin', 'bibliotecario', 'alumno'] as $roleName) {
            $roleWeb = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'sanctum']);
            if ($roleName === 'admin') {
                $roleWeb->givePermissionTo($permissionNames);
                $user->assignRole($roleWeb);

                $roleSanctum = Role::where('name', 'admin')->where('guard_name', 'sanctum')->first();
                $roleSanctum->givePermissionTo($permissionNames);
                $user->assignRole($roleSanctum);
            }
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

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
            'email' => fake()->unique()->safeEmail(),
            'anio' => 6,
            'division' => 1,
            'activo' => 1,
            'institucion_id' => $institucionId,
        ]);
    }

    private function createAlumno(): User
    {
        $institucion = $this->createInstitucion();

        $user = User::create([
            'name'           => 'Alumno Test',
            'nombre'         => 'Alumno Test',
            'email'          => fake()->unique()->safeEmail(),
            'usuario'        => fake()->unique()->userName(),
            'password'       => 'secret123',
            'picture'        => '',
            'institucion_id' => $institucion->id,
            'activo'         => true,
        ]);

        $role = Role::firstOrCreate(['name' => 'alumno', 'guard_name' => 'web']);
        $user->assignRole($role);

        return $user;
    }

    private function actingAsBibliotecario(): User
    {
        $user = $this->createBibliotecario();
        $this->actingAs($user, 'sanctum');

        return $user;
    }
}
