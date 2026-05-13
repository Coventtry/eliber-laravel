<?php

namespace App\Services;

use App\Models\Area;
use App\Models\Material;
use App\Models\MaterialEjemplar;
use BaconQrCode\Exception\RuntimeException;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class MaterialService
{
    /**
     * Genera el siguiente código de ejemplar para un material.
     * Formato: {codigo_material}-E{seq_2_digits}  ej: 1300-002-E01
     */
    public function generarCodigoEjemplar(Material $material): string
    {
        $ultimo = MaterialEjemplar::withoutGlobalScopes()
            ->where('material_id', $material->id)
            ->orderByDesc('id')
            ->value('codigo_ejemplar');

        $secuencia = 1;
        if ($ultimo && preg_match('/-E(\d+)$/', $ultimo, $matches)) {
            $secuencia = (int) $matches[1] + 1;
        }

        return $material->codigo.'-E'.str_pad($secuencia, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Crea N ejemplares para un material recién creado.
     */
    public function crearEjemplares(Material $material, int $cantidad): void
    {
        for ($i = 0; $i < $cantidad; $i++) {
            MaterialEjemplar::create([
                'material_id' => $material->id,
                'institucion_id' => $material->institucion_id,
                'codigo_ejemplar' => $this->generarCodigoEjemplar($material),
                'estado' => 'disponible',
            ]);
        }
    }

    /**
     * Sincroniza los ejemplares cuando cambia la disponibilidad en una edición.
     * Agrega ejemplares si aumenta, da de baja disponibles si disminuye.
     */
    public function ajustarEjemplares(Material $material, int $nuevaDisponibilidad): int
    {
        $totalActuales = MaterialEjemplar::withoutGlobalScopes()
            ->where('material_id', $material->id)
            ->whereIn('estado', ['disponible', 'prestado', 'reservado'])
            ->count();

        $delta = $nuevaDisponibilidad - $totalActuales;

        if ($delta > 0) {
            for ($i = 0; $i < $delta; $i++) {
                MaterialEjemplar::create([
                    'material_id' => $material->id,
                    'institucion_id' => $material->institucion_id,
                    'codigo_ejemplar' => $this->generarCodigoEjemplar($material),
                    'estado' => 'disponible',
                ]);
            }
        } elseif ($delta < 0) {
            MaterialEjemplar::withoutGlobalScopes()
                ->where('material_id', $material->id)
                ->where('estado', 'disponible')
                ->orderByDesc('id')
                ->limit(abs($delta))
                ->get()
                ->each(fn ($ej) => $ej->update(['estado' => 'baja']));
        }

        return $nuevaDisponibilidad;
    }

    public function sincronizarDisponibilidad(Material $material): void
    {
        $total = MaterialEjemplar::withoutGlobalScopes()
            ->where('material_id', $material->id)
            ->whereIn('estado', ['disponible', 'prestado', 'reservado'])
            ->count();

        $material->update(['disponibilidad' => $total]);
    }

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

        return $area->codigo_dewey.'-'.str_pad($secuencia, 3, '0', STR_PAD_LEFT);
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
            $material->autor ? 'Autor: '.mb_strimwidth($material->autor, 0, 40, '…') : null,
            $material->area ? 'Dewey: '.$material->area->codigo_dewey : null,
            $material->categoria ? 'Tipo: '.$material->categoria : null,
            $material->clasificacion_fisica ? 'Ubic: '.$material->clasificacion_fisica : null,
            'Ejemplares: '.($material->disponibilidad ?? 0),
            'Prestamo: '.($material->tipo_prestamo ?: 'Sin especificar'),
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
