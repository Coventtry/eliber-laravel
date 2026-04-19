<?php

namespace App\Services;

use App\Models\Area;
use App\Models\Material;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class MaterialService
{
    /**
     * Genera el siguiente código correlativo para un área.
     * Formato: {codigo_dewey}-{seq_3_digits}  ej: 1300-002
     */
    public function generarCodigo(Area $area): string
    {
        $ultimo = Material::where('area_id', $area->id)
            ->whereNotNull('codigo')
            ->orderByDesc('id')
            ->value('codigo');

        $secuencia = 1;
        if ($ultimo && preg_match('/-(\d+)$/', $ultimo, $matches)) {
            $secuencia = (int) $matches[1] + 1;
        }

        return $area->codigo_dewey . '-' . str_pad($secuencia, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Genera el código de clasificación física.
     * Formato: {AREA_ABREV}-{PASILLO}-({TIPO}){ESTANTE}-{NIVEL}
     */
    public function generarClasificacionFisica(Area $area, string $pasillo, string $tipo, int $estante, int $nivel): string
    {
        $abrev = strtoupper($area->Abreviado ?? $area->codigo_dewey);
        return "{$abrev}-{$pasillo}-({$tipo}){$estante}-{$nivel}";
    }

    /**
     * Genera o regenera el QR PNG de un material y devuelve su URL pública.
     */
    public function generarQR(Material $material): string
    {
        $contenido = implode("\n", [
            "Título: {$material->titulo}",
            "Autor: {$material->autor}",
            "Año: {$material->anio_publicacion}",
            "Categoría: {$material->categoria}",
            "Código: {$material->codigo}",
        ]);

        $png  = QrCode::format('png')->size(300)->errorCorrection('H')->generate($contenido);
        $path = "qrcodes/QR_{$material->id}.png";

        Storage::disk('public')->put($path, $png);

        return Storage::disk('public')->url($path);
    }

    /**
     * Devuelve la URL del QR si ya existe, o null.
     */
    public function urlQR(Material $material): ?string
    {
        $path = "qrcodes/QR_{$material->id}.png";
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }
        return null;
    }
}
