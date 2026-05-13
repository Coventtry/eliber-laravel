<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Materiales</title>
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
    <h2>Materiales</h2>
    <table>
        <thead>
            <tr><th>#</th><th>Código</th><th>Título</th><th>Autor</th><th>Área</th><th>Categoría</th><th>Disp.</th></tr>
        </thead>
        <tbody>
            @foreach($rows as $m)
            <tr>
                <td>{{ $m->id }}</td>
                <td>{{ $m->codigo }}</td>
                <td>{{ $m->titulo }}</td>
                <td>{{ $m->autor }}</td>
                <td>{{ $m->area?->nombre ?? '-' }}</td>
                <td>{{ $m->categoria }}</td>
                <td class="text-right">{{ $m->disponibilidad }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
