<?php

namespace Database\Seeders;

use App\Models\Institucion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InstitucionesSeeder extends Seeder
{
    public function run(): void
    {
        $nombre = env('DEFAULT_INSTITUCION_NOMBRE', 'Institucion Principal');
        $slug = env('DEFAULT_INSTITUCION_SLUG', Str::slug($nombre) ?: 'principal');
        $estado = env('DEFAULT_INSTITUCION_ESTADO', 'activa');

        Institucion::firstOrCreate(
            ['slug' => $slug],
            [
                'nombre' => $nombre,
                'estado' => in_array($estado, ['activa', 'inactiva'], true) ? $estado : 'activa',
            ]
        );
    }
}
