<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\SocioController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\NoticiaController;
use App\Http\Controllers\AnotacionController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Auth
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login')->middleware('guest');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('guest');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('socios', SocioController::class);
    Route::patch('socios/{socio}/baja', [SocioController::class, 'baja'])->name('socios.baja');
    Route::patch('socios/{socio}/reactivar', [SocioController::class, 'reactivar'])->name('socios.reactivar');

    Route::resource('materiales', MaterialController::class);
    Route::get('materiales/{material}/qr', [MaterialController::class, 'qrCode'])->name('materiales.qr');

    Route::resource('areas', AreaController::class);

    Route::resource('prestamos', PrestamoController::class)->only(['index', 'create', 'store']);
    Route::patch('prestamos/{prestamo}/devolver', [PrestamoController::class, 'devolver'])->name('prestamos.devolver');
    Route::patch('prestamos/{prestamo}/extender', [PrestamoController::class, 'extender'])->name('prestamos.extender');
    Route::get('prestamos/{prestamo}/devolucion', [PrestamoController::class, 'showDevolucion'])->name('prestamos.devolucion');

    Route::resource('noticias', NoticiaController::class);
    Route::resource('anotaciones', AnotacionController::class)->only(['index', 'create', 'store']);

    // AJAX endpoints
    Route::get('api/socios/buscar', [SocioController::class, 'buscar'])->name('api.socios.buscar');
    Route::get('api/materiales/disponibles', [MaterialController::class, 'disponibles'])->name('api.materiales.disponibles');
    Route::get('api/materiales/ultimo-codigo', [MaterialController::class, 'ultimoCodigo'])->name('api.materiales.ultimo-codigo');
});
