<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Socios</title>
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
    <h2>Socios</h2>
    <table>
        <thead>
            <tr><th>#</th><th>Apellido</th><th>Nombre</th><th>Email</th><th>Teléfono</th><th>Año</th><th>Div.</th><th>Activo</th></tr>
        </thead>
        <tbody>
            @foreach($rows as $s)
            <tr>
                <td>{{ $s->id }}</td>
                <td>{{ $s->apellido }}</td>
                <td>{{ $s->nombre }}</td>
                <td>{{ $s->email }}</td>
                <td>{{ $s->telefono }}</td>
                <td class="text-right">{{ $s->anio }}</td>
                <td class="text-right">{{ $s->division }}</td>
                <td>{{ $s->activo ? 'Sí' : 'No' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
