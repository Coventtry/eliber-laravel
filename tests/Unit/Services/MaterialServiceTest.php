<?php

namespace Tests\Unit\Services;

use App\Models\Area;
use App\Models\Institucion;
use App\Models\Material;
use App\Services\MaterialService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MaterialServiceTest extends TestCase
{
    use RefreshDatabase;

    private MaterialService $service;
    private Area $area;
    private Institucion $institucion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->institucion = Institucion::factory()->create();
        $this->area = Area::factory()->create(['institucion_id' => $this->institucion->id]);
        $this->service = $this->app->make(MaterialService::class);
    }

    public function test_genera_codigo_first(): void
    {
        $codigo = $this->service->generarCodigo($this->area);

        $this->assertStringStartsWith($this->area->codigo_dewey . '-', $codigo);
        $this->assertStringEndsWith('-001', $codigo);
    }

    public function test_genera_codigo_incremental(): void
    {
        Material::factory()->create([
            'area_id' => $this->area->id,
            'codigo' => $this->area->codigo_dewey . '-005',
            'institucion_id' => $this->institucion->id,
        ]);

        $codigo = $this->service->generarCodigo($this->area);

        $this->assertEquals($this->area->codigo_dewey . '-006', $codigo);
    }

    public function test_genera_clasificacion_fisica(): void
    {
        $clasificacion = $this->service->generarClasificacionFisica($this->area, 'A', 'E', 1, 1);

        $abrev = strtoupper($this->area->Abreviado);
        $this->assertEquals("{$abrev}-A-(E)1-1", $clasificacion);
    }

    public function test_devuelve_null_cuando_no_hay_qr(): void
    {
        Storage::fake('public');

        $material = Material::factory()->create(['institucion_id' => $this->institucion->id]);

        $this->assertNull($this->service->urlCodigoQr($material));
    }

    public function test_genera_qr_y_devuelve_url(): void
    {
        Storage::fake('public');

        $material = Material::factory()->create(['institucion_id' => $this->institucion->id]);
        $url = $this->service->generarQR($material);

        $this->assertStringContainsString('qrcodes/QR_' . $material->id, $url);
    }
}
