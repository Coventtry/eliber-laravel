<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AlertaController;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\AnaliticaController;
use App\Http\Controllers\AnotacionController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CategoriaFisicaController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\ContenidoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\NoticiaController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\SocioController;
use App\Http\Controllers\UserController;
use App\Models\Faq;
use Illuminate\Support\Facades\Route;

// Ficha pública de material (accesible sin login — para QR)
Route::get('/materiales/{id}/ficha', [MaterialController::class, 'fichaPublica'])->name('materiales.ficha');

// Landing page - pública
Route::get('/', [LandingController::class, '__invoke'])->name('landing');
Route::get('/acerca', fn () => inertia('Acerca'))->name('acerca');
Route::get('/faqs', function () {
    // Muestra FAQs activas de la primera institución activa (público)
    $faqs = Faq::where('activa', true)
        ->orderBy('orden')->orderBy('id')
        ->get(['id', 'pregunta', 'respuesta']);

    return inertia('FAQs', ['faqs' => $faqs]);
})->name('faqs');

// Auth
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login')->middleware('guest');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('guest');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
Route::post('/register', [RegisterController::class, 'store'])->name('register')->middleware('guest');

// Password reset (público)
Route::get('/reset-password', [PasswordResetController::class, 'showForm'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.reset.submit');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('feedback/nota', [FeedbackController::class, 'storeNota'])->name('feedback.nota');

    Route::resource('socios', SocioController::class);
    Route::patch('socios/{socio}/baja', [SocioController::class, 'baja'])->name('socios.baja');
    Route::patch('socios/{socio}/reactivar', [SocioController::class, 'reactivar'])->name('socios.reactivar');

    Route::resource('materiales', MaterialController::class)->parameters(['materiales' => 'material']);
    Route::get('materiales/{material}/qr', [MaterialController::class, 'qrCode'])->name('materiales.qr');
    Route::get('materiales/{material}/ejemplares', [MaterialController::class, 'ejemplares'])->name('materiales.ejemplares');
    Route::get('api/materiales/ejemplares-disponibles', [MaterialController::class, 'ejemplaresDisponibles'])->name('api.materiales.ejemplares-disponibles');

    Route::resource('areas', AreaController::class);
    Route::resource('categorias', CategoriaFisicaController::class)->except(['show']);

    Route::resource('prestamos', PrestamoController::class)->only(['index', 'create', 'store']);
    Route::patch('prestamos/{prestamo}/devolver', [PrestamoController::class, 'devolver'])->name('prestamos.devolver');
    Route::patch('prestamos/{prestamo}/extender', [PrestamoController::class, 'extender'])->name('prestamos.extender');
    Route::get('prestamos/{prestamo}/devolucion', [PrestamoController::class, 'showDevolucion'])->name('prestamos.devolucion');

    Route::resource('noticias', NoticiaController::class);
    Route::resource('anotaciones', AnotacionController::class)->only(['index', 'create', 'store']);

    Route::resource('usuarios', UserController::class)
        ->parameters(['usuarios' => 'user'])
        ->only(['index', 'edit', 'update']);
    Route::patch('usuarios/{user}/permisos', [UserController::class, 'updatePermisos'])->name('usuarios.permisos');
    Route::patch('usuarios/{user}/toggle-activo', [UserController::class, 'toggleActivo'])->name('usuarios.toggle-activo');
    Route::patch('usuarios/{user}/aprobar', [UserController::class, 'aprobar'])->name('usuarios.aprobar');

    // Alertas
    Route::get('alertas', [AlertaController::class, 'index'])->name('alertas.index');
    Route::patch('alertas/todas-leidas', [AlertaController::class, 'marcarTodasLeidas'])->name('alertas.todas-leidas');
    Route::patch('alertas/{alerta}/leida', [AlertaController::class, 'marcarLeida'])->name('alertas.leida');
    Route::post('alertas/{alerta}/baja-material', [AlertaController::class, 'bajaAlerta'])->name('alertas.baja-material');

    // Admin
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('usuarios', [AdminController::class, 'usuarios'])->name('usuarios');
        Route::post('usuarios', [AdminController::class, 'storeUsuario'])->name('usuarios.store');
        Route::put('usuarios/{user}', [AdminController::class, 'updateUsuario'])->name('usuarios.update');
        Route::delete('usuarios/{user}', [AdminController::class, 'destroyUsuario'])->name('usuarios.destroy');
        Route::patch('usuarios/{user}/toggle-activo', [AdminController::class, 'toggleActivoUsuario'])->name('usuarios.toggle');
        Route::patch('usuarios/{user}/aprobar', [AdminController::class, 'aprobarUsuario'])->name('usuarios.aprobar');

        // Feedback Kanban
        Route::get('feedback', [FeedbackController::class, 'index'])->name('feedback.index');
        Route::post('feedback', [FeedbackController::class, 'store'])->name('feedback.store');
        Route::put('feedback/{feedbackCard}', [FeedbackController::class, 'update'])->name('feedback.update');
        Route::patch('feedback/{feedbackCard}/mover', [FeedbackController::class, 'mover'])->name('feedback.mover');
        Route::delete('feedback/{feedbackCard}', [FeedbackController::class, 'destroy'])->name('feedback.destroy');

        // Contenido
        Route::get('contenido', [ContenidoController::class, 'index'])->name('contenido.index');
        Route::post('contenido/faqs', [ContenidoController::class, 'storeFaq'])->name('contenido.faqs.store');
        Route::put('contenido/faqs/{faq}', [ContenidoController::class, 'updateFaq'])->name('contenido.faqs.update');
        Route::delete('contenido/faqs/{faq}', [ContenidoController::class, 'destroyFaq'])->name('contenido.faqs.destroy');
        Route::post('contenido/footer', [ContenidoController::class, 'storeFooterLink'])->name('contenido.footer.store');
        Route::put('contenido/footer/{footerLink}', [ContenidoController::class, 'updateFooterLink'])->name('contenido.footer.update');
        Route::delete('contenido/footer/{footerLink}', [ContenidoController::class, 'destroyFooterLink'])->name('contenido.footer.destroy');
        Route::patch('contenido/anuncio', [ContenidoController::class, 'updateAnuncio'])->name('contenido.anuncio.update');

        // Analítica
        Route::get('analitica', [AnaliticaController::class, 'index'])->name('analitica.index');

        // Configuración
        Route::get('configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');
        Route::put('configuracion', [ConfiguracionController::class, 'update'])->name('configuracion.update');

        // Selector de institución
        Route::post('switch-institucion', [AdminController::class, 'switchInstitucion'])->name('switch-institucion');
    });

    // Perfil (todos los roles)
    Route::get('perfil', [PerfilController::class, 'edit'])->name('perfil.edit');
    Route::put('perfil', [PerfilController::class, 'update'])->name('perfil.update');

    // Vistas alumno
    Route::middleware('role:alumno')->prefix('alumno')->name('alumno.')->group(function () {
        Route::get('dashboard', [AlumnoController::class, 'dashboard'])->name('dashboard');
        Route::get('mis-reservas', [AlumnoController::class, 'misReservas'])->name('reservas');
        Route::get('catalogo', [AlumnoController::class, 'catalogo'])->name('catalogo');
        Route::post('reservas', [AlumnoController::class, 'reservar'])->name('reservas.store');
        Route::delete('reservas/{reserva}', [AlumnoController::class, 'cancelarReserva'])->name('reservas.cancel');
    });

    // AJAX endpoints
    Route::get('api/socios/buscar', [SocioController::class, 'buscar'])->name('api.socios.buscar');
    Route::get('api/socios/{socio}/prestamos', [SocioController::class, 'prestamosSocio'])->name('api.socios.prestamos');
    Route::get('api/materiales/disponibles', [MaterialController::class, 'disponibles'])->name('api.materiales.disponibles');
    Route::get('api/materiales/ultimo-codigo', [MaterialController::class, 'ultimoCodigo'])->name('api.materiales.ultimo-codigo');
});
