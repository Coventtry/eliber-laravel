<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriasFisicasSeeder extends Seeder
{
    public function run(): void
    {
        $instituciones = DB::table('instituciones')->pluck('id');
        $nombres = [
            'Libro', 'Manual', 'Diccionario', 'Enciclopedia', 'Revista',
            'Atlas', 'Folleto', 'Cuadernillo', 'CD / DVD', 'Mapa',
            'Periódico', 'Lámina', 'Juego didáctico', 'Rompecabezas',
        ];

        foreach ($instituciones as $id) {
            foreach ($nombres as $nombre) {
                DB::table('categorias_fisicas')->insertOrIgnore([
                    'nombre'         => $nombre,
                    'institucion_id' => $id,
                ]);
            }
        }
    }
}
