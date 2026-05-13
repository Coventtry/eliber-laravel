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
        Route::post('materiales', [MaterialController::class, 'store'])->name('materiales.store');
        Route::put('materiales/{material}', [MaterialController::class, 'update'])->name('materiales.update');
        Route::delete('materiales/{material}', [MaterialController::class, 'destroy'])->name('materiales.destroy');

        // Noticias
        Route::post('noticias', [NoticiaController::class, 'store'])->name('noticias.store');
        Route::put('noticias/{noticia}', [NoticiaController::class, 'update'])->name('noticias.update');
        Route::delete('noticias/{noticia}', [NoticiaController::class, 'destroy'])->name('noticias.destroy');

        // Socios
        Route::get('socios', [SocioController::class, 'index'])->name('socios.index');
        Route::get('socios/{socio}', [SocioController::class, 'show'])->name('socios.show');
        Route::post('socios', [SocioController::class, 'store'])->name('socios.store');
        Route::put('socios/{socio}', [SocioController::class, 'update'])->name('socios.update');
        Route::delete('socios/{socio}', [SocioController::class, 'destroy'])->name('socios.destroy');
        Route::patch('socios/{socio}/baja', [SocioController::class, 'baja'])->name('socios.baja');
        Route::patch('socios/{socio}/reactivar', [SocioController::class, 'reactivar'])->name('socios.reactivar');

        // Préstamos
        Route::get('prestamos', [PrestamoController::class, 'index'])->name('prestamos.index');
        Route::get('prestamos/{prestamo}', [PrestamoController::class, 'show'])->name('prestamos.show');
        Route::post('prestamos', [PrestamoController::class, 'store'])->name('prestamos.store');
        Route::patch('prestamos/{prestamo}/devolver', [PrestamoController::class, 'devolver'])->name('prestamos.devolver');
        Route::patch('prestamos/{prestamo}/extender', [PrestamoController::class, 'extender'])->name('prestamos.extender');

        // Reservas
        Route::get('reservas', [ReservaController::class, 'index'])->name('reservas.index');
        Route::post('reservas', [ReservaController::class, 'store'])->name('reservas.store');
        Route::delete('reservas/{reserva}', [ReservaController::class, 'destroy'])->name('reservas.destroy');
        Route::patch('reservas/{reserva}/aprobar', [ReservaController::class, 'aprobar'])->name('reservas.aprobar');
        Route::patch('reservas/{reserva}/rechazar', [ReservaController::class, 'rechazar'])->name('reservas.rechazar');

        // Áreas
        Route::get('areas', [AreaController::class, 'index'])->name('areas.index');
        Route::get('areas/{area}', [AreaController::class, 'show'])->name('areas.show');
        Route::post('areas', [AreaController::class, 'store'])->name('areas.store');
        Route::put('areas/{area}', [AreaController::class, 'update'])->name('areas.update');
        Route::delete('areas/{area}', [AreaController::class, 'destroy'])->name('areas.destroy');

        // Alertas
        Route::get('alertas', [AlertaController::class, 'index'])->name('alertas.index');
        Route::patch('alertas/todas-leidas', [AlertaController::class, 'marcarTodasLeidas'])->name('alertas.todas-leidas');
        Route::patch('alertas/{alerta}/leida', [AlertaController::class, 'marcarLeida'])->name('alertas.leida');

        // Multas
        Route::get('multas', [MultaController::class, 'index'])->name('multas.index');
        Route::post('multas', [MultaController::class, 'store'])->name('multas.store');
        Route::patch('multas/{multa}/pagar', [MultaController::class, 'pagar'])->name('multas.pagar');
        Route::patch('multas/{multa}/perdonar', [MultaController::class, 'perdonar'])->name('multas.perdonar');

        // Usuarios
        Route::get('usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::get('usuarios/{user}', [UsuarioController::class, 'show'])->name('usuarios.show');
        Route::post('usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::put('usuarios/{user}', [UsuarioController::class, 'update'])->name('usuarios.update');
        Route::delete('usuarios/{user}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');
        Route::patch('usuarios/{user}/permisos', [UsuarioController::class, 'updatePermisos'])->name('usuarios.permisos');
        Route::patch('usuarios/{user}/toggle-activo', [UsuarioController::class, 'toggleActivo'])->name('usuarios.toggle-activo');
    });
});
