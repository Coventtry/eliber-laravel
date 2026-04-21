<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);

        $institucionId = \App\Models\Institucion::first()?->id ?? 1;

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'nombre' => 'Administrador',
                'password' => bcrypt('password'),
                'activo' => true,
                'institucion_id' => $institucionId,
            ]
        );
        $admin->assignRole('admin');
    }
}
