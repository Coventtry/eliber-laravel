<?php

namespace App\Services;

use App\Models\Area;
use App\Models\Material;
use BaconQrCode\Exception\RuntimeException;
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
        $abreviatura = strtoupper($area->Abreviado ?? $area->codigo_dewey);
        return "{$abreviatura}-{$pasillo}-({$tipo}){$estante}-{$nivel}";
    }

    /**
     * Genera o regenera el QR PNG de un material y devuelve su URL pública.
     */
    public function generarQR(Material $material): string
    {
        $material->loadMissing('area');

        $lineas = array_filter([
            mb_strimwidth($material->titulo, 0, 60, '…'),
            $material->autor               ? 'Autor: '  . mb_strimwidth($material->autor, 0, 40, '…') : null,
            $material->area                ? 'Dewey: '  . $material->area->codigo_dewey               : null,
            $material->categoria           ? 'Tipo: '   . $material->categoria                        : null,
            $material->clasificacion_fisica ? 'Ubic: '       . $material->clasificacion_fisica        : null,
            'Ejemplares: ' . ($material->disponibilidad ?? 0),
            'Prestamo: ' . ($material->tipo_prestamo ?: 'Sin especificar'),
        ]);

        $contenido = implode("\n", $lineas);

        try {
            $contenidoQr = QrCode::format('png')->size(400)->errorCorrection('M')->generate($contenido);
            $ruta = "qrcodes/QR_{$material->id}.png";
        } catch (RuntimeException) {
            $contenidoQr = QrCode::format('svg')->size(400)->errorCorrection('M')->generate($contenido);
            $ruta = "qrcodes/QR_{$material->id}.svg";
        }

        Storage::disk('public')->put($ruta, $contenidoQr);

        return Storage::disk('public')->url($ruta);
    }

    /**
     * Devuelve la URL del QR si ya existe, o null.
     */
    public function urlCodigoQr(Material $material): ?string
    {
        foreach (["qrcodes/QR_{$material->id}.png", "qrcodes/QR_{$material->id}.svg"] as $ruta) {
            if (Storage::disk('public')->exists($ruta)) {
                return Storage::disk('public')->url($ruta);
            }
        }

        return null;
    }
}
