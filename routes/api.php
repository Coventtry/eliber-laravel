<?php

use App\Http\Controllers\Api\AlertaController;
use App\Http\Controllers\Api\AreaController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\NoticiaController;
use App\Http\Controllers\Api\PrestamoController;
use App\Http\Controllers\Api\ReservaController;
use App\Http\Controllers\Api\SocioController;
use App\Http\Controllers\Api\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Endpoints públicos (con throttle suave)
    Route::middleware('throttle:30,1')->group(function () {
        Route::get('materiales', [MaterialController::class, 'index']);
        Route::get('materiales/{material}', [MaterialController::class, 'show']);
        Route::get('noticias', [NoticiaController::class, 'index']);
    });

    // Endpoints autenticados (Sanctum) — throttle más permisivo
    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
        // Materiales
        Route::post('materiales', [MaterialController::class, 'store']);
        Route::put('materiales/{material}', [MaterialController::class, 'update']);
        Route::delete('materiales/{material}', [MaterialController::class, 'destroy']);

        // Noticias
        Route::post('noticias', [NoticiaController::class, 'store']);
        Route::put('noticias/{noticia}', [NoticiaController::class, 'update']);
        Route::delete('noticias/{noticia}', [NoticiaController::class, 'destroy']);

        // Socios
        Route::get('socios', [SocioController::class, 'index']);
        Route::get('socios/{socio}', [SocioController::class, 'show']);
        Route::post('socios', [SocioController::class, 'store']);
        Route::put('socios/{socio}', [SocioController::class, 'update']);
        Route::delete('socios/{socio}', [SocioController::class, 'destroy']);
        Route::patch('socios/{socio}/baja', [SocioController::class, 'baja']);
        Route::patch('socios/{socio}/reactivar', [SocioController::class, 'reactivar']);

        // Préstamos
        Route::get('prestamos', [PrestamoController::class, 'index']);
        Route::get('prestamos/{prestamo}', [PrestamoController::class, 'show']);
        Route::post('prestamos', [PrestamoController::class, 'store']);
        Route::patch('prestamos/{prestamo}/devolver', [PrestamoController::class, 'devolver']);
        Route::patch('prestamos/{prestamo}/extender', [PrestamoController::class, 'extender']);

        // Reservas
        Route::get('reservas', [ReservaController::class, 'index']);
        Route::post('reservas', [ReservaController::class, 'store']);
        Route::delete('reservas/{reserva}', [ReservaController::class, 'destroy']);
        Route::patch('reservas/{reserva}/aprobar', [ReservaController::class, 'aprobar']);
        Route::patch('reservas/{reserva}/rechazar', [ReservaController::class, 'rechazar']);

        // Áreas
        Route::get('areas', [AreaController::class, 'index']);
        Route::get('areas/{area}', [AreaController::class, 'show']);
        Route::post('areas', [AreaController::class, 'store']);
        Route::put('areas/{area}', [AreaController::class, 'update']);
        Route::delete('areas/{area}', [AreaController::class, 'destroy']);

        // Alertas
        Route::get('alertas', [AlertaController::class, 'index']);
        Route::patch('alertas/todas-leidas', [AlertaController::class, 'marcarTodasLeidas']);
        Route::patch('alertas/{id}/leida', [AlertaController::class, 'marcarLeida']);

        // Usuarios
        Route::get('usuarios', [UsuarioController::class, 'index']);
        Route::get('usuarios/{id}', [UsuarioController::class, 'show']);
        Route::post('usuarios', [UsuarioController::class, 'store']);
        Route::put('usuarios/{id}', [UsuarioController::class, 'update']);
        Route::delete('usuarios/{id}', [UsuarioController::class, 'destroy']);
        Route::patch('usuarios/{id}/permisos', [UsuarioController::class, 'updatePermisos']);
        Route::patch('usuarios/{id}/toggle-activo', [UsuarioController::class, 'toggleActivo']);
    });
});
