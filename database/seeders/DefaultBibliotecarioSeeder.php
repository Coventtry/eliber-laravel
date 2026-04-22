<?php

namespace Database\Seeders;

use App\Models\Bibliotecario;
use App\Models\Institucion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DefaultBibliotecarioSeeder extends Seeder
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

        $admin = Bibliotecario::firstOrCreate(
            ['usuario' => $usuario],
            [
                'nombre' => $nombre,
                'email' => $email,
                'telefono' => null,
                'password' => $password,
                'picture' => '',
                'institucion_id' => $institucion->id,
            ]
        );

        if (empty($admin->institucion_id)) {
            $admin->forceFill(['institucion_id' => $institucion->id])->save();
        }

        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
    }
}
