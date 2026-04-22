<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="E-liber - Sistema de Gestión de Biblioteca Escolar">
    <meta property="og:title" content="E-liber - Biblioteca Escolar">
    <meta property="og:description" content="Sistema de Gestión de Biblioteca Escolar">
    <meta property="og:image" content="{{ asset('img/logo.png') }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="icon" type="image/x-icon" href="{{ asset('img/icono.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/icono.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <title>E-liber</title>
    @routes
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
