<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Capacitador\CertificadoController as CapacitadorCertificado;
use App\Http\Controllers\Capacitador\CursoController as CapacitadorCurso;
use App\Http\Controllers\Capacitador\DashboardController as CapacitadorDashboard;
use App\Http\Controllers\Capacitador\EstadisticasController as CapacitadorEstadisticas;
use App\Http\Controllers\Capacitador\ModuloController as CapacitadorModulo;
use App\Http\Controllers\Capacitador\ParticipanteController as CapacitadorParticipante;
use App\Http\Controllers\Capacitador\SeccionCursoController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\DevHealthController;
use App\Http\Controllers\MisCertificadosController;
use App\Http\Controllers\ModuloController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\SupportTicketAttachmentController;
use App\Http\Controllers\VerificarCertificadoController;
use App\Livewire\CalendarioUsuario;
use App\Livewire\Capacitador\CalendarioCapacitaciones;
use App\Models\SupportTicket;
use App\Support\UserAreaRedirector;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:30,1')->group(function () {
    Route::get('/certificados/verificar', [VerificarCertificadoController::class, 'index'])->name('certificados.verificar.index');
    Route::get('/certificados/verificar/{codigo}', [VerificarCertificadoController::class, 'show'])->name('certificados.verificar.show');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

    Route::view('/soporte-publico', 'support.public-create')->name('support.public.create');

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
        return redirect()->route(UserAreaRedirector::canonicalRouteName(auth()->user()));
    });

    // --- RUTAS COLABORADOR ---
    Route::middleware('worker.area')->group(function () {
        Route::get('/cursos', [CursoController::class, 'index'])->name('cursos.index');
        Route::get('/cursos/{curso}', [CursoController::class, 'show'])->name('cursos.show');
        Route::get('/cursos/{curso}/modulos/{modulo}', [ModuloController::class, 'show'])->name('modulos.show');
        Route::get('/cursos/{curso}/modulos/{modulo}/archivo', [ModuloController::class, 'verArchivo'])->name('modulos.archivo');
        Route::get('/cursos/{curso}/modulos/{modulo}/descargar', [ModuloController::class, 'descargarArchivo'])->name('modulos.descargar');
        Route::post('/cursos/{curso}/modulos/{modulo}/completar', [ModuloController::class, 'completar'])->name('modulos.completar');
        Route::get('/calendario-cursos', CalendarioUsuario::class)->name('calendario-cursos.index');

        // Perfil del colaborador
        Route::get('/perfil', [PerfilController::class, 'show'])->name('perfil.index');

        // Soporte técnico de usuario autenticado
        Route::view('/soporte', 'support.index')->name('support.index');
        Route::get('/soporte/{ticket}', function (SupportTicket $ticket) {
            abort_unless(auth()->user()?->can('view', $ticket), 403);

            return view('support.show', [
                'ticket' => $ticket->load([
                    'attachments',
                    'messages' => fn ($query) => $query->with('author')->latest(),
                ]),
            ]);
        })->name('support.show');
    });

    Route::get('/soporte/adjuntos/{attachment}', SupportTicketAttachmentController::class)
        ->name('support.attachments.download');

    // --- MODO VISTA PREVIA (Admin/Dev) ---
    Route::post('/admin/preview-mode/toggle', function () {
        if (! auth()->user()->hasAdminAccess() && ! auth()->user()->isCapacitador()) {
            abort(403);
        }

        $current = session('preview_mode', false);
        session(['preview_mode' => ! $current]);

        if (session('preview_mode')) {
            return redirect()->route('cursos.index')->with('success', 'Modo vista previa activado.');
        }

        return redirect()
            ->route(UserAreaRedirector::userAreaFallbackRouteName(auth()->user()))
            ->with('success', auth()->user()->hasAdminAccess()
                ? 'Has vuelto al Panel de Administración.'
                : 'Has vuelto al Panel del Capacitador.');
    })->name('admin.preview.toggle');

    Route::middleware('worker.area')->group(function () {
        // Mis certificados (reemplaza Ajustes en el nav)
        Route::get('/mis-certificados', [MisCertificadosController::class, 'index'])->name('mis-certificados.index');
        Route::get('/mis-certificados/{certificado}/descargar', [MisCertificadosController::class, 'descargar'])->name('mis-certificados.descargar');

        // Redirige legacy /ajustes → /mis-certificados
        Route::get('/ajustes', fn () => redirect()->route('mis-certificados.index'))->name('ajustes.index');
    });

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
        Route::post('/cursos/{curso}/duplicar', [CapacitadorCurso::class, 'duplicar'])->name('cursos.duplicar');

        // Módulos
        Route::get('/cursos/{curso}/modulos/crear', [CapacitadorModulo::class, 'create'])->name('cursos.modulos.crear');
        Route::post('/cursos/{curso}/modulos', [CapacitadorModulo::class, 'store'])->name('cursos.modulos.store');
        Route::get('/cursos/{curso}/modulos/{modulo}/editar', [CapacitadorModulo::class, 'edit'])->name('cursos.modulos.editar');
        Route::put('/cursos/{curso}/modulos/{modulo}', [CapacitadorModulo::class, 'update'])->name('cursos.modulos.update');
        Route::delete('/cursos/{curso}/modulos/{modulo}', [CapacitadorModulo::class, 'destroy'])->name('cursos.modulos.destroy');
        Route::get('/cursos/{curso}/modulos/{modulo}/evaluacion', [CapacitadorModulo::class, 'evaluacion'])->name('cursos.modulos.evaluacion');
        Route::post('/cursos/{curso}/modulos/reordenar', [CapacitadorModulo::class, 'reordenar'])->name('cursos.modulos.reordenar');

        // Secciones
        Route::post('/cursos/{curso}/secciones', [SeccionCursoController::class, 'store'])->name('cursos.secciones.store');
        Route::put('/cursos/{curso}/secciones/{seccion}', [SeccionCursoController::class, 'update'])->name('cursos.secciones.update');
        Route::delete('/cursos/{curso}/secciones/{seccion}', [SeccionCursoController::class, 'destroy'])->name('cursos.secciones.destroy');
        Route::post('/cursos/{curso}/secciones/reordenar', [SeccionCursoController::class, 'reordenar'])->name('cursos.secciones.reordenar');

        // Participantes
        Route::get('/cursos/{curso}/participantes', [CapacitadorParticipante::class, 'index'])->name('cursos.participantes.index');

        // Certificados
        Route::post('/cursos/{curso}/certificados/{user}', [CapacitadorCertificado::class, 'generar'])->name('certificados.generar');
        Route::get('/certificados/{certificado}/descargar', [CapacitadorCertificado::class, 'descargar'])->name('certificados.descargar');

        // Calendario Institucional
        Route::get('/calendario', CalendarioCapacitaciones::class)->name('calendario.index');

        // Estadísticas
        Route::get('/estadisticas', [CapacitadorEstadisticas::class, 'index'])->name('estadisticas.index');

        // Solo Capacitador Interno
        Route::middleware(['capacitador.interno'])->group(function () {
            Route::post('/cursos/{curso}/estamentos', [CapacitadorParticipante::class, 'syncEstamentos'])->name('cursos.estamentos.sync');
            Route::get('/cursos/{curso}/participantes/exportar', [CapacitadorParticipante::class, 'exportar'])->name('cursos.participantes.exportar');
        });
    });

    // --- SOLO DESARROLLADOR ---
    Route::get('/dev/configuracion', function () {
        if (! auth()->user()->isDesarrollador()) {
            abort(403);
        }

        return view('admin.configuracion');
    })->name('dev.configuracion');

    Route::get('/dev/soporte', function () {
        if (! auth()->user()->isDesarrollador()) {
            abort(403);
        }

        return view('dev.support');
    })->name('dev.support.index');

    Route::get('/dev/salud-lms', DevHealthController::class)
        ->name('dev.salud-lms');

    // RUTAS DE ADMINISTRACIÓN
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard.index');

        // Reportes
        Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
        Route::get('/reportes/exportar', [ReporteController::class, 'exportar'])->name('reportes.exportar');

        // Acreditación institucional
        Route::view('/acreditacion', 'admin.acreditacion.index')->name('acreditacion.index');

        // Usuarios
        Route::get('/usuarios', function () {
            return view('admin.usuarios.index');
        })->name('usuarios.index');

        // Perfil Administrativo
        Route::get('/perfil', [PerfilController::class, 'showAdmin'])->name('perfil.index');
    });
});
