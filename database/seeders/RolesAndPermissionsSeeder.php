<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permisos = [
            'gestionar-usuarios',
            'gestionar-materiales',
            'gestionar-socios',
            'gestionar-prestamos',
            'gestionar-areas',
            'gestionar-noticias',
            'gestionar-anotaciones',
            'ver-reportes',
            'ver-materiales',
            'crear-reservas',
            'ver-reservas',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->givePermissionTo(Permission::all());

        Role::firstOrCreate(['name' => 'bibliotecario', 'guard_name' => 'web']);

        $alumno = Role::firstOrCreate(['name' => 'alumno', 'guard_name' => 'web']);
        $alumno->givePermissionTo([
            'ver-materiales',
            'crear-reservas',
            'ver-reservas',
        ]);
    }
}