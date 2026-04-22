<?php

namespace Database\Seeders;

use App\Models\Institucion;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $institucion = Institucion::query()->first();

        if (!$institucion) {
            return;
        }

        if (DB::table('socios')->exists() || DB::table('materiales')->exists() || DB::table('prestamos')->exists()) {
            return;
        }

        $ahora = now();
        $fechaPrestamo = Carbon::today();
        $fechaDevolucion = Carbon::today()->addDays(7);

        DB::table('socios')->insert([
            [
                'nombre' => 'Ana',
                'apellido' => 'Gomez',
                'telefono' => '2454123456',
                'direccion' => 'Av. Escolar 123',
                'email' => 'ana.gomez@example.com',
                'anio' => 5,
                'division' => 2,
                'activo' => 1,
                'institucion_id' => $institucion->id,
            ],
            [
                'nombre' => 'Martin',
                'apellido' => 'Perez',
                'telefono' => '2454654321',
                'direccion' => 'Calle Biblioteca 456',
                'email' => 'martin.perez@example.com',
                'anio' => 6,
                'division' => 1,
                'activo' => 1,
                'institucion_id' => $institucion->id,
            ],
        ]);

        $areaLiteraturaId = DB::table('areas')->where('codigo_dewey', '1300')->value('id')
            ?? DB::table('areas')->where('codigo_dewey', '100')->value('id');
        $areaMatematicaId = DB::table('areas')->where('codigo_dewey', '200')->value('id');

        DB::table('materiales')->insert([
            [
                'titulo' => 'El Principito',
                'autor' => 'Antoine de Saint-Exupery',
                'anio_publicacion' => 1943,
                'area_id' => $areaLiteraturaId,
                'categoria' => 'LIBRO',
                'codigo' => '1300-901',
                'disponibilidad' => 4,
                'disponibilidad_reservada' => 0,
                'editorial' => 'Salamandra',
                'clasificacion_fisica' => 'LITINF-A-(E)1-1',
                'institucion_id' => $institucion->id,
            ],
            [
                'titulo' => 'Matematica 6',
                'autor' => 'Equipo Docente',
                'anio_publicacion' => 2022,
                'area_id' => $areaMatematicaId,
                'categoria' => 'LIBRO',
                'codigo' => '200-901',
                'disponibilidad' => 3,
                'disponibilidad_reservada' => 1,
                'editorial' => 'Puerto de Palos',
                'clasificacion_fisica' => 'MAT-B-(E)2-1',
                'institucion_id' => $institucion->id,
            ],
            [
                'titulo' => 'Ciencias para Curiosos',
                'autor' => 'Laura Bianchi',
                'anio_publicacion' => 2021,
                'area_id' => DB::table('areas')->where('codigo_dewey', '010')->value('id'),
                'categoria' => 'LIBRO',
                'codigo' => '010-901',
                'disponibilidad' => 2,
                'disponibilidad_reservada' => 0,
                'editorial' => 'Colihue',
                'clasificacion_fisica' => 'CIENAT-C-(M)1-2',
                'institucion_id' => $institucion->id,
            ],
        ]);

        $socioAnaId = DB::table('socios')->where('email', 'ana.gomez@example.com')->value('id');
        $socioMartinId = DB::table('socios')->where('email', 'martin.perez@example.com')->value('id');
        $materialPrincipitoId = DB::table('materiales')->where('codigo', '1300-901')->value('id');
        $materialMatematicaId = DB::table('materiales')->where('codigo', '200-901')->value('id');

        DB::table('prestamos')->insert([
            'socio_id' => $socioAnaId,
            'material_id' => $materialPrincipitoId,
            'fecha_prestamo' => $fechaPrestamo->toDateString(),
            'fecha_devolucion' => $fechaDevolucion->toDateString(),
            'estado' => 'activo',
            'cantidad' => 1,
            'institucion_id' => $institucion->id,
        ]);

        DB::table('reservas')->insert([
            'material_id' => $materialMatematicaId,
            'socio_id' => $socioMartinId,
            'estado' => 'pendiente',
            'fecha_reserva' => $ahora,
            'fecha_vencimiento' => $ahora->copy()->addDays(2),
            'created_at' => $ahora,
            'updated_at' => $ahora,
            'deleted_at' => null,
        ]);

        DB::table('noticias')->insert([
            'titulo' => 'Bienvenidos a e-LibeR',
            'descripcion' => 'Instalacion inicial completada con datos de ejemplo para probar el sistema.',
            'imagen' => null,
            'fecha' => $ahora,
            'deleted_at' => null,
            'institucion_id' => $institucion->id,
        ]);

        DB::table('anotaciones')->insert([
            'anotacion' => 'Revisar los datos demo y reemplazarlos por informacion real antes de usar el sistema en produccion.',
            'fecha' => $ahora,
            'deleted_at' => null,
            'institucion_id' => $institucion->id,
        ]);

        DB::table('historial_socios')->insert([
            'id_socio' => $socioAnaId,
            'accion' => 'ALTA',
            'fecha' => $ahora,
            'observaciones' => 'Socio de ejemplo creado por SampleDataSeeder.',
        ]);
    }
}
