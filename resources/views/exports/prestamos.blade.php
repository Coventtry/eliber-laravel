<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Préstamos</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 4px 6px; text-align: left; }
        th { background: #eee; }
        h2 { margin-bottom: 6px; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h2>Préstamos</h2>
    <table>
        <thead>
            <tr><th>#</th><th>Socio</th><th>Material</th><th>Inicio</th><th>Vencimiento</th><th>Estado</th><th>Cant.</th></tr>
        </thead>
        <tbody>
            @foreach($rows as $p)
            <tr>
                <td>{{ $p->id }}</td>
                <td>{{ $p->socio?->apellido }}, {{ $p->socio?->nombre }}</td>
                <td>{{ $p->material?->titulo }}</td>
                <td>{{ $p->fecha_prestamo?->format('d/m/Y') }}</td>
                <td>{{ $p->fecha_devolucion?->format('d/m/Y') }}</td>
                <td>{{ $p->estado }}</td>
                <td class="text-right">{{ $p->cantidad }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
