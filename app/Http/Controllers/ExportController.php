<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Multa;
use App\Models\Prestamo;
use App\Models\Socio;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ExportController extends Controller
{
    public function sociosCsv(): Response
    {
        $this->authorize('viewAny', Socio::class);
        $rows = Socio::orderBy('apellido')->get();

        $csv = $this->buildCsv($rows, ['ID', 'Apellido', 'Nombre', 'Email', 'Teléfono', 'Año', 'División', 'Activo'],
            fn($r) => [$r->id, $r->apellido, $r->nombre, $r->email, $r->telefono, $r->anio, $r->division, $r->activo ? 'Sí' : 'No']
        );

        return response($csv, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="socios.csv"']);
    }

    public function sociosPdf(): Response
    {
        $this->authorize('viewAny', Socio::class);
        $rows = Socio::orderBy('apellido')->get();
        $pdf = Pdf::loadView('exports.socios', compact('rows'));
        return $pdf->download('socios.pdf');
    }

    public function materialesCsv(): Response
    {
        $this->authorize('viewAny', Material::class);
        $rows = Material::with('area')->orderBy('codigo')->get();

        $csv = $this->buildCsv($rows, ['ID', 'Código', 'Título', 'Autor', 'Área', 'Categoría', 'Disponibilidad'],
            fn($r) => [$r->id, $r->codigo, $r->titulo, $r->autor, $r->area?->nombre ?? '-', $r->categoria, $r->disponibilidad]
        );

        return response($csv, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="materiales.csv"']);
    }

    public function materialesPdf(): Response
    {
        $this->authorize('viewAny', Material::class);
        $rows = Material::with('area')->orderBy('codigo')->get();
        $pdf = Pdf::loadView('exports.materiales', compact('rows'));
        return $pdf->download('materiales.pdf');
    }

    public function prestamosCsv(): Response
    {
        $this->authorize('viewAny', Prestamo::class);
        $rows = Prestamo::with('socio', 'material')->orderByDesc('id')->get();

        $csv = $this->buildCsv($rows, ['ID', 'Socio', 'Material', 'Inicio', 'Vencimiento', 'Estado', 'Cantidad'],
            fn($r) => [
                $r->id,
                ($r->socio?->apellido ?? '') . ', ' . ($r->socio?->nombre ?? ''),
                $r->material?->titulo ?? '-',
                $r->fecha_prestamo?->format('d/m/Y'),
                $r->fecha_devolucion?->format('d/m/Y'),
                $r->estado,
                $r->cantidad,
            ]
        );

        return response($csv, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="prestamos.csv"']);
    }

    public function prestamosPdf(): Response
    {
        $this->authorize('viewAny', Prestamo::class);
        $rows = Prestamo::with('socio', 'material')->orderByDesc('id')->get();
        $pdf = Pdf::loadView('exports.prestamos', compact('rows'));
        return $pdf->download('prestamos.pdf');
    }

    public function multasCsv(): Response
    {
        $this->authorize('viewAny', Multa::class);
        $rows = Multa::with('socio')->orderByDesc('fecha_creacion')->get();

        $csv = $this->buildCsv($rows, ['ID', 'Socio', 'Monto', 'Motivo', 'Fecha', 'Pagada'],
            fn($r) => [
                $r->id,
                ($r->socio?->apellido ?? '') . ', ' . ($r->socio?->nombre ?? ''),
                number_format($r->monto, 2),
                $r->motivo,
                $r->fecha_creacion instanceof \Carbon\Carbon ? $r->fecha_creacion->format('d/m/Y') : $r->fecha_creacion,
                $r->pagada ? 'Sí' : 'No',
            ]
        );

        return response($csv, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="multas.csv"']);
    }

    public function multasPdf(): Response
    {
        $this->authorize('viewAny', Multa::class);
        $rows = Multa::with('socio')->orderByDesc('fecha_creacion')->get();
        $pdf = Pdf::loadView('exports.multas', compact('rows'));
        return $pdf->download('multas.pdf');
    }

    private function buildCsv(iterable $rows, array $headers, callable $mapper): string
    {
        $out = fopen('php://temp', 'r+');
        fputcsv($out, $headers);
        foreach ($rows as $row) {
            fputcsv($out, $mapper($row));
        }
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);
        return $csv;
    }
}
