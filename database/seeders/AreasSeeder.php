<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * 100 divisiones estándar de la Clasificación Decimal Dewey (DDC 23).
 * Las áreas son globales — no pertenecen a ninguna institución en particular.
 */
class AreasSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('areas')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        DB::table('areas')->insert([
            // ── 000 Ciencias de la computación, información y obras generales ──
            ['codigo_dewey' => '000', 'nombre' => 'Ciencias de la computación, información y obras generales', 'Abreviado' => 'GENRAL'],
            ['codigo_dewey' => '010', 'nombre' => 'Bibliografías',                                             'Abreviado' => 'BIBLIO'],
            ['codigo_dewey' => '020', 'nombre' => 'Bibliotecología y ciencias de la información',              'Abreviado' => 'BIBLINF'],
            ['codigo_dewey' => '030', 'nombre' => 'Enciclopedias y obras de referencia general',               'Abreviado' => 'ENCICL'],
            ['codigo_dewey' => '050', 'nombre' => 'Publicaciones seriadas y revistas generales',               'Abreviado' => 'REVIS'],
            ['codigo_dewey' => '060', 'nombre' => 'Organizaciones, museos y asociaciones',                     'Abreviado' => 'ORGAN'],
            ['codigo_dewey' => '070', 'nombre' => 'Medios de comunicación, periodismo y editorial',            'Abreviado' => 'PERIOD'],
            ['codigo_dewey' => '080', 'nombre' => 'Colecciones generales',                                     'Abreviado' => 'COLGEN'],
            ['codigo_dewey' => '090', 'nombre' => 'Manuscritos, libros raros y otros impresos',                'Abreviado' => 'MANUS'],

            // ── 100 Filosofía y psicología ──
            ['codigo_dewey' => '100', 'nombre' => 'Filosofía y psicología',                                    'Abreviado' => 'FILOS'],
            ['codigo_dewey' => '110', 'nombre' => 'Metafísica',                                                'Abreviado' => 'METAF'],
            ['codigo_dewey' => '120', 'nombre' => 'Epistemología',                                             'Abreviado' => 'EPIST'],
            ['codigo_dewey' => '130', 'nombre' => 'Parapsicología y ocultismo',                                'Abreviado' => 'PARAPS'],
            ['codigo_dewey' => '140', 'nombre' => 'Escuelas y sistemas filosóficos',                           'Abreviado' => 'ESCFIL'],
            ['codigo_dewey' => '150', 'nombre' => 'Psicología',                                                'Abreviado' => 'PSICO'],
            ['codigo_dewey' => '160', 'nombre' => 'Lógica filosófica',                                        'Abreviado' => 'LOGICA'],
            ['codigo_dewey' => '170', 'nombre' => 'Ética',                                                     'Abreviado' => 'ETICA'],
            ['codigo_dewey' => '180', 'nombre' => 'Filosofía antigua, medieval y oriental',                    'Abreviado' => 'FILANT'],
            ['codigo_dewey' => '190', 'nombre' => 'Filosofía occidental moderna',                              'Abreviado' => 'FILMOD'],

            // ── 200 Religión ──
            ['codigo_dewey' => '200', 'nombre' => 'Religión',                                                  'Abreviado' => 'RELIG'],
            ['codigo_dewey' => '210', 'nombre' => 'Filosofía y teoría de la religión',                         'Abreviado' => 'FILREL'],
            ['codigo_dewey' => '220', 'nombre' => 'Biblia',                                                    'Abreviado' => 'BIBLIA'],
            ['codigo_dewey' => '230', 'nombre' => 'Teología cristiana y dogmática',                            'Abreviado' => 'TEOCRI'],
            ['codigo_dewey' => '240', 'nombre' => 'Moral cristiana y práctica devocional',                     'Abreviado' => 'MORCRJ'],
            ['codigo_dewey' => '250', 'nombre' => 'Órdenes y pastoral cristiana',                              'Abreviado' => 'PASTOR'],
            ['codigo_dewey' => '260', 'nombre' => 'Iglesia y orden social cristiano',                          'Abreviado' => 'IGLESI'],
            ['codigo_dewey' => '270', 'nombre' => 'Historia del Cristianismo',                                 'Abreviado' => 'HISCRI'],
            ['codigo_dewey' => '280', 'nombre' => 'Denominaciones y sectas cristianas',                        'Abreviado' => 'DENOMI'],
            ['codigo_dewey' => '290', 'nombre' => 'Otras religiones',                                          'Abreviado' => 'OTRELI'],

            // ── 300 Ciencias sociales ──
            ['codigo_dewey' => '300', 'nombre' => 'Ciencias sociales, sociología y antropología',              'Abreviado' => 'CISO'],
            ['codigo_dewey' => '310', 'nombre' => 'Estadística',                                               'Abreviado' => 'ESTAT'],
            ['codigo_dewey' => '320', 'nombre' => 'Ciencias políticas y gobierno',                             'Abreviado' => 'POLITI'],
            ['codigo_dewey' => '330', 'nombre' => 'Economía',                                                  'Abreviado' => 'ECON'],
            ['codigo_dewey' => '340', 'nombre' => 'Derecho',                                                   'Abreviado' => 'DERECH'],
            ['codigo_dewey' => '350', 'nombre' => 'Administración pública y ciencias militares',               'Abreviado' => 'ADMPU'],
            ['codigo_dewey' => '360', 'nombre' => 'Problemas y servicios sociales',                            'Abreviado' => 'SERSO'],
            ['codigo_dewey' => '370', 'nombre' => 'Educación',                                                 'Abreviado' => 'EDUC'],
            ['codigo_dewey' => '380', 'nombre' => 'Comercio, comunicaciones y transporte',                     'Abreviado' => 'COMER'],
            ['codigo_dewey' => '390', 'nombre' => 'Costumbres, etiqueta y folclore',                           'Abreviado' => 'FOLKL'],

            // ── 400 Lenguaje ──
            ['codigo_dewey' => '400', 'nombre' => 'Lenguaje y lingüística',                                    'Abreviado' => 'LENG'],
            ['codigo_dewey' => '410', 'nombre' => 'Lingüística',                                               'Abreviado' => 'LINGUI'],
            ['codigo_dewey' => '420', 'nombre' => 'Inglés y anglosajón',                                       'Abreviado' => 'INGLES'],
            ['codigo_dewey' => '430', 'nombre' => 'Alemán y lenguas germánicas relacionadas',                  'Abreviado' => 'ALEMAN'],
            ['codigo_dewey' => '440', 'nombre' => 'Francés y lenguas romances relacionadas',                   'Abreviado' => 'FRANC'],
            ['codigo_dewey' => '450', 'nombre' => 'Italiano, rumano y lenguas relacionadas',                   'Abreviado' => 'ITAL'],
            ['codigo_dewey' => '460', 'nombre' => 'Español y portugués',                                       'Abreviado' => 'ESPPT'],
            ['codigo_dewey' => '470', 'nombre' => 'Latín y lenguas itálicas',                                  'Abreviado' => 'LATIN'],
            ['codigo_dewey' => '480', 'nombre' => 'Griego clásico',                                            'Abreviado' => 'GRIEGO'],
            ['codigo_dewey' => '490', 'nombre' => 'Otras lenguas',                                             'Abreviado' => 'OTLENG'],

            // ── 500 Ciencias naturales y matemáticas ──
            ['codigo_dewey' => '500', 'nombre' => 'Ciencias naturales y matemáticas',                          'Abreviado' => 'CIENAT'],
            ['codigo_dewey' => '510', 'nombre' => 'Matemáticas',                                               'Abreviado' => 'MAT'],
            ['codigo_dewey' => '520', 'nombre' => 'Astronomía y ciencias afines',                              'Abreviado' => 'ASTRO'],
            ['codigo_dewey' => '530', 'nombre' => 'Física',                                                    'Abreviado' => 'FISIC'],
            ['codigo_dewey' => '540', 'nombre' => 'Química y ciencias afines',                                 'Abreviado' => 'QUIM'],
            ['codigo_dewey' => '550', 'nombre' => 'Ciencias de la Tierra y geología',                         'Abreviado' => 'GEOL'],
            ['codigo_dewey' => '560', 'nombre' => 'Paleontología y vida prehistórica',                         'Abreviado' => 'PALEONT'],
            ['codigo_dewey' => '570', 'nombre' => 'Biología y ciencias de la vida',                            'Abreviado' => 'BIOL'],
            ['codigo_dewey' => '580', 'nombre' => 'Plantas — Botánica',                                        'Abreviado' => 'BOTAN'],
            ['codigo_dewey' => '590', 'nombre' => 'Animales — Zoología',                                       'Abreviado' => 'ZOOL'],

            // ── 600 Tecnología y ciencias aplicadas ──
            ['codigo_dewey' => '600', 'nombre' => 'Tecnología y ciencias aplicadas',                           'Abreviado' => 'TECNOL'],
            ['codigo_dewey' => '610', 'nombre' => 'Medicina, salud y ciencias médicas',                        'Abreviado' => 'MED'],
            ['codigo_dewey' => '620', 'nombre' => 'Ingeniería y ciencias afines',                              'Abreviado' => 'INGEN'],
            ['codigo_dewey' => '630', 'nombre' => 'Agricultura y tecnologías afines',                          'Abreviado' => 'AGRIC'],
            ['codigo_dewey' => '640', 'nombre' => 'Hogar y economía doméstica',                                'Abreviado' => 'HOGAR'],
            ['codigo_dewey' => '650', 'nombre' => 'Administración y gestión de empresas',                      'Abreviado' => 'ADMEMP'],
            ['codigo_dewey' => '660', 'nombre' => 'Ingeniería química',                                        'Abreviado' => 'INQUIM'],
            ['codigo_dewey' => '670', 'nombre' => 'Manufactura e industrias',                                  'Abreviado' => 'MANUF'],
            ['codigo_dewey' => '680', 'nombre' => 'Fabricación de productos específicos',                      'Abreviado' => 'FABRI'],
            ['codigo_dewey' => '690', 'nombre' => 'Construcción y edificación',                                'Abreviado' => 'CONSTR'],

            // ── 700 Arte y recreación ──
            ['codigo_dewey' => '700', 'nombre' => 'Arte y recreación',                                         'Abreviado' => 'ARTE'],
            ['codigo_dewey' => '710', 'nombre' => 'Paisajismo y planificación de áreas',                       'Abreviado' => 'PAISAJ'],
            ['codigo_dewey' => '720', 'nombre' => 'Arquitectura',                                              'Abreviado' => 'ARQUIT'],
            ['codigo_dewey' => '730', 'nombre' => 'Escultura, cerámica y trabajo en metal',                    'Abreviado' => 'ESCULT'],
            ['codigo_dewey' => '740', 'nombre' => 'Dibujo y artes decorativas',                                'Abreviado' => 'DIBUJO'],
            ['codigo_dewey' => '750', 'nombre' => 'Pintura y pintores',                                        'Abreviado' => 'PINTUR'],
            ['codigo_dewey' => '760', 'nombre' => 'Artes gráficas y grabado',                                  'Abreviado' => 'GRAFIC'],
            ['codigo_dewey' => '770', 'nombre' => 'Fotografía y arte digital',                                 'Abreviado' => 'FOTOG'],
            ['codigo_dewey' => '780', 'nombre' => 'Música',                                                    'Abreviado' => 'MUSIC'],
            ['codigo_dewey' => '790', 'nombre' => 'Deportes, juegos y entretenimiento',                        'Abreviado' => 'DEPORT'],

            // ── 800 Literatura y retórica ──
            ['codigo_dewey' => '800', 'nombre' => 'Literatura, retórica y crítica literaria',                  'Abreviado' => 'LIT'],
            ['codigo_dewey' => '810', 'nombre' => 'Literatura estadounidense en inglés',                        'Abreviado' => 'LITAM'],
            ['codigo_dewey' => '820', 'nombre' => 'Literatura inglesa y anglosajona',                          'Abreviado' => 'LITING'],
            ['codigo_dewey' => '830', 'nombre' => 'Literaturas en alemán',                                     'Abreviado' => 'LITALE'],
            ['codigo_dewey' => '840', 'nombre' => 'Literaturas en francés',                                    'Abreviado' => 'LITFRA'],
            ['codigo_dewey' => '850', 'nombre' => 'Literaturas en italiano y rumano',                          'Abreviado' => 'LITITA'],
            ['codigo_dewey' => '860', 'nombre' => 'Literaturas en español y portugués',                        'Abreviado' => 'LITES'],
            ['codigo_dewey' => '870', 'nombre' => 'Literaturas en latín e itálicas',                           'Abreviado' => 'LITLAT'],
            ['codigo_dewey' => '880', 'nombre' => 'Literaturas en griego clásico y moderno',                   'Abreviado' => 'LITGRI'],
            ['codigo_dewey' => '890', 'nombre' => 'Literaturas en otras lenguas',                              'Abreviado' => 'LITOT'],

            // ── 900 Historia y geografía ──
            ['codigo_dewey' => '900', 'nombre' => 'Historia y geografía',                                      'Abreviado' => 'HIST'],
            ['codigo_dewey' => '910', 'nombre' => 'Geografía y viajes',                                        'Abreviado' => 'GEOG'],
            ['codigo_dewey' => '920', 'nombre' => 'Biografía y genealogía',                                    'Abreviado' => 'BIOG'],
            ['codigo_dewey' => '930', 'nombre' => 'Historia del mundo antiguo (hasta aprox. 499)',             'Abreviado' => 'HISANT'],
            ['codigo_dewey' => '940', 'nombre' => 'Historia de Europa',                                        'Abreviado' => 'HIEUR'],
            ['codigo_dewey' => '950', 'nombre' => 'Historia de Asia',                                          'Abreviado' => 'HIASI'],
            ['codigo_dewey' => '960', 'nombre' => 'Historia de África',                                        'Abreviado' => 'HIAFRI'],
            ['codigo_dewey' => '970', 'nombre' => 'Historia de América del Norte',                             'Abreviado' => 'HINORT'],
            ['codigo_dewey' => '980', 'nombre' => 'Historia de América del Sur',                               'Abreviado' => 'HISUR'],
            ['codigo_dewey' => '990', 'nombre' => 'Historia de otras áreas',                                   'Abreviado' => 'HISOT'],
        ]);
    }
}
