<?php

namespace Database\Seeders;

use App\Models\Institucion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DefaultAdminSeeder extends Seeder
{
    public function run(): void
    {
        $nombreInstitucion = env('DEFAULT_INSTITUCION_NOMBRE', 'Institucion Principal');
        $slugInstitucion = env('DEFAULT_INSTITUCION_SLUG', Str::slug($nombreInstitucion) ?: 'principal');

        $institucion = Institucion::where('slug', $slugInstitucion)->first()
            ?? Institucion::query()->firstOrCreate(
                ['slug' => $slugInstitucion],
                ['nombre' => $nombreInstitucion, 'estado' => env('DEFAULT_INSTITUCION_ESTADO', 'activa')]
            );

        $usuario = env('DEFAULT_ADMIN_USUARIO', 'admin');
        $nombre = env('DEFAULT_ADMIN_NOMBRE', 'Administrador');
        $email = env('DEFAULT_ADMIN_EMAIL', 'admin@example.com');
        $password = env('DEFAULT_ADMIN_PASSWORD', 'password');

        $admin = User::firstOrCreate(
            ['usuario' => $usuario],
            [
                'name' => $nombre,
                'nombre' => $nombre,
                'email' => $email,
                'password' => $password,
                'picture' => '',
                'institucion_id' => $institucion->id,
                'activo' => true,
            ]
        );

        if (empty($admin->institucion_id)) {
            $admin->forceFill(['institucion_id' => $institucion->id])->save();
        }

        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
    }
}
