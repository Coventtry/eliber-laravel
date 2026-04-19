<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Poblar tabla 'areas' con las áreas del dump original.
 * Solo usar en instalación nueva — en producción los datos ya existen.
 */
class AreasSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('areas')->insertOrIgnore([
            ['id' => 1,   'codigo_dewey' => '010',  'nombre' => 'Ciencias Naturales / Tecnología / Medioambiente',     'Abreviado' => 'CIENAT'],
            ['id' => 2,   'codigo_dewey' => '100',  'nombre' => 'Lengua y Literatura / Prácticas del Lenguaje',         'Abreviado' => 'LENG'],
            ['id' => 3,   'codigo_dewey' => '200',  'nombre' => 'Matemática',                                           'Abreviado' => 'MAT'],
            ['id' => 4,   'codigo_dewey' => '300',  'nombre' => 'Ciencias Sociales / Historia / Geografía',             'Abreviado' => 'CISO'],
            ['id' => 5,   'codigo_dewey' => '400',  'nombre' => 'Educación Sexual Integral (ESI)',                      'Abreviado' => 'ESI'],
            ['id' => 6,   'codigo_dewey' => '500',  'nombre' => 'Educación Inicial / Nivel Primario',                   'Abreviado' => 'EDINIP'],
            ['id' => 7,   'codigo_dewey' => '600',  'nombre' => 'Educación Física / Juego y Movimiento',                'Abreviado' => 'EDUFIS'],
            ['id' => 8,   'codigo_dewey' => '700',  'nombre' => 'Filosofía / Formación Ética y Ciudadana',              'Abreviado' => 'FILOS'],
            ['id' => 9,   'codigo_dewey' => '800',  'nombre' => 'Psicología / Neurociencias / Educación Emocional',     'Abreviado' => 'PSICO'],
            ['id' => 10,  'codigo_dewey' => '900',  'nombre' => 'Pedagogía Crítica / Gestión Escolar / Evaluación',     'Abreviado' => 'PEDA'],
            ['id' => 82,  'codigo_dewey' => '681',  'nombre' => 'Proyectos Institucionales / Currículo / Planificación', 'Abreviado' => 'PROY'],
            ['id' => 83,  'codigo_dewey' => '526',  'nombre' => 'Inclusión / Diversidad / Interculturalidad',           'Abreviado' => 'INCLU'],
            ['id' => 84,  'codigo_dewey' => '004',  'nombre' => 'Ciencias de la Computación y Tecnología de la Información', 'Abreviado' => null],
            ['id' => 86,  'codigo_dewey' => '1000', 'nombre' => 'Didáctico / Formación Docente',                        'Abreviado' => 'DIDAC'],
            ['id' => 87,  'codigo_dewey' => '250',  'nombre' => 'Arte y Literatura Argentina',                          'Abreviado' => 'ARTELIT'],
            ['id' => 89,  'codigo_dewey' => '1100', 'nombre' => 'Lengua Extranjera / Enseñanza del Inglés',             'Abreviado' => 'LENEXT'],
            ['id' => 90,  'codigo_dewey' => '1200', 'nombre' => 'Educación Artística / Convivencia Escolar',            'Abreviado' => 'EDUART'],
            ['id' => 91,  'codigo_dewey' => '1300', 'nombre' => 'Literatura Infantil / Juvenil',                        'Abreviado' => 'LITINF'],
            ['id' => 92,  'codigo_dewey' => '50',   'nombre' => 'TIC / Informática Educativa / Recursos Digitales',     'Abreviado' => 'TIC'],
            ['id' => 93,  'codigo_dewey' => '60',   'nombre' => 'Investigación Educativa / Tesis / Ensayos',            'Abreviado' => 'INVEST'],
            ['id' => 94,  'codigo_dewey' => '70',   'nombre' => 'Educación Ambiental / Sostenibilidad',                 'Abreviado' => 'AMBIENT'],
            ['id' => 95,  'codigo_dewey' => '210',  'nombre' => 'Educación en Pandemia / Nuevos Escenarios',            'Abreviado' => 'PANDEM'],
            ['id' => 96,  'codigo_dewey' => '220',  'nombre' => 'Teoría Educativa / Sociología / Política de la Educación', 'Abreviado' => 'TEO'],
            ['id' => 97,  'codigo_dewey' => '230',  'nombre' => 'Antologías / Compilaciones Literarias',                'Abreviado' => 'ANTO'],
            ['id' => 98,  'codigo_dewey' => '240',  'nombre' => 'Teatro / Expresión Corporal / Narración Oral',         'Abreviado' => 'TEATRO'],
            ['id' => 99,  'codigo_dewey' => '260',  'nombre' => 'Lectura y Escritura / Talleres / Producción Textual',  'Abreviado' => 'LECTESCR'],
            ['id' => 100, 'codigo_dewey' => '270',  'nombre' => 'Revistas Educativas / Publicaciones Especiales',       'Abreviado' => 'REVIST'],
            ['id' => 101, 'codigo_dewey' => '280',  'nombre' => 'Manual Escolar / Recursos Didácticos Generales',       'Abreviado' => 'MANUAL'],
            ['id' => 102, 'codigo_dewey' => '310',  'nombre' => 'Mapas / Atlas / Cartografía Escolar',                  'Abreviado' => 'MAPA'],
            ['id' => 103, 'codigo_dewey' => '320',  'nombre' => 'Educación Rural / Contextos Desfavorecidos',           'Abreviado' => 'RURAL'],
            ['id' => 104, 'codigo_dewey' => '330',  'nombre' => 'Historia Argentina / Historia Universal / Procesos Políticos', 'Abreviado' => 'HISTAR'],
        ]);
    }
}
