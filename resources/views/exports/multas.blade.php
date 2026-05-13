<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Multas</title>
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
    <h2>Multas</h2>
    <table>
        <thead>
            <tr><th>#</th><th>Socio</th><th>Monto</th><th>Motivo</th><th>Fecha</th><th>Estado</th></tr>
        </thead>
        <tbody>
            @foreach($rows as $m)
            <tr>
                <td>{{ $m->id }}</td>
                <td>{{ $m->socio?->apellido }}, {{ $m->socio?->nombre }}</td>
                <td class="text-right">${{ number_format($m->monto, 2) }}</td>
                <td>{{ $m->motivo }}</td>
                <td>{{ $m->fecha_creacion instanceof \Carbon\Carbon ? $m->fecha_creacion->format('d/m/Y') : $m->fecha_creacion }}</td>
                <td>{{ $m->pagada ? 'Pagada' : 'Pendiente' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
