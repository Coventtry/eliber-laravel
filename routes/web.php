<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\SocioController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\NoticiaController;
use App\Http\Controllers\AnotacionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AlertaController;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\PerfilController;
use Illuminate\Support\Facades\Route;

// Landing page - pública
Route::get('/', [LandingController::class, '__invoke'])->name('landing');
Route::get('/acerca', fn() => inertia('Acerca'))->name('acerca');
Route::get('/faqs', fn() => inertia('FAQs'))->name('faqs');

// Auth
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login')->middleware('guest');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('guest');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

// Password reset (público)
Route::get('/reset-password', [PasswordResetController::class, 'showForm'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.reset.submit');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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

    Route::resource('usuarios', UserController::class)
        ->parameters(['usuarios' => 'user'])
        ->only(['index', 'create', 'store', 'edit', 'update']);
    Route::patch('usuarios/{user}/permisos', [UserController::class, 'updatePermisos'])->name('usuarios.permisos');
    Route::patch('usuarios/{user}/toggle-activo', [UserController::class, 'toggleActivo'])->name('usuarios.toggle-activo');

    // Alertas
    Route::get('alertas', [AlertaController::class, 'index'])->name('alertas.index');
    Route::patch('alertas/todas-leidas', [AlertaController::class, 'marcarTodasLeidas'])->name('alertas.todas-leidas');
    Route::patch('alertas/{alerta}/leida', [AlertaController::class, 'marcarLeida'])->name('alertas.leida');
    Route::post('alertas/{alerta}/baja-material', [AlertaController::class, 'bajaAlerta'])->name('alertas.baja-material');

    // Perfil (todos los roles)
    Route::get('perfil', [PerfilController::class, 'edit'])->name('perfil.edit');
    Route::put('perfil', [PerfilController::class, 'update'])->name('perfil.update');

    // Vistas alumno
    Route::get('alumno/dashboard', [AlumnoController::class, 'dashboard'])->name('alumno.dashboard');
    Route::get('alumno/mis-reservas', [AlumnoController::class, 'misReservas'])->name('alumno.reservas');
    Route::get('alumno/catalogo', [AlumnoController::class, 'catalogo'])->name('alumno.catalogo');

    // AJAX endpoints
    Route::get('api/socios/buscar', [SocioController::class, 'buscar'])->name('api.socios.buscar');
    Route::get('api/materiales/disponibles', [MaterialController::class, 'disponibles'])->name('api.materiales.disponibles');
    Route::get('api/materiales/ultimo-codigo', [MaterialController::class, 'ultimoCodigo'])->name('api.materiales.ultimo-codigo');
});
