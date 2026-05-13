<?php

namespace Tests\Unit;

use App\Models\Area;
use App\Models\Institucion;
use App\Models\Material;
use App\Models\MaterialEjemplar;
use App\Services\MaterialService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaterialServiceTest extends TestCase
{
    use RefreshDatabase;

    private MaterialService $service;
    private Area $area;
    private Material $material;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(MaterialService::class);

        $institucion = Institucion::create([
            'nombre' => 'Test Inst',
            'slug' => 'test-inst',
            'estado' => 'activa',
        ]);

        $this->area = Area::forceCreate([
            'codigo_dewey' => '300',
            'nombre' => 'Ciencias Sociales',
            'Abreviado' => 'SOC',
            'institucion_id' => $institucion->id,
        ]);

        $this->material = Material::forceCreate([
            'titulo' => 'Test Material',
            'autor' => 'Autor',
            'anio_publicacion' => 2020,
            'area_id' => $this->area->id,
            'categoria' => 'LIBRO',
            'codigo' => '300-001',
            'disponibilidad' => 2,
            'disponibilidad_reservada' => 0,
            'editorial' => 'Test',
            'clasificacion_fisica' => 'SOC-A-(E)1-1',
            'institucion_id' => $institucion->id,
        ]);
    }

    public function test_generar_codigo_secuencia(): void
    {
        // setUp creates material with codigo '300-001'
        // generarCodigo finds the latest by id and increments suffix
        $codigo = $this->service->generarCodigo($this->area);

        $this->assertSame('300-002', $codigo);
    }

    public function test_generar_clasificacion_fisica(): void
    {
        $clasif = $this->service->generarClasificacionFisica($this->area, 'A', 'E', 1, 2);

        $this->assertSame('SOC-A-(E)1-2', $clasif);
    }

    public function test_crear_ejemplares(): void
    {
        $this->service->crearEjemplares($this->material, 3);

        $count = MaterialEjemplar::withoutGlobalScopes()
            ->where('material_id', $this->material->id)
            ->count();

        $this->assertSame(3, $count);
    }

    public function test_generar_codigo_ejemplar(): void
    {
        MaterialEjemplar::forceCreate([
            'material_id' => $this->material->id,
            'institucion_id' => $this->material->institucion_id,
            'codigo_ejemplar' => '300-001-E01',
            'estado' => 'disponible',
        ]);

        $codigo = $this->service->generarCodigoEjemplar($this->material);

        $this->assertSame('300-001-E02', $codigo);
    }

    public function test_ajustar_ejemplares_agrega_cuando_aumenta(): void
    {
        MaterialEjemplar::forceCreate([
            'material_id' => $this->material->id,
            'institucion_id' => $this->material->institucion_id,
            'codigo_ejemplar' => '300-001-E01',
            'estado' => 'disponible',
        ]);

        $this->service->ajustarEjemplares($this->material, 3);

        $count = MaterialEjemplar::withoutGlobalScopes()
            ->where('material_id', $this->material->id)
            ->where('estado', 'disponible')
            ->count();

        $this->assertSame(3, $count);
    }

    public function test_ajustar_ejemplares_da_baja_cuando_disminuye(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            MaterialEjemplar::forceCreate([
                'material_id' => $this->material->id,
                'institucion_id' => $this->material->institucion_id,
                'codigo_ejemplar' => "300-001-E".str_pad($i, 2, '0', STR_PAD_LEFT),
                'estado' => 'disponible',
            ]);
        }

        $this->service->ajustarEjemplares($this->material, 1);

        $bajas = MaterialEjemplar::withoutGlobalScopes()
            ->where('material_id', $this->material->id)
            ->where('estado', 'baja')
            ->count();

        $this->assertSame(2, $bajas);
    }
}
