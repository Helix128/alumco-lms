<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\ModuloController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\MisCertificadosController;
use App\Http\Controllers\Capacitador\DashboardController as CapacitadorDashboard;
use App\Http\Controllers\Capacitador\CursoController as CapacitadorCurso;
use App\Http\Controllers\Capacitador\ModuloController as CapacitadorModulo;
use App\Http\Controllers\Capacitador\ParticipanteController as CapacitadorParticipante;
use App\Http\Controllers\Capacitador\CertificadoController as CapacitadorCertificado;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Password Reset
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Redirección / según rol
    Route::get('/', function () {
        if (auth()->user()->hasAdminAccess()) return redirect()->route('admin.reportes.index');
        if (auth()->user()->isCapacitador())  return redirect()->route('capacitador.dashboard');
        return redirect()->route('cursos.index');
    });

    // --- RUTAS COLABORADOR ---
    Route::get('/cursos', [CursoController::class, 'index'])->name('cursos.index');
    Route::get('/cursos/{curso}', [CursoController::class, 'show'])->name('cursos.show');
    Route::get('/cursos/{curso}/modulos/{modulo}', [ModuloController::class, 'show'])->name('modulos.show');
    Route::post('/cursos/{curso}/modulos/{modulo}/completar', [ModuloController::class, 'completar'])->name('modulos.completar');
    Route::get('/calendario', \App\Livewire\Capacitador\CalendarioCapacitaciones::class)->name('calendario.index');

    // Perfil del colaborador
    Route::get('/perfil', [PerfilController::class, 'show'])->name('perfil.index');

    // Mis certificados (reemplaza Ajustes en el nav)
    Route::get('/mis-certificados', [MisCertificadosController::class, 'index'])->name('mis-certificados.index');
    Route::get('/mis-certificados/{certificado}/descargar', [MisCertificadosController::class, 'descargar'])->name('mis-certificados.descargar');

    // Redirige legacy /ajustes → /mis-certificados
    Route::get('/ajustes', fn () => redirect()->route('mis-certificados.index'))->name('ajustes.index');
    
    // --- RUTAS CAPACITADOR ---
    Route::middleware(['capacitador'])->prefix('capacitador')->name('capacitador.')->group(function () {
        Route::get('/', [CapacitadorDashboard::class, 'index'])->name('dashboard');

        // Cursos propios
        Route::get('/cursos', [CapacitadorCurso::class, 'index'])->name('cursos.index');
        Route::get('/cursos/crear', [CapacitadorCurso::class, 'create'])->name('cursos.crear');
        Route::post('/cursos', [CapacitadorCurso::class, 'store'])->name('cursos.store');
        Route::get('/cursos/{curso}', [CapacitadorCurso::class, 'show'])->name('cursos.show');
        Route::get('/cursos/{curso}/editar', [CapacitadorCurso::class, 'edit'])->name('cursos.editar');
        Route::put('/cursos/{curso}', [CapacitadorCurso::class, 'update'])->name('cursos.update');
        Route::delete('/cursos/{curso}', [CapacitadorCurso::class, 'destroy'])->name('cursos.destroy');

        // Módulos
        Route::get('/cursos/{curso}/modulos/crear', [CapacitadorModulo::class, 'create'])->name('cursos.modulos.crear');
        Route::post('/cursos/{curso}/modulos', [CapacitadorModulo::class, 'store'])->name('cursos.modulos.store');
        Route::get('/cursos/{curso}/modulos/{modulo}/editar', [CapacitadorModulo::class, 'edit'])->name('cursos.modulos.editar');
        Route::put('/cursos/{curso}/modulos/{modulo}', [CapacitadorModulo::class, 'update'])->name('cursos.modulos.update');
        Route::delete('/cursos/{curso}/modulos/{modulo}', [CapacitadorModulo::class, 'destroy'])->name('cursos.modulos.destroy');
        Route::get('/cursos/{curso}/modulos/{modulo}/evaluacion', [CapacitadorModulo::class, 'evaluacion'])->name('cursos.modulos.evaluacion');
        Route::post('/cursos/{curso}/modulos/reordenar', [CapacitadorModulo::class, 'reordenar'])->name('cursos.modulos.reordenar');

        // Participantes
        Route::get('/cursos/{curso}/participantes', [CapacitadorParticipante::class, 'index'])->name('cursos.participantes.index');

        // Certificados
        Route::post('/cursos/{curso}/certificados/{user}', [CapacitadorCertificado::class, 'generar'])->name('certificados.generar');
        Route::get('/certificados/{certificado}/descargar', [CapacitadorCertificado::class, 'descargar'])->name('certificados.descargar');

        // Solo Capacitador Interno
        Route::middleware(['capacitador.interno'])->group(function () {
            Route::post('/cursos/{curso}/estamentos', [CapacitadorParticipante::class, 'syncEstamentos'])->name('cursos.estamentos.sync');
            Route::get('/cursos/{curso}/participantes/exportar', [CapacitadorParticipante::class, 'exportar'])->name('cursos.participantes.exportar');
        });
    });

    // RUTAS DE ADMINISTRACIÓN
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        // Reportes
        Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
        Route::get('/reportes/exportar', [ReporteController::class, 'exportar'])->name('reportes.exportar');

        // Usuarios
        Route::get('/usuarios', [UserController::class, 'index'])->name('usuarios.index');
        Route::post('/usuarios', [UserController::class, 'store'])->name('usuarios.store');
        Route::put('/usuarios/{user}', [UserController::class, 'update'])->name('usuarios.update');
        Route::delete('/usuarios/{user}', [UserController::class, 'destroy'])->name('usuarios.destroy');
        Route::patch('/usuarios/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('usuarios.toggle-status');
        Route::patch('/usuarios/{user}/reset-password', [UserController::class, 'resetPassword'])->name('usuarios.reset-password');
        
        // Futuras rutas de Estamentos y Sedes se añadirán aquí.
    });
});