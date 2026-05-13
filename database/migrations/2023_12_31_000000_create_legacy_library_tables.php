<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('areas')) {
            Schema::create('areas', function (Blueprint $table) {
                $table->integer('id', true);
                $table->string('codigo_dewey');
                $table->string('nombre');
                $table->string('Abreviado')->nullable();
            });
        }

        if (! Schema::hasTable('bibliotecarios')) {
            Schema::create('bibliotecarios', function (Blueprint $table) {
                $table->integer('id', true);
                $table->string('nombre');
                $table->string('email')->unique();
                $table->string('telefono', 20)->nullable();
                $table->string('usuario')->unique();
                $table->string('password');
                $table->string('Clave_unica', 50)->nullable();
                $table->string('picture', 255)->default('');
            });
        }

        if (! Schema::hasTable('socios')) {
            Schema::create('socios', function (Blueprint $table) {
                $table->integer('id', true);
                $table->string('nombre');
                $table->string('apellido');
                $table->string('telefono')->nullable();
                $table->string('direccion')->nullable();
                $table->string('email')->nullable()->unique();
                $table->unsignedTinyInteger('anio')->nullable();
                $table->unsignedTinyInteger('division')->nullable();
                $table->tinyInteger('activo')->default(1);
            });
        }

        if (! Schema::hasTable('materiales')) {
            Schema::create('materiales', function (Blueprint $table) {
                $table->integer('id', true);
                $table->string('titulo')->nullable();
                $table->string('autor')->nullable();
                $table->integer('anio_publicacion')->nullable();
                $table->integer('area_id')->nullable();
                $table->string('categoria')->nullable();
                $table->string('codigo')->nullable()->unique();
                $table->integer('disponibilidad')->default(0);
                $table->string('editorial')->nullable();
                $table->string('clasificacion_fisica', 50)->nullable();
            });
        }

        if (! Schema::hasTable('prestamos')) {
            Schema::create('prestamos', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('socio_id');
                $table->integer('material_id')->nullable();
                $table->date('fecha_prestamo')->nullable();
                $table->date('fecha_devolucion')->nullable();
                $table->string('estado', 50)->default('activo');
                $table->integer('cantidad')->default(1);
            });
        }

        if (! Schema::hasTable('noticias')) {
            Schema::create('noticias', function (Blueprint $table) {
                $table->integer('id', true);
                $table->string('titulo');
                $table->string('descripcion', 255);
                $table->string('imagen')->nullable();
                $table->dateTime('fecha')->nullable();
            });
        }

        if (! Schema::hasTable('anotaciones')) {
            Schema::create('anotaciones', function (Blueprint $table) {
                $table->integer('id', true);
                $table->string('anotacion', 255);
                $table->timestamp('fecha')->useCurrent();
            });
        }

        if (! Schema::hasTable('historial_socios')) {
            Schema::create('historial_socios', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('id_socio');
                $table->enum('accion', ['ALTA', 'BAJA']);
                $table->dateTime('fecha')->useCurrent();
                $table->text('observaciones')->nullable();
                $table->foreign('id_socio')->references('id')->on('socios')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('notificaciones')) {
            Schema::create('notificaciones', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('prestamo_id');
                $table->string('email')->nullable();
                $table->string('material')->nullable();
                $table->date('fecha_devolucion')->nullable();
                $table->dateTime('fecha_envio')->nullable();
                $table->foreign('prestamo_id')->references('id')->on('prestamos')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('ubicaciones_fisicas')) {
            Schema::create('ubicaciones_fisicas', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('material_id');
                $table->string('pasillo', 10)->nullable();
                $table->unsignedTinyInteger('estante')->nullable();
                $table->unsignedTinyInteger('nivel')->nullable();
                $table->string('codigo_ubicacion')->nullable();
                $table->dateTime('fecha_asignacion')->nullable();
                $table->foreign('material_id')->references('id')->on('materiales')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('libros')) {
            Schema::create('libros', function (Blueprint $table) {
                $table->integer('id', true);
                $table->string('titulo')->nullable();
                $table->string('autor')->nullable();
            });
        }

        if (! Schema::hasTable('prestamos_detalle')) {
            Schema::create('prestamos_detalle', function (Blueprint $table) {
                $table->integer('id', true);
                $table->integer('prestamo_id');
                $table->integer('libro_id')->nullable();
                $table->integer('cantidad_prestada')->default(1);
                $table->integer('cantidad_devuelta')->default(0);
                $table->foreign('prestamo_id')->references('id')->on('prestamos')->cascadeOnDelete();
                $table->foreign('libro_id')->references('id')->on('libros')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('prestamos_detalle');
        Schema::dropIfExists('libros');
        Schema::dropIfExists('ubicaciones_fisicas');
        Schema::dropIfExists('notificaciones');
        Schema::dropIfExists('historial_socios');
        Schema::dropIfExists('anotaciones');
        Schema::dropIfExists('noticias');
        Schema::dropIfExists('prestamos');
        Schema::dropIfExists('materiales');
        Schema::dropIfExists('socios');
        Schema::dropIfExists('bibliotecarios');
        Schema::dropIfExists('areas');
    }
};
