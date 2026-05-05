## BLOQUE 1 — Rutas actuales
```text
Grupo: admin (admin.preview.toggle y prefijo /admin)
- POST /admin/preview-mode/toggle -> closure -> admin.preview.toggle
- GET /admin/reportes -> App\Http\Controllers\ReporteController@index -> admin.reportes.index
- GET /admin/reportes/exportar -> App\Http\Controllers\ReporteController@exportar -> admin.reportes.exportar
- GET /admin/usuarios -> closure (return view('admin.usuarios.index')) -> admin.usuarios.index
- GET /admin/perfil -> App\Http\Controllers\PerfilController@showAdmin -> admin.perfil.index

Grupo: capacitador (prefijo /capacitador)
- GET /capacitador/ -> App\Http\Controllers\Capacitador\DashboardController@index -> capacitador.dashboard
- GET /capacitador/cursos -> App\Http\Controllers\Capacitador\CursoController@index -> capacitador.cursos.index
- GET /capacitador/cursos/crear -> App\Http\Controllers\Capacitador\CursoController@create -> capacitador.cursos.crear
- POST /capacitador/cursos -> App\Http\Controllers\Capacitador\CursoController@store -> capacitador.cursos.store
- GET /capacitador/cursos/{curso} -> App\Http\Controllers\Capacitador\CursoController@show -> capacitador.cursos.show
- GET /capacitador/cursos/{curso}/editar -> App\Http\Controllers\Capacitador\CursoController@edit -> capacitador.cursos.editar
- PUT /capacitador/cursos/{curso} -> App\Http\Controllers\Capacitador\CursoController@update -> capacitador.cursos.update
- DELETE /capacitador/cursos/{curso} -> App\Http\Controllers\Capacitador\CursoController@destroy -> capacitador.cursos.destroy
- POST /capacitador/cursos/{curso}/duplicar -> App\Http\Controllers\Capacitador\CursoController@duplicar -> capacitador.cursos.duplicar
- GET /capacitador/cursos/{curso}/modulos/crear -> App\Http\Controllers\Capacitador\ModuloController@create -> capacitador.cursos.modulos.crear
- POST /capacitador/cursos/{curso}/modulos -> App\Http\Controllers\Capacitador\ModuloController@store -> capacitador.cursos.modulos.store
- GET /capacitador/cursos/{curso}/modulos/{modulo}/editar -> App\Http\Controllers\Capacitador\ModuloController@edit -> capacitador.cursos.modulos.editar
- PUT /capacitador/cursos/{curso}/modulos/{modulo} -> App\Http\Controllers\Capacitador\ModuloController@update -> capacitador.cursos.modulos.update
- DELETE /capacitador/cursos/{curso}/modulos/{modulo} -> App\Http\Controllers\Capacitador\ModuloController@destroy -> capacitador.cursos.modulos.destroy
- GET /capacitador/cursos/{curso}/modulos/{modulo}/evaluacion -> App\Http\Controllers\Capacitador\ModuloController@evaluacion -> capacitador.cursos.modulos.evaluacion
- POST /capacitador/cursos/{curso}/modulos/reordenar -> App\Http\Controllers\Capacitador\ModuloController@reordenar -> capacitador.cursos.modulos.reordenar
- POST /capacitador/cursos/{curso}/secciones -> App\Http\Controllers\Capacitador\SeccionCursoController@store -> capacitador.cursos.secciones.store
- PUT /capacitador/cursos/{curso}/secciones/{seccion} -> App\Http\Controllers\Capacitador\SeccionCursoController@update -> capacitador.cursos.secciones.update
- DELETE /capacitador/cursos/{curso}/secciones/{seccion} -> App\Http\Controllers\Capacitador\SeccionCursoController@destroy -> capacitador.cursos.secciones.destroy
- POST /capacitador/cursos/{curso}/secciones/reordenar -> App\Http\Controllers\Capacitador\SeccionCursoController@reordenar -> capacitador.cursos.secciones.reordenar
- GET /capacitador/cursos/{curso}/participantes -> App\Http\Controllers\Capacitador\ParticipanteController@index -> capacitador.cursos.participantes.index
- POST /capacitador/cursos/{curso}/certificados/{user} -> App\Http\Controllers\Capacitador\CertificadoController@generar -> capacitador.certificados.generar
- GET /capacitador/certificados/{certificado}/descargar -> App\Http\Controllers\Capacitador\CertificadoController@descargar -> capacitador.certificados.descargar
- GET /capacitador/calendario -> App\Livewire\Capacitador\CalendarioCapacitaciones -> capacitador.calendario.index
- POST /capacitador/cursos/{curso}/estamentos -> App\Http\Controllers\Capacitador\ParticipanteController@syncEstamentos -> capacitador.cursos.estamentos.sync
- GET /capacitador/cursos/{curso}/participantes/exportar -> App\Http\Controllers\Capacitador\ParticipanteController@exportar -> capacitador.cursos.participantes.exportar
```

## BLOQUE 2 — Controladores involucrados
### app/Http/Controllers/Capacitador/DashboardController.php
```php
<?php

namespace App\Http\Controllers\Capacitador;

use App\Http\Controllers\Controller;
use App\Models\Certificado;
use App\Models\Curso;
use App\Models\ProgresoModulo;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $cacheScope = $user->hasAdminAccess() ? 'admin' : "capacitador_{$user->id}";
        $cacheKey = "dashboard_summary_v2_{$cacheScope}";

        ['stats' => $stats, 'ultimosCursos' => $ultimosCursos] = Cache::flexible(
            $cacheKey,
            [30, 120],
            function () use ($user): array {
                $cursosQuery = $user->hasAdminAccess()
                    ? Curso::query()
                    : $user->cursosImpartidos();

                $cursos = $cursosQuery
                    ->withCount(['modulos', 'estamentos', 'planificaciones'])
                    ->orderByDesc('created_at')
                    ->get();

                $cursoIds = $cursos->pluck('id');
                $totalParticipantes = $cursoIds->isEmpty()
                    ? 0
                    : ProgresoModulo::whereHas('modulo', fn ($query) => $query->whereIn('curso_id', $cursoIds))
                        ->distinct('user_id')
                        ->count('user_id');

                $totalCertificados = $cursoIds->isEmpty()
                    ? 0
                    : Certificado::whereIn('curso_id', $cursoIds)->count();

                return [
                    'stats' => [
                        'cursos' => $cursos->count(),
                        'participantes' => $totalParticipantes,
                        'certificados' => $totalCertificados,
                    ],
                    'ultimosCursos' => $cursos
                        ->take(5)
                        ->map(fn (Curso $curso): array => [
                            'id' => $curso->id,
                            'titulo' => $curso->titulo,
                            'modulos_count' => $curso->modulos_count,
                            'planificaciones_count' => $curso->planificaciones_count,
                        ])
                        ->values()
                        ->all(),
                ];
            }
        );

        return view('capacitador.dashboard', compact('stats', 'ultimosCursos'));
    }
}

```

### app/Http/Controllers/Admin/
Archivos encontrados en la carpeta:
```text
app/Http/Controllers/Admin/EstamentoController.php
app/Http/Controllers/Admin/SedeController.php
```

Contenido de los archivos de la carpeta:
#### app/Http/Controllers/Admin/EstamentoController.php
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class EstamentoController extends Controller
{
    //
}

```

#### app/Http/Controllers/Admin/SedeController.php
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class SedeController extends Controller
{
    //
}

```

#### app/Http/Controllers/Admin/DashboardController.php
[ARCHIVO NO ENCONTRADO]
#### app/Http/Controllers/Admin/ReportesController.php
[ARCHIVO NO ENCONTRADO]
### app/Http/Controllers/Auth/ o donde viva UserAreaRedirector
#### app/Support/UserAreaRedirector.php
```php
<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserAreaRedirector
{
    public static function canonicalRouteName(User $user): string
    {
        if (session('preview_mode')) {
            return 'cursos.index';
        }

        if ($user->hasAdminAccess()) {
            return 'admin.reportes.index';
        }

        if ($user->isCapacitador()) {
            return 'capacitador.dashboard';
        }

        return 'cursos.index';
    }

    public static function canonicalUrl(User $user): string
    {
        return route(self::canonicalRouteName($user));
    }

    public static function intendedOrCanonicalUrl(Request $request, User $user): string
    {
        $intended = $request->session()->pull('url.intended');

        if (is_string($intended) && self::canAccessUrl($request, $user, $intended)) {
            return $intended;
        }

        return self::canonicalUrl($user);
    }

    public static function canAccessUserArea(User $user): bool
    {
        if ($user->hasAdminAccess() || $user->isCapacitador()) {
            return (bool) session('preview_mode');
        }

        return true;
    }

    public static function userAreaFallbackRouteName(User $user): string
    {
        if ($user->hasAdminAccess()) {
            return 'admin.reportes.index';
        }

        if ($user->isCapacitador()) {
            return 'capacitador.dashboard';
        }

        return 'cursos.index';
    }

    private static function canAccessUrl(Request $request, User $user, string $url): bool
    {
        $path = self::pathForUrl($request, $url);

        if ($path === null) {
            return false;
        }

        if ($path === '/') {
            return true;
        }

        if (self::isAdminAreaPath($path)) {
            return $user->hasAdminAccess();
        }

        if (self::isCapacitadorAreaPath($path)) {
            return $user->isCapacitador() || $user->hasAdminAccess();
        }

        if (self::isDevAreaPath($path)) {
            return $user->isDesarrollador();
        }

        if (self::isUserAreaPath($path)) {
            return self::canAccessUserArea($user);
        }

        return true;
    }

    private static function pathForUrl(Request $request, string $url): ?string
    {
        $parts = parse_url($url);

        if ($parts === false) {
            return null;
        }

        if (isset($parts['host']) && $parts['host'] !== $request->getHost()) {
            return null;
        }

        $path = $parts['path'] ?? '/';

        return '/'.ltrim($path, '/');
    }

    private static function isAdminAreaPath(string $path): bool
    {
        return Str::is('/admin/*', $path) && $path !== '/admin/preview-mode/toggle';
    }

    private static function isCapacitadorAreaPath(string $path): bool
    {
        return $path === '/capacitador' || Str::is('/capacitador/*', $path);
    }

    private static function isDevAreaPath(string $path): bool
    {
        return $path === '/dev/configuracion';
    }

    private static function isUserAreaPath(string $path): bool
    {
        return $path === '/cursos'
            || Str::is('/cursos/*', $path)
            || $path === '/calendario-cursos'
            || $path === '/perfil'
            || $path === '/mis-certificados'
            || Str::is('/mis-certificados/*', $path)
            || $path === '/ajustes';
    }
}

```

#### app/Http/Controllers/AuthController.php
```php
<?php

namespace App\Http\Controllers;

use App\Support\UserAreaRedirector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Muestra el formulario de login.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Maneja el intento de inicio de sesión.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = auth()->user();

            return redirect()->to(UserAreaRedirector::intendedOrCanonicalUrl($request, $user));
        }

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con los registros.',
        ])->onlyInput('email');
    }

    /**
     * Cierra la sesión del usuario.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}

```

#### app/Http/Controllers/Auth/PasswordResetController.php
```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)])->onlyInput('email');
    }

    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            Auth::login(Password::broker()->getUser($request->only('email')));

            return redirect('/')->with('status', __($status));
        }

        return back()->withErrors(['email' => __($status)])->onlyInput('email');
    }
}

```

#### app/Http/Controllers/ReporteController.php
```php
<?php

namespace App\Http\Controllers;

use App\Exports\ReporteExport;
use App\Http\Requests\ReportFilterRequest;
use App\Models\Curso;
use App\Models\Estamento;
use App\Models\Sede;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ReporteController extends Controller
{
    private function sanitizeIdFilter(array $ids): array
    {
        return array_values(array_unique(array_filter(array_map('intval', $ids), fn ($id) => $id > 0)));
    }

    public function index(ReportFilterRequest $request)
    {
        $data = $request->validated();
        $estamentos = Estamento::all();
        $cursos = Curso::all();
        $sedes = Sede::all();
        $cursoSeleccionado = null;

        $selectedSedes = $this->sanitizeIdFilter($data['sede_id'] ?? []);
        $selectedEstamentos = $this->sanitizeIdFilter($data['estamento_id'] ?? []);
        $selectedCursos = $this->sanitizeIdFilter($data['curso_id'] ?? []);

        $edadMinReq = $data['edad_min'] ?? null;
        $edadMaxReq = $data['edad_max'] ?? null;

        // Límites para el slider de edad
        $minDateLimit = Carbon::now()->subYears(100)->format('Y-m-d');
        $maxDateLimit = Carbon::now()->format('Y-m-d');

        $maxNac = User::whereNotNull('fecha_nacimiento')->whereBetween('fecha_nacimiento', [$minDateLimit, $maxDateLimit])->max('fecha_nacimiento');
        $minNac = User::whereNotNull('fecha_nacimiento')->whereBetween('fecha_nacimiento', [$minDateLimit, $maxDateLimit])->min('fecha_nacimiento');

        $ageBounds = [
            'min' => $maxNac ? Carbon::parse($maxNac)->age : 18,
            'max' => $minNac ? Carbon::parse($minNac)->age : 80,
        ];

        // El filtro de edad ahora siempre se considera activo y usa los límites de la BD por defecto
        $edadActiva = true;
        $edadMin = is_numeric($edadMinReq) ? (int) $edadMinReq : $ageBounds['min'];
        $edadMax = is_numeric($edadMaxReq) ? (int) $edadMaxReq : $ageBounds['max'];

        $query = User::with(['estamento', 'sede', 'certificados.curso'])
            ->whereNotNull('estamento_id');

        // 1. Filtro por Estamento
        if (! empty($selectedEstamentos)) {
            $query->whereIn('estamento_id', $selectedEstamentos);
        }

        // 2. Filtro por Sede
        if (! empty($selectedSedes)) {
            $query->whereIn('sede_id', $selectedSedes);
        }

        // 3. Filtro por Curso
        if (! empty($selectedCursos)) {
            foreach ($selectedCursos as $id) {
                $query->whereHas('certificados', function ($q) use ($id) {
                    $q->where('curso_id', $id);
                });
            }

            if (count($selectedCursos) === 1) {
                $cursoSeleccionado = Curso::withCount('modulos')->find($selectedCursos[0]);
                if ($cursoSeleccionado) {
                    $query->withCount([
                        'progresos as modulos_completados_count' => function ($q) use ($cursoSeleccionado) {
                            $q->whereHas('modulo', function ($q2) use ($cursoSeleccionado) {
                                $q2->where('curso_id', $cursoSeleccionado->id);
                            })->where('completado', true);
                        },
                    ]);
                }
            } else {
                $cursoSeleccionado = Curso::withCount('modulos')->find($selectedCursos[0]);
            }
        }

        // 4. Filtro por Edad (siempre aplicado, usando defaults si es necesario)
        $query->where('fecha_nacimiento', '<=', Carbon::now()->subYears($edadMin)->format('Y-m-d'));
        $query->where('fecha_nacimiento', '>=', Carbon::now()->subYears($edadMax + 1)->addDay()->format('Y-m-d'));

        // 5. Filtro por Fechas
        if (! empty($data['fecha_inicio']) && ! empty($data['fecha_fin'])) {
            $query->whereHas('certificados', function ($q) use ($data, $selectedCursos) {
                $q->whereBetween('fecha_emision', [$data['fecha_inicio'], $data['fecha_fin']]);
                if (! empty($selectedCursos)) {
                    $q->whereIn('curso_id', $selectedCursos);
                }
            });
        }

        $usuarios = $query->paginate(15)->withQueryString();

        return view('admin.reportes.index', compact(
            'usuarios', 'estamentos', 'cursos', 'sedes',
            'cursoSeleccionado', 'ageBounds',
            'selectedSedes', 'selectedEstamentos', 'selectedCursos', 'edadActiva'
        ));
    }

    public function exportar(ReportFilterRequest $request)
    {
        return Excel::download(new ReporteExport($request), 'reporte_capacitaciones.xlsx');
    }
}

```

#### app/Http/Controllers/PerfilController.php
```php
<?php

namespace App\Http\Controllers;

class PerfilController extends Controller
{
    public function show()
    {
        $user = auth()->user()->load(['sede', 'estamento']);

        $totalCursos = $user->estamento?->cursos()->count() ?? 0;

        $cursosCompletados = $user->certificados()->count();

        $cursosEnProgreso = 0;
        if ($user->estamento && $totalCursos > 0) {
            $ids = $user->certificados()->pluck('curso_id');
            $cursosEnProgreso = $user->estamento->cursos()
                ->whereNotIn('cursos.id', $ids)
                ->whereHas('modulos.progresos', fn ($q) => $q->where('user_id', $user->id)->where('completado', true))
                ->count();
        }

        $certificados = $user->certificados()->with('curso')->latest()->take(5)->get();

        return view('perfil.index', compact(
            'user',
            'totalCursos',
            'cursosCompletados',
            'cursosEnProgreso',
            'certificados'
        ));
    }

    public function showAdmin()
    {
        $user = auth()->user()->load(['sede', 'estamento']);

        return view('admin.perfil', compact('user'));
    }
}

```

## BLOQUE 3 — Componentes Livewire actuales
Archivos dentro de `app/Livewire/`:
```text
app/Livewire/AccessibilityPreferences.php
app/Livewire/Admin/ReportePresets.php
app/Livewire/Admin/UserManagement.php
app/Livewire/CalendarioUsuario.php
app/Livewire/Capacitador/CalendarioCapacitaciones.php
app/Livewire/Capacitador/EditarEvaluacion.php
app/Livewire/Capacitador/EstadisticasDashboard.php
app/Livewire/Capacitador/GestionEstamentos.php
app/Livewire/DevConfig.php
app/Livewire/VerEvaluacion.php
```

### Componentes relacionados con cursos, gráficos, reportes o estadísticas
#### app/Livewire/Capacitador/EstadisticasDashboard.php
- Clase: `App\\Livewire\\Capacitador\\EstadisticasDashboard`
- Propiedades públicas: `public int $capacitadorId;`, `public array $chartData = [];`
- render(): usa `return view('livewire.capacitador.estadisticas-dashboard');`
- Vista renderizada: `resources/views/livewire/capacitador/estadisticas-dashboard.blade.php`
```php
<?php

namespace App\Livewire\Capacitador;

use App\Models\Curso;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Defer;
use Livewire\Component;

#[Defer]
class EstadisticasDashboard extends Component
{
    public int $capacitadorId;

    /** @var array Chart data: [{label, value, color}] */
    public array $chartData = [];

    public function mount(int $capacitadorId): void
    {
        $this->capacitadorId = $capacitadorId;

        $this->chartData = Cache::flexible(
            "capacitador_dashboard_chart_{$capacitadorId}",
            [30, 120],
            fn (): array => $this->buildChartData($capacitadorId),
        );
    }

    public function render()
    {
        return view('livewire.capacitador.estadisticas-dashboard');
    }

    public function placeholder(): string
    {
        return <<<'HTML'
        <div class="space-y-3 py-2">
            <div class="h-5 w-1/3 animate-pulse rounded bg-Alumco-blue/10"></div>
            <div class="h-52 w-full animate-pulse rounded-2xl bg-gray-100"></div>
        </div>
        HTML;
    }

    /**
     * @return array<int, array{label: string, value: int}>
     */
    private function buildChartData(int $capacitadorId): array
    {
        $cursos = Curso::query()
            ->where('capacitador_id', $capacitadorId)
            ->withCount('modulos')
            ->get(['id', 'titulo']);

        if ($cursos->isEmpty()) {
            return [];
        }

        $courseIds = $cursos->pluck('id')->all();

        $progresoPorUsuario = DB::table('progresos_modulo as progreso')
            ->join('modulos as modulo', 'modulo.id', '=', 'progreso.modulo_id')
            ->whereIn('modulo.curso_id', $courseIds)
            ->selectRaw('modulo.curso_id as curso_id')
            ->selectRaw('progreso.user_id as user_id')
            ->selectRaw('COUNT(DISTINCT progreso.modulo_id) as modulos_con_avance')
            ->selectRaw('COUNT(DISTINCT CASE WHEN progreso.completado = 1 THEN progreso.modulo_id END) as modulos_completados')
            ->groupBy('modulo.curso_id', 'progreso.user_id')
            ->get()
            ->groupBy('curso_id');

        return $cursos->map(function (Curso $curso) use ($progresoPorUsuario): array {
            $totalModulos = (int) $curso->modulos_count;

            if ($totalModulos === 0) {
                return ['label' => $curso->titulo, 'value' => 0];
            }

            $resumenUsuarios = $progresoPorUsuario->get($curso->id, collect());
            $usuariosTotal = $resumenUsuarios->count();

            if ($usuariosTotal === 0) {
                return ['label' => $curso->titulo, 'value' => 0];
            }

            $completaron = $resumenUsuarios->filter(
                fn ($item): bool => (int) $item->modulos_completados >= $totalModulos
            )->count();

            return [
                'label' => $curso->titulo,
                'value' => (int) round(($completaron / $usuariosTotal) * 100),
            ];
        })->values()->all();
    }
}

```

#### app/Livewire/Admin/ReportePresets.php
- Clase: `App\\Livewire\\Admin\\ReportePresets`
- Propiedades públicas: `public $presets;`, `public $nuevoNombre = ;`
- render(): usa `return view('livewire.admin.reporte-presets');`
- Vista renderizada: `resources/views/livewire/admin/reporte-presets.blade.php`
```php
<?php

namespace App\Livewire\Admin;

use App\Models\ReportePreset;
use Livewire\Component;

class ReportePresets extends Component
{
    public $presets;

    public $nuevoNombre = '';

    public function mount()
    {
        $this->cargarPresets();
    }

    public function cargarPresets()
    {
        $this->presets = ReportePreset::orderBy('nombre')->get();
    }

    public function guardarPreset(array $columnas)
    {
        $this->validate([
            'nuevoNombre' => 'required|string|max:50|unique:reporte_presets,nombre',
        ], [
            'nuevoNombre.required' => 'Debes asignar un nombre al formato.',
            'nuevoNombre.unique' => 'Ya existe un formato con este nombre.',
            'nuevoNombre.max' => 'El nombre es muy largo (máx 50 carac.).',
        ]);

        ReportePreset::create([
            'nombre' => $this->nuevoNombre,
            'columnas' => $columnas,
        ]);

        $this->nuevoNombre = '';
        $this->cargarPresets();

        $this->dispatch('preset-guardado');
    }

    public function eliminarPreset(int $id)
    {
        ReportePreset::destroy($id);
        $this->cargarPresets();
    }

    public function resetError($field = null)
    {
        $this->resetValidation($field);
    }

    public function render()
    {
        return view('livewire.admin.reporte-presets');
    }
}

```

## BLOQUE 4 — Vistas actuales
### capacitador/dashboard.blade.php
```blade
@extends('layouts.panel')

@section('title', 'Dashboard')
@section('header_title', 'Dashboard')

@section('content')
    <div class="mb-8">
        <h2 class="text-xl font-display font-bold text-Alumco-blue/70">Resumen de Actividad Académica</h2>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-10">
        <div class="bg-white p-6 rounded-[24px] border border-gray-200 shadow-none flex items-center gap-5 transition-all duration-300 hover:shadow-xl hover:shadow-Alumco-blue/5 hover:-translate-y-1 hover:border-Alumco-blue/20">
            <div class="bg-Alumco-blue/5 rounded-2xl p-4 shrink-0 text-Alumco-blue">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div>
                <p class="text-3xl font-display font-black text-Alumco-blue">{{ $stats['cursos'] }}</p>
                <p class="text-[11px] font-display font-black text-gray-400 uppercase tracking-widest">Mis Cursos</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-[24px] border border-gray-200 shadow-none flex items-center gap-5 transition-all duration-300 hover:shadow-xl hover:shadow-Alumco-green/5 hover:-translate-y-1 hover:border-Alumco-green/20">
            <div class="bg-Alumco-green/10 rounded-2xl p-4 shrink-0 text-Alumco-green-vivid">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-3xl font-display font-black text-Alumco-green-vivid">{{ $stats['participantes'] }}</p>
                <p class="text-[11px] font-display font-black text-gray-400 uppercase tracking-widest">Participantes</p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-[24px] border border-gray-200 shadow-none flex items-center gap-5 transition-all duration-300 hover:shadow-xl hover:shadow-Alumco-yellow/5 hover:-translate-y-1 hover:border-Alumco-yellow/20">
            <div class="bg-Alumco-yellow/10 rounded-2xl p-4 shrink-0 text-Alumco-yellow">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
            </div>
            <div>
                <p class="text-3xl font-display font-black text-Alumco-yellow">{{ $stats['certificados'] }}</p>
                <p class="text-[11px] font-display font-black text-gray-400 uppercase tracking-widest">Certificados</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
        {{-- Chart --}}
        <div class="lg:col-span-7 bg-white p-8 rounded-[32px] border border-gray-200 shadow-none overflow-hidden">
            <h3 class="text-lg font-display font-black text-Alumco-blue mb-6">Rendimiento por Curso</h3>
            @livewire('capacitador.estadisticas-dashboard', ['capacitadorId' => auth()->id()])
        </div>

        {{-- Últimos cursos --}}
        <div class="lg:col-span-5 bg-white p-8 rounded-[32px] border border-gray-200 shadow-none flex flex-col overflow-hidden">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-display font-black text-Alumco-blue">Últimos Cursos</h3>
                <a href="{{ route('capacitador.cursos.index') }}" class="text-xs font-bold text-Alumco-blue hover:underline">Ver todos</a>
            </div>
            
            <div class="flex-1 overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-50">
                            <th class="pb-3 text-[10px] font-display font-black uppercase tracking-widest text-gray-400">Título</th>
                            <th class="pb-3 text-[10px] font-display font-black uppercase tracking-widest text-gray-400 text-right">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($ultimosCursos as $curso)
                            @php
                                $tienePlanActiva = $curso['planificaciones_count'] > 0;
                                $estado = $tienePlanActiva ? 'Programado' : 'Sin Programar';
                                $badgeColor = $tienePlanActiva ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400';
                            @endphp
                            <tr class="group">
                                <td class="py-4 pr-4">
                                    <a href="{{ route('capacitador.cursos.show', $curso['id']) }}" class="font-display font-bold text-Alumco-gray group-hover:text-Alumco-blue transition-colors leading-tight block">
                                        {{ $curso['titulo'] }}
                                    </a>
                                    <span class="text-[10px] text-Alumco-gray/40 font-bold uppercase">{{ $curso['modulos_count'] }} módulos</span>
                                </td>
                                <td class="py-4 text-right">
                                    <span class="inline-block px-2 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-tighter {{ $badgeColor }}">
                                        {{ $estado }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="py-10 text-center text-Alumco-gray/30 font-medium">No has creado cursos aún.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

```

### resources/views/admin/reportes/index.blade.php
```blade
@extends('layouts.panel')

@section('title', 'Reportes de Capacitación')

@section('header_title', 'Reportes e Impacto')

@section('content')
<div class="mb-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h2 class="admin-page-title">Análisis de Cumplimiento</h2>
            <p class="admin-page-subtitle">Visualización de progreso y resultados por estamentos</p>
        </div>
    </div>
</div>

{{-- Filtros --}}
<div class="mb-8">
    <form action="{{ route('admin.reportes.index') }}" method="GET" class="filter-card admin-surface p-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            {{-- Sedes (Multi) --}}
            <div class="lg:col-span-3 space-y-2">
                <label class="admin-page-eyebrow">Sedes</label>
                <x-picker-multi name="sede_id" :options="$sedes->pluck('nombre', 'id')->toArray()" :selected="$selectedSedes" placeholder="Todas las sedes" />
            </div>

            {{-- Estamentos (Multi) --}}
            <div class="lg:col-span-3 space-y-2">
                <label class="admin-page-eyebrow">Estamentos</label>
                <x-picker-multi name="estamento_id" :options="$estamentos->pluck('nombre', 'id')->toArray()" :selected="$selectedEstamentos" placeholder="Todos los estamentos" />
            </div>

            {{-- Cursos (Multi) --}}
            <div class="lg:col-span-4 space-y-2">
                <label class="admin-page-eyebrow">Cursos Aprobados</label>
                <x-picker-multi name="curso_id" :options="$cursos->pluck('titulo', 'id')->toArray()" :selected="$selectedCursos" placeholder="Cualquier curso" />
            </div>

            {{-- Rango Etario --}}
            <div class="lg:col-span-2 space-y-2" id="age-filter-root">
                <label class="admin-page-eyebrow">Rango Etario</label>
                
                <div class="pt-2 px-1" id="age-slider-wrapper">
                    <div class="relative h-1 bg-gray-100 rounded-full">
                        <div id="age-range-fill" class="absolute h-full bg-Alumco-blue rounded-full" style="left: 0%; width: 100%;"></div>
                        <input type="range" id="age-min-slider" min="{{ $ageBounds['min'] }}" max="{{ $ageBounds['max'] }}" value="{{ request('edad_min', $ageBounds['min']) }}" class="absolute w-full -top-1.5 h-4 appearance-none bg-transparent pointer-events-none custom-slider">
                        <input type="range" id="age-max-slider" min="{{ $ageBounds['min'] }}" max="{{ $ageBounds['max'] }}" value="{{ request('edad_max', $ageBounds['max']) }}" class="absolute w-full -top-1.5 h-4 appearance-none bg-transparent pointer-events-none custom-slider">
                    </div>
                    <div class="flex justify-between mt-3 text-xs font-black text-Alumco-blue/60">
                        <span id="age-min-value">{{ request('edad_min', $ageBounds['min']) }}</span>
                        <span id="age-max-value">{{ request('edad_max', $ageBounds['max']) }}</span>
                    </div>
                </div>
                <input type="hidden" name="edad_min" id="edad-min-input" value="{{ request('edad_min', $ageBounds['min']) }}">
                <input type="hidden" name="edad_max" id="edad-max-input" value="{{ request('edad_max', $ageBounds['max']) }}">
            </div>

            <!-- Footer del Formulario -->
            <div class="lg:col-span-12 flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-gray-100">
                <div class="flex items-center gap-3">
                    @php
                        $activeFiltersCount = 0;
                        if (count($selectedSedes) > 0) { $activeFiltersCount++; }
                        if (count($selectedEstamentos) > 0) { $activeFiltersCount++; }
                        if (count($selectedCursos) > 0) { $activeFiltersCount++; }
                        if ((request()->filled('edad_min') && request('edad_min') != $ageBounds['min']) || 
                            (request()->filled('edad_max') && request('edad_max') != $ageBounds['max'])) { 
                            $activeFiltersCount++; 
                        }
                        if (request()->filled('fecha_inicio') || request()->filled('fecha_fin')) { $activeFiltersCount++; }
                    @endphp
                    <span class="badge-filter">{{ $activeFiltersCount }} filtros aplicados</span>
                    @if($activeFiltersCount > 0)
                        <a href="{{ route('admin.reportes.index') }}" class="text-xs font-bold text-gray-400 hover:text-Alumco-coral transition-colors underline underline-offset-4">Limpiar todo</a>
                    @endif
                </div>

                <div class="flex items-center gap-4 w-full sm:w-auto">
                    <button type="button" onclick="openExportModal()"
                        class="admin-action-button admin-action-button--success shadow-lg shadow-Alumco-green/20">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Exportar Excel
                    </button>
                    
                    <button type="submit" class="flex-1 sm:flex-none admin-action-button admin-action-button--primary px-10 shadow-lg shadow-Alumco-blue/20">
                        Aplicar Filtros
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="mb-4 flex items-center gap-3">
    <div class="h-px bg-gray-200 flex-1"></div>
    <span class="px-4 py-1 rounded-full bg-white border border-gray-100 text-[11px] font-display font-black text-Alumco-blue uppercase tracking-widest shadow-sm">
        {{ $usuarios->total() }} registros encontrados
    </span>
    <div class="h-px bg-gray-200 flex-1"></div>
</div>

<!-- Tabla Card-Style -->
<div class="admin-surface overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50">
                    <th class="px-6 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100">Colaborador</th>
                    <th class="px-6 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100">Email</th>
                    <th class="px-6 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100">RUT</th>
                    <th class="px-6 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100">Sexo</th>
                    <th class="px-6 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100">Edad</th>
                    <th class="px-6 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100">Estamento</th>
                    <th class="px-6 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100">Sede</th>
                    <th class="px-6 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100">Cursos</th>
                    @if($cursoSeleccionado)
                        <th class="px-6 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100 text-right">Aprobación</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($usuarios as $user)
                <tr class="hover:bg-Alumco-cream/30 transition-colors group cursor-default">
                    <td class="px-6 py-5">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-Alumco-blue/5 text-Alumco-blue flex items-center justify-center font-display font-bold text-xs shrink-0">
                                {{ collect(explode(' ', $user->name))->map(fn($n) => $n[0])->take(2)->join('') }}
                            </div>
                            <p class="font-display font-bold text-Alumco-gray leading-tight text-sm">{{ $user->name }}</p>
                        </div>
                    </td>
                    <td class="px-6 py-5">
                        <span class="text-sm text-Alumco-gray font-medium">{{ $user->email }}</span>
                    </td>
                    <td class="px-6 py-5">
                        <span class="text-xs font-bold text-Alumco-blue/60 uppercase tracking-tight">{{ $user->rut ?? '—' }}</span>
                    </td>
                    <td class="px-6 py-5">
                        <span class="text-sm font-bold text-Alumco-gray capitalize">{{ $user->sexo === 'F' ? 'Femenino' : ($user->sexo === 'M' ? 'Masculino' : 'Otro') }}</span>
                    </td>
                    <td class="px-6 py-5">
                        <span class="text-sm font-bold text-Alumco-gray/60">{{ $user->fecha_nacimiento ? \Carbon\Carbon::parse($user->fecha_nacimiento)->age . ' años' : '—' }}</span>
                    </td>
                    <td class="px-6 py-5">
                        <span class="text-sm font-bold text-Alumco-gray">{{ $user->estamento->nombre ?? '—' }}</span>
                    </td>
                    <td class="px-6 py-5">
                        <span class="text-[11px] font-black text-Alumco-blue/40 uppercase tracking-tighter">{{ $user->sede->nombre ?? '—' }}</span>
                    </td>
                    <td class="px-6 py-5">
                        @if($user->certificados->isEmpty())
                            <span class="text-xs text-gray-300 italic font-medium">Ninguno</span>
                        @else
                            <div x-data="{ open: false, x: 0, y: 0 }" 
                                 @mouseenter="const rect = $el.getBoundingClientRect(); x = rect.left + (rect.width / 2); y = rect.top; open = true" 
                                 @mouseleave="open = false" 
                                 class="relative cursor-help inline-block">
                                <span class="text-sm font-black text-Alumco-blue underline underline-offset-4 decoration-Alumco-blue/30 decoration-dotted">
                                    {{ $user->certificados->count() }} {{ Str::plural('curso', $user->certificados->count()) }}
                                </span>
                                
                                {{-- Tooltip con lista de cursos (Teleportado para evitar clipping) --}}
                                <template x-teleport="body">
                                    <div x-show="open" 
                                         x-cloak
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 translate-y-1"
                                         x-transition:enter-end="opacity-100 translate-y-0"
                                         x-transition:leave="transition ease-in duration-150"
                                         x-transition:leave-start="opacity-100 translate-y-0"
                                         x-transition:leave-end="opacity-0 translate-y-1"
                                         class="fixed z-[9999] w-80 bg-white border border-gray-100 text-Alumco-gray rounded-2xl p-5 shadow-[0_20px_50px_rgba(32,80,153,0.15)] pointer-events-none"
                                         :style="`left: ${x}px; top: ${y}px; transform: translate(-50%, calc(-100% - 10px))`">
                                        
                                        <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-50">
                                            <h4 class="font-display font-black uppercase tracking-widest text-[10px] text-Alumco-blue">Historial Académico</h4>
                                            <span class="text-[9px] font-bold text-gray-300 bg-gray-50 px-2 py-0.5 rounded-full">{{ $user->certificados->count() }} total</span>
                                        </div>
                                        
                                        <div class="space-y-3 max-h-56 overflow-y-auto custom-scrollbar pr-2 pointer-events-auto">
                                            @foreach($user->certificados->sortByDesc('fecha_emision') as $cert)
                                                <div class="flex items-start gap-3 p-2 rounded-xl transition-colors {{ in_array($cert->curso_id, $selectedCursos) ? 'bg-Alumco-blue/5 border border-Alumco-blue/10' : '' }}">
                                                    <div class="w-1.5 h-1.5 rounded-full mt-1.5 shrink-0 {{ in_array($cert->curso_id, $selectedCursos) ? 'bg-Alumco-blue animate-pulse' : 'bg-gray-200' }}"></div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-[11px] leading-tight {{ in_array($cert->curso_id, $selectedCursos) ? 'font-black text-Alumco-blue' : 'font-bold text-Alumco-gray' }}">
                                                            {{ $cert->curso->titulo }}
                                                        </p>
                                                        <p class="text-[9px] text-Alumco-gray/40 font-medium mt-1">Aprobado el {{ $cert->fecha_emision->format('d/m/Y') }}</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        {{-- Flechita --}}
                                        <div class="absolute top-full left-1/2 -translate-x-1/2 border-[8px] border-transparent border-t-white drop-shadow-[0_1px_0_rgba(0,0,0,0.05)]"></div>
                                    </div>
                                </template>
                            </div>
                        @endif
                    </td>
                    @if($cursoSeleccionado)
                        <td class="px-8 py-5 text-right">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold bg-green-50 text-green-700 border border-green-100">
                                {{ $user->certificados->where('curso_id', $cursoSeleccionado->id)->first()?->fecha_emision?->format('d/m/Y') ?? '—' }}
                            </span>
                        </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-8 py-16 text-center text-Alumco-gray/40">
                        <div class="flex flex-col items-center opacity-40">
                            <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <p class="font-display font-bold uppercase tracking-widest text-xs">No se encontraron resultados para los filtros aplicados</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($usuarios->hasPages())
    <div class="px-8 py-5 border-t border-gray-50 bg-gray-50/30">
        {{ $usuarios->links() }}
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .custom-slider {
        pointer-events: none;
    }
    .custom-slider::-webkit-slider-thumb {
        pointer-events: auto;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #205099;
        cursor: pointer;
        border: 2px solid white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        -webkit-appearance: none;
    }
    .custom-slider::-moz-range-thumb {
        pointer-events: auto;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #205099;
        cursor: pointer;
        border: 2px solid white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
</style>
@endpush

@section('modals')
<!-- Modal Configuración Exportación -->
<div id="export-modal-backdrop" class="fixed inset-0 bg-Alumco-gray/40 backdrop-blur-sm z-50 hidden opacity-0 pointer-events-none transition-opacity duration-300" 
     :class="{ 'transition-none': window.AlumcoAccessibility?.isReducedMotion() }" aria-hidden="true"></div>
<div id="export-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden opacity-0 pointer-events-none transition-all duration-300 scale-95" 
     :class="{ 'transition-none': window.AlumcoAccessibility?.isReducedMotion() }" aria-hidden="true">
    <div class="bg-white w-full max-w-7xl rounded-3xl shadow-xl overflow-hidden" 
         x-data="{
            allCols: {
                rut:       { label: 'RUT', data: ['12.345.678-9', '18.765.432-K', '21.098.765-4'] },
                nombre:    { label: 'Nombre completo', data: ['Juan Pérez', 'María Ignacia', 'Carlos Ruiz'] },
                sexo:      { label: 'Sexo', data: ['Masculino', 'Femenino', 'Masculino'] },
                edad:      { label: 'Edad', data: ['28 años', '34 años', '45 años'] },
                email:     { label: 'Correo', data: ['j.perez@alumco.cl', 'm.ignacia@alumco.cl', 'c.ruiz@alumco.cl'] },
                sede:      { label: 'Sede', data: ['Sede Central', 'Sede Norte', 'Sede Sur'] },
                estamento: { label: 'Estamento', data: ['Auxiliares', 'Enfermería', 'Directivos'] },
                cursos:    { label: 'Cursos Aprobados', data: ['Curso A (20/04)', 'Curso B (15/04)', '—'] }
            },
            selectedKeys: ['rut', 'nombre', 'sexo', 'edad', 'email', 'sede', 'estamento', 'cursos'],
            
            toggleCol(key) {
                if (this.selectedKeys.includes(key)) {
                    this.selectedKeys = this.selectedKeys.filter(k => k !== key);
                } else {
                    this.selectedKeys.push(key);
                }
            },
            
            reorder(fromIdx, toIdx) {
                const item = this.selectedKeys.splice(fromIdx, 1)[0];
                this.selectedKeys.splice(toIdx, 0, item);
            },

            resetToDefault() {
                this.selectedKeys = ['rut', 'nombre', 'sexo', 'edad', 'email', 'sede', 'estamento', 'cursos'];
            }
         }"
         @open-export-modal.window="resetToDefault()">
        <form action="{{ route('admin.reportes.exportar') }}" method="GET">
            {{-- Replicar filtros actuales --}}
            @foreach(request()->except(['columnas', 'nombres']) as $key => $value)
                @if(is_array($value))
                    @foreach($value as $v)
                        <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                    @endforeach
                @else
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach

            {{-- Inputs ocultos para enviar el orden final al servidor --}}
            <template x-for="key in selectedKeys" :key="key">
                <input type="hidden" name="columnas[]" :value="key">
            </template>

            <div class="p-8">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-12 h-12 rounded-full bg-Alumco-green/10 text-Alumco-blue flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-display font-black text-Alumco-blue">Configurar Exportación</h3>
                    </div>
                </div>

                {{-- Paso 1: Selección de Columnas --}}
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-[11px] font-black text-Alumco-blue/70 uppercase tracking-widest flex items-center gap-2">
                            <span class="w-5 h-5 rounded-full bg-Alumco-blue text-white flex items-center justify-center text-[9px]">1</span>
                            Seleccionar columnas a incluir
                        </h4>
                        <button type="button" @click="resetToDefault()" class="text-[16px] font-black uppercase text-Alumco-blue hover:text-Alumco-coral transition-colors flex items-center gap-1.5">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            Restaurar
                        </button>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="(info, key) in allCols" :key="key">
                            <button type="button" 
                                    @click="toggleCol(key)"
                                    :class="selectedKeys.includes(key) ? 'bg-Alumco-blue text-white border-Alumco-blue shadow-md' : 'bg-white text-Alumco-gray/40 border-gray-100 hover:border-Alumco-blue/20'"
                                    class="px-4 py-2 rounded-xl border text-xs font-bold transition-all flex items-center gap-2 active:scale-95">
                                <svg x-show="selectedKeys.includes(key)" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                <svg x-show="!selectedKeys.includes(key)" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                <span x-text="info.label"></span>
                            </button>
                        </template>
                    </div>
                </div>
                
                {{-- Paso 2: Vista Previa y Orden --}}
                <div x-show="selectedKeys.length > 0">
                    <h4 class="text-[11px] font-black text-Alumco-blue/70 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-Alumco-blue text-white flex items-center justify-center text-[9px]">2</span>
                        Vista previa y orden de columnas
                    </h4>
                    <div class="bg-white border border-gray-200 rounded-2xl overflow-x-auto shadow-inner custom-scrollbar">
                        <div id="column-sortable-list" class="flex min-w-max bg-white">
                            <template x-for="(key, index) in selectedKeys" :key="key">
                                <div class="column-drag-item flex flex-col min-w-[180px] border-r border-gray-100 last:border-r-0 bg-white group transition-all" 
                                     draggable="true" 
                                     :data-key="key"
                                     @dragstart.stop="$event.dataTransfer.setData('fromIdx', index); $event.target.classList.add('opacity-40')"
                                     @dragend.stop="$event.target.classList.remove('opacity-40')"
                                     @dragover.prevent
                                     @drop.stop="const from = $event.dataTransfer.getData('fromIdx'); reorder(parseInt(from), index)">
                                    
                                    {{-- Cabecera --}}
                                    <div class="px-5 py-4 bg-gray-50/50 border-b border-gray-100 flex items-center justify-between gap-3 relative">
                                        <span class="text-[11px] font-display font-black text-Alumco-blue uppercase tracking-widest whitespace-nowrap" x-text="allCols[key].label"></span>

                                        <div class="cursor-grab active:cursor-grabbing text-gray-300 hover:text-Alumco-blue/40 transition-colors">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M7 7a2 2 0 100-4 2 2 0 000 4zm0 6a2 2 0 100-4 2 2 0 000 4zm0 6a2 2 0 100-4 2 2 0 000 4zm6-12a2 2 0 100-4 2 2 0 000 4zm0 6a2 2 0 100-4 2 2 0 000 4zm0 6a2 2 0 100-4 2 2 0 000 4z"/></svg>
                                        </div>
                                    </div>

                                    {{-- Datos de Ejemplo --}}
                                    <template x-for="dataText in allCols[key].data">
                                        <div class="px-5 py-3.5 text-xs text-Alumco-gray/60 border-b border-gray-50 last:border-b-0 whitespace-nowrap overflow-hidden text-ellipsis" x-text="dataText"></div>
                                    </template>
                                    
                                    {{-- Pie --}}
                                    <div class="px-5 py-2 bg-gray-50/20 text-[9px] font-bold text-gray-300 uppercase tracking-tighter" x-text="'Columna: ' + key"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Empty State --}}
                <div x-show="selectedKeys.length === 0" class="py-20 text-center border-2 border-dashed border-gray-100 rounded-3xl">
                    <p class="text-Alumco-gray/40 font-bold uppercase tracking-widest text-xs">Selecciona al menos una columna para ver la vista previa</p>
                </div>

                <div class="mt-6 p-4 rounded-2xl bg-amber-50 border border-amber-100 flex gap-3">
                    <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="text-[10px] text-amber-800 font-medium leading-relaxed uppercase tracking-wider">
                        El archivo Excel final respetará exactamente el orden de izquierda a derecha configurado arriba.
                    </p>
                </div>
            </div>
            
            <div class="p-6 border-t border-gray-50 bg-gray-50/30 flex items-center justify-between gap-3">
                <div class="flex-1">
                    <livewire:admin.reporte-presets />
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" onclick="closeExportModal()" class="px-6 py-2.5 text-sm font-bold text-Alumco-gray/50 hover:text-Alumco-coral transition-colors text-center font-display">Cancelar</button>
                    <button type="submit" 
                            :disabled="selectedKeys.length === 0"
                            class="bg-Alumco-green hover:bg-Alumco-green-vivid text-Alumco-blue font-display font-bold py-3 px-10 rounded-xl shadow-lg shadow-Alumco-green/20 transition-all active:scale-95 flex items-center gap-2 disabled:opacity-30 disabled:pointer-events-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Generar Reporte Excel
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const expBackdrop = document.getElementById('export-modal-backdrop');
    const expModal = document.getElementById('export-modal');
    let isExpOpen = false;

    window.openExportModal = function () {
        if (!expBackdrop || !expModal) return;
        window.dispatchEvent(new CustomEvent('open-export-modal'));
        expBackdrop.classList.remove('hidden');
        expModal.classList.remove('hidden');
        void expModal.offsetWidth;
        expBackdrop.classList.remove('opacity-0', 'pointer-events-none');
        expModal.classList.remove('opacity-0', 'pointer-events-none', 'scale-95');
        expModal.classList.add('scale-100');
        isExpOpen = true;
    };

    window.closeExportModal = function () {
        if (!expBackdrop || !expModal) return;
        expBackdrop.classList.add('opacity-0', 'pointer-events-none');
        expModal.classList.add('opacity-0', 'pointer-events-none', 'scale-95');
        expModal.classList.remove('scale-100');
        setTimeout(() => {
            if (!isExpOpen) {
                expBackdrop.classList.add('hidden');
                expModal.classList.add('hidden');
            }
        }, 300);
        isExpOpen = false;
    };

    expBackdrop?.addEventListener('click', window.closeExportModal);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && isExpOpen) window.closeExportModal(); });

    const initializeAgeRangeFilter = () => {
        // --- RANGO ETARIO ---
        const ageRoot = document.getElementById('age-filter-root');
        if (ageRoot) {
            const minSlider = document.getElementById('age-min-slider');
            const maxSlider = document.getElementById('age-max-slider');
            const minValue = document.getElementById('age-min-value');
            const maxValue = document.getElementById('age-max-value');
            const fill = document.getElementById('age-range-fill');
            const minInput = document.getElementById('edad-min-input');
            const maxInput = document.getElementById('edad-max-input');
            
            const minRange = parseInt(minSlider.min);
            const maxRange = parseInt(maxSlider.max);

            const updateRange = () => {
                let v1 = parseInt(minSlider.value);
                let v2 = parseInt(maxSlider.value);
                
                // Asegurar que v1 sea el menor y v2 el mayor
                if (v1 > v2) {
                    [v1, v2] = [v2, v1];
                }
                
                fill.style.left = ((v1 - minRange) / (maxRange - minRange) * 100) + '%';
                fill.style.width = ((v2 - v1) / (maxRange - minRange) * 100) + '%';
                
                minValue.textContent = v1;
                maxValue.textContent = v2;
                
                minInput.value = v1;
                maxInput.value = v2;
            };

            minSlider.oninput = updateRange;
            maxSlider.oninput = updateRange;
            
            // Inicializar
            updateRange();
        }
    };

    initializeAgeRangeFilter();
})();
</script>
@endpush

```

### resources/views/admin/usuarios/index.blade.php
```blade
@extends('layouts.panel')

@section('title', 'Usuarios')
@section('header_title', 'Gestión de Colaboradores')

@section('content')
    <livewire:admin.user-management />
@endsection

```

### resources/views/admin/perfil.blade.php
```blade
@extends('layouts.panel')

@section('title', 'Mi Perfil')
@section('header_title', 'Perfil de Usuario')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-8">
        <h2 class="admin-page-title">Mi Perfil</h2>
        <p class="admin-page-subtitle">Información de tu cuenta administrativa</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Columna Izquierda: Avatar y Rol -->
        <div class="lg:col-span-1">
            <div class="admin-surface p-8 text-center">
                @php
                    $initials = collect(explode(' ', trim($user->name)))
                        ->map(fn($w) => strtoupper($w[0] ?? ''))
                        ->take(2)
                        ->join('');
                @endphp
                <div class="w-24 h-24 rounded-full bg-Alumco-blue/5 text-Alumco-blue flex items-center justify-center mx-auto mb-6 shadow-sm">
                    <span class="font-display font-black text-3xl">{{ $initials }}</span>
                </div>

                <h3 class="font-display font-bold text-xl text-Alumco-gray leading-tight mb-1">{{ $user->name }}</h3>
                <p class="text-sm text-Alumco-gray/50 mb-4">{{ $user->email }}</p>

                <div class="inline-flex items-center px-3 py-1 rounded-full bg-Alumco-blue text-white text-[10px] font-black uppercase tracking-widest">
                    {{ $user->roles->first()?->name ?? 'Administrador' }}
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Detalles -->
        <div class="lg:col-span-2">
            <div class="admin-surface overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-50 bg-gray-50/30">
                    <h4 class="admin-page-eyebrow">Datos Personales</h4>
                </div>
                
                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-1">
                        <p class="text-[11px] font-black text-Alumco-blue/70 uppercase tracking-widest">Nombre Completo</p>
                        <p class="font-bold text-Alumco-gray">{{ $user->name }}</p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-[11px] font-black text-Alumco-blue/70 uppercase tracking-widest">Correo Electrónico</p>
                        <p class="font-bold text-Alumco-gray">{{ $user->email }}</p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-[11px] font-black text-Alumco-blue/70 uppercase tracking-widest">Fecha de Nacimiento</p>
                        <p class="font-bold text-Alumco-gray">
                            {{ $user->fecha_nacimiento ? \Carbon\Carbon::parse($user->fecha_nacimiento)->format('d/m/Y') : 'No registrada' }}
                        </p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-[11px] font-black text-Alumco-blue/70 uppercase tracking-widest">Sexo / Género</p>
                        <p class="font-bold text-Alumco-gray">
                            @if($user->sexo == 'M') Masculino @elseif($user->sexo == 'F') Femenino @else {{ $user->sexo ?? 'No informado' }} @endif
                        </p>
                    </div>
                </div>

                <div class="px-8 py-6 border-b border-t border-gray-50 bg-gray-50/30">
                    <h4 class="admin-page-eyebrow">Organización</h4>
                </div>

                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-1">
                        <p class="text-[11px] font-black text-Alumco-blue/70 uppercase tracking-widest">Sede Asignada</p>
                        <p class="font-bold text-Alumco-gray">{{ $user->sede->nombre ?? 'Sin Sede' }}</p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-[11px] font-black text-Alumco-blue/70 uppercase tracking-widest">Estamento / Área</p>
                        <p class="font-bold text-Alumco-gray">{{ $user->estamento->nombre ?? 'Sin Estamento' }}</p>
                    </div>
                </div>
            </div>
            
            <div class="mt-8 p-6 rounded-[24px] bg-amber-50 border border-amber-100 flex gap-4">
                <svg class="w-6 h-6 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86l-7.1 12.3A1 1 0 004.06 18h15.88a1 1 0 00.87-1.84l-7.1-12.3a1 1 0 00-1.74 0z"></path>
                </svg>
                <div>
                    <p class="text-sm text-amber-800 font-bold mb-1">Cuenta Administrativa</p>
                    <p class="text-xs text-amber-700 leading-relaxed">
                        Tienes acceso a funciones críticas del sistema. Para cambiar tu contraseña o datos sensibles, contacta al soporte técnico o utiliza el flujo de recuperación de contraseña al iniciar sesión.
                    </p>
                </div>
            </div>

            <div class="mt-8 admin-surface p-6">
                <x-accessibility-preferences title="Preferencias de accesibilidad" description="Son las mismas opciones del botón Opciones" />
            </div>
        </div>
    </div>
</div>
@endsection

```

### resources/views/admin/configuracion.blade.php
```blade
@extends('layouts.panel')

@section('title', 'Configuración del Sistema')

@section('header_title', 'Variables de Negocio')

@section('content')
    <div class="space-y-8">
        <div>
            <h2 class="admin-page-title">Configuración Global</h2>
            <p class="admin-page-subtitle">(Solo visible para desarrolladores)</p>
        </div>

        <div class="admin-surface p-6">
            @livewire('dev-config')
        </div>
    </div>
@endsection

```

### Layout principal usado por estas vistas: resources/views/layouts/panel.blade.php
```blade
@php
    $accessibilityPreferences = \App\Support\AccessibilityPreferences::normalize(auth()->user()?->accessibility_preferences);
    $accessibilityFontSize = \App\Support\AccessibilityPreferences::fontSizeFor($accessibilityPreferences['fontLevel']);
@endphp

<!DOCTYPE html>
<html lang="es"
      style="--font-base: {{ $accessibilityFontSize }}px;"
      data-font="{{ $accessibilityPreferences['fontLevel'] }}"
      data-contrast="{{ $accessibilityPreferences['highContrast'] ? 'high' : 'default' }}"
      data-motion="{{ $accessibilityPreferences['reducedMotion'] ? 'reduced' : 'default' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Alumco - @yield('title', 'Panel')</title>
    
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    
    <script>
        (function() {
            try {
                var prefs = JSON.parse(localStorage.getItem('alumco-accessibility'));
                if (prefs) {
                    var levels = [18, 20, 22];
                    document.documentElement.style.setProperty('--font-base', levels[prefs.fontLevel || 0] + 'px');
                    document.documentElement.dataset.font = String(prefs.fontLevel || 0);
                    document.documentElement.dataset.contrast = prefs.highContrast ? 'high' : 'default';
                    document.documentElement.dataset.motion = prefs.reducedMotion ? 'reduced' : 'default';
                }
            } catch (e) {}
        })();
    </script>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @livewireStyles
    
    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 80px;
        }

        .sidebar-transition {
            transition: transform 0.28s cubic-bezier(0.4, 0, 0.2, 1), margin-left 0.28s cubic-bezier(0.4, 0, 0.2, 1);
        }
        [data-motion="reduced"] .sidebar-transition {
            transition: none !important;
        }

        .nav-item-active {
            background-color: rgba(255, 255, 255, 0.1);
            border-right: 4px solid var(--color-Alumco-cyan);
        }

        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(94, 94, 94, 0.2);
            border-radius: 9999px;
        }

        .custom-scrollbar-light::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar-light::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 9999px;
        }
        
        /* Context Menu Styles */
        .context-menu {
            position: fixed;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(94, 94, 94, 0.1);
            z-index: 100;
            min-width: 180px;
            padding: 6px;
            display: none;
        }
        .context-menu.active { display: block; }
        .context-menu-item {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 10px 12px;
            font-size: 14px;
            color: #4A4A4A;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .context-menu-item:hover {
            background-color: #F3F4F6;
            color: #205099;
        }
    </style>
    @stack('css')
    @stack('styles')
</head>

<body class="admin-shell font-sans text-Alumco-gray h-screen flex flex-col overflow-hidden antialiased"
      x-data="{ sidebarOpen: true, toggleSidebar() { this.sidebarOpen = !this.sidebarOpen; } }">
    @persist('admin-nav-progress')
        <div class="nav-progress-bar" data-nav-progress data-active="false" aria-hidden="true"></div>
    @endpersist

    <!-- Topbar -->
    @persist('admin-topbar')
    <header class="admin-topbar admin-topbar-persistent border-b border-white/10 px-6 py-3 flex items-center justify-between z-[80] shrink-0">
        <div class="flex items-center gap-4">
            <button
                @click="toggleSidebar()"
                :aria-label="sidebarOpen ? 'Ocultar menú lateral' : 'Mostrar menú lateral'"
                :aria-expanded="sidebarOpen"
                class="worker-focus admin-topbar-action"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <div class="flex items-center">
                <a href="{{ route('capacitador.dashboard') }}" wire:navigate.hover class="flex items-center text-white">
                    <x-logo-alumco class="h-8 w-auto" width="120" height="32" />
                </a>
            </div>
            <div class="admin-topbar-divider h-6 w-px hidden md:block"></div>
            <h1 class="hidden md:block font-display font-black text-lg text-white tracking-tight">
                @yield('header_title', 'Centro de Gestión')
            </h1>
        </div>

        <div class="flex items-center gap-4">
            @auth
            @include('partials.accessibility-modal', [
                'buttonClass' => 'worker-focus admin-topbar-action hidden sm:inline-flex',
            ])

            @if(auth()->user()->hasAdminAccess())
                <form action="{{ route('admin.preview.toggle') }}" method="POST">
                    @csrf
                    <button type="submit" 
                            data-active="{{ session('preview_mode') ? 'true' : 'false' }}"
                            class="worker-focus admin-topbar-action admin-topbar-action--preview">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        {{ session('preview_mode') ? 'Saliendo de Vista Previa' : 'Ver como Usuario' }}
                    </button>
                </form>
            @endif

            <div class="text-right hidden sm:block">
                <p class="text-[10px] font-black text-white/40 uppercase tracking-widest leading-none mb-0.5">Administrador</p>
                <p class="text-sm font-bold text-white leading-none tracking-tight">{{ auth()->user()->name }}</p>
            </div>
            @php
                $initials = collect(explode(' ', trim(auth()->user()->name)))
                    ->map(fn($w) => strtoupper($w[0] ?? ''))
                    ->take(2)
                    ->join('');
            @endphp
            <a href="{{ route('admin.perfil.index') }}"
               wire:navigate.hover
               class="worker-focus admin-avatar-button select-none">
                {{ $initials }}
            </a>
            <div class="sm:hidden">
                @include('partials.accessibility-modal', [
                    'buttonClass' => 'worker-focus admin-icon-button',
                    'showLabel' => false,
                ])
            </div>
            @endauth
        </div>
    </header>
    @endpersist

    <div class="flex-1 flex overflow-hidden">

        <!-- Expandable Sidebar -->
        <aside id="sidebar"
               class="admin-sidebar sidebar-transition bg-Alumco-blue flex flex-col z-[70] shrink-0 overflow-hidden w-72"
               :style="sidebarOpen ? '' : 'transform: translateX(-100%); margin-left: -18rem'">
            
            <div class="flex-1 py-5 px-2 flex flex-col gap-1.5 overflow-y-auto custom-scrollbar border-r border-white/10 min-w-[18rem]">
                
                @if(session('preview_mode'))
                    {{-- Opciones de Trabajador en Vista Previa --}}
                    <h2 class="admin-sidebar-section-label mb-2 select-none">Vista Previa: Trabajador</h2>
                    
                    <x-nav-link-admin href="{{ route('cursos.index') }}" :active="request()->routeIs('cursos.*')" title="Mis Cursos">
                        <x-slot name="icon">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </x-slot>
                        Mis Cursos
                    </x-nav-link-admin>

                    <x-nav-link-admin href="{{ route('calendario-cursos.index') }}" :active="request()->routeIs('calendario-cursos.*')" title="Calendario">
                        <x-slot name="icon">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </x-slot>
                        Calendario
                    </x-nav-link-admin>

                    <x-nav-link-admin href="{{ route('mis-certificados.index') }}" :active="request()->routeIs('mis-certificados.*')" title="Mis Certificados">
                        <x-slot name="icon">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </x-slot>
                        Mis Certificados
                    </x-nav-link-admin>

                @else
                {{-- Dashboard --}}
                @if(auth()->user()->isCapacitador() || auth()->user()->hasAdminAccess())
                <x-nav-link-admin href="{{ route('capacitador.dashboard') }}" :active="request()->routeIs('capacitador.dashboard')" title="Dashboard">
                    <x-slot name="icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </x-slot>
                    Dashboard
                </x-nav-link-admin>
                @endif

                {{-- Mis Cursos / Gestión Contenido --}}
                @if(auth()->user()->isCapacitador() || auth()->user()->hasAdminAccess())
                <x-nav-link-admin href="{{ route('capacitador.cursos.index') }}" :active="request()->routeIs('capacitador.*cursos*')" title="Contenido">
                    <x-slot name="icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </x-slot>
                    Cursos y Material
                </x-nav-link-admin>
                @endif

                {{-- Reportes --}}
                @if(auth()->user()->hasAdminAccess())
                <x-nav-link-admin href="{{ route('admin.reportes.index') }}" :active="request()->routeIs('admin.reportes.*')" title="Reportes">
                    <x-slot name="icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </x-slot>
                    Reportes Académicos
                </x-nav-link-admin>
                @endif

                {{-- Usuarios --}}
                @if(auth()->user()->hasAdminAccess())
                <x-nav-link-admin href="{{ route('admin.usuarios.index') }}" :active="request()->routeIs('admin.usuarios.*')" title="Usuarios">
                    <x-slot name="icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </x-slot>
                    Directorio de usuarios
                </x-nav-link-admin>
                @endif

                {{-- Configuración Global (Solo Dev) --}}
                @if(auth()->user()->isDesarrollador())
                <x-nav-link-admin href="{{ route('dev.configuracion') }}" :active="request()->routeIs('dev.configuracion')" title="Variables">
                    <x-slot name="icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </x-slot>
                    Variables de Sistema
                </x-nav-link-admin>
                @endif

                {{-- Calendario --}}
                @if(auth()->user()->isCapacitador() || auth()->user()->hasAdminAccess())
                <x-nav-link-admin href="{{ route('capacitador.calendario.index') }}" :active="request()->routeIs('capacitador.calendario.*')" title="Calendario">
                    <x-slot name="icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </x-slot>
                    Calendario Institucional
                </x-nav-link-admin>
                @endif
                @endif
            </div>

            <!-- Footer Sidebar: Cerrar sesión -->
            <div class="p-3 border-t border-white/10 min-w-[18rem]">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="admin-sidebar-link worker-focus w-full text-left text-white/70 hover:text-Alumco-coral hover:bg-Alumco-coral/10 group"
                            title="Cerrar sesión">
                        <svg class="w-6 h-6 shrink-0 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span class="font-medium whitespace-nowrap overflow-hidden text-ellipsis">Cerrar Sesión</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-6 lg:p-10">
            @php
                $navigationPageKind = trim($__env->yieldContent('page_kind')) ?: 'dashboard';
            @endphp
            <div id="admin-content-{{ md5(request()->fullUrl()) }}"
                 class="max-w-[1600px] mx-auto animate-page-entry"
                 data-nav-content
                 data-page-kind="{{ $navigationPageKind }}"
                 aria-busy="false">
                <div class="nav-skeleton nav-skeleton--dense" data-nav-skeleton aria-hidden="true">
                    <div class="nav-skeleton__row nav-skeleton__title"></div>
                    <div class="nav-skeleton__grid nav-skeleton__grid--three">
                        <div class="nav-skeleton__row"></div>
                        <div class="nav-skeleton__row"></div>
                        <div class="nav-skeleton__row"></div>
                    </div>
                    <div class="nav-skeleton__row nav-skeleton__table"></div>
                </div>
                @yield('content')
            </div>
        </main>
    </div>

    @yield('modals')

    @livewireScripts
    @stack('scripts')
    @include('partials.accessibility-scripts')
</body>
</html>

```

## BLOQUE 5 — Modelos y relaciones clave
Modelos incluidos: `Curso`, `User`, `Certificado`, `Sede`, `Estamento`, `PlanificacionCurso`, `ProgresoModulo`, `Modulo`, `SeccionCurso`.
### app/Models/Curso.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curso extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo',
        'descripcion',
        'imagen_portada',
        'color_promedio',
        'capacitador_id',
        'curso_original_id',
    ];

    protected $casts = [
    ];

    // --- RELACIONES ---

    public function capacitador()
    {
        return $this->belongsTo(User::class, 'capacitador_id');
    }

    public function cursoOriginal()
    {
        return $this->belongsTo(Curso::class, 'curso_original_id');
    }

    public function versionesDerivadas()
    {
        return $this->hasMany(Curso::class, 'curso_original_id');
    }

    public function estamentos()
    {
        return $this->belongsToMany(Estamento::class);
    }

    public function modulos()
    {
        return $this->hasMany(Modulo::class)->orderBy('orden');
    }

    public function secciones(): HasMany
    {
        return $this->hasMany(SeccionCurso::class, 'curso_id')->orderBy('orden');
    }

    public function planificaciones(): HasMany
    {
        return $this->hasMany(PlanificacionCurso::class);
    }

    // --- LÓGICA DE NEGOCIO ---

    public function estaDisponible(): bool
    {
        $hoy = now()->startOfDay();

        return $this->planificaciones()
            ->where('fecha_inicio', '<=', $hoy)
            ->where('fecha_fin', '>=', $hoy)
            ->exists();
    }

    public function estaDisponiblePara(User $user): bool
    {
        $hoy = now()->startOfDay();

        return $this->planificaciones()
            ->where('fecha_inicio', '<=', $hoy)
            ->where('fecha_fin', '>=', $hoy)
            ->where(fn ($q) => $q->whereNull('sede_id')->orWhere('sede_id', $user->sede_id))
            ->exists();
    }

    public function progresoParaUsuario(User $user): int
    {
        $total = $this->modulos->count();

        if ($total === 0) {
            return 0;
        }

        $completados = $this->modulos
            ->filter(fn (Modulo $m) => $m->estaCompletadoPor($user))
            ->count();

        return (int) round(($completados / $total) * 100);
    }
}

```

### app/Models/User.php
```php
<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use App\Services\Authorization\UserHierarchyService;
use App\Support\AccessibilityPreferences;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'rut', 'password', 'fecha_nacimiento', 'sexo', 'activo', 'accessibility_preferences', 'firma_digital', 'sede_id', 'estamento_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * @return Attribute<array{fontLevel: int, highContrast: bool, reducedMotion: bool}, array<string, mixed>|null>
     */
    protected function accessibilityPreferences(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value): array {
                $decoded = is_array($value) ? $value : json_decode($value ?? '[]', true);

                return AccessibilityPreferences::normalize(is_array($decoded) ? $decoded : null);
            },
            set: fn (?array $value): string => json_encode(
                AccessibilityPreferences::normalize($value),
                JSON_THROW_ON_ERROR
            ),
        );
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class);
    }

    public function estamento()
    {
        return $this->belongsTo(Estamento::class);
    }

    public function cursosImpartidos()
    {
        return $this->hasMany(Curso::class, 'capacitador_id');
    }

    public function certificados()
    {
        return $this->hasMany(Certificado::class);
    }

    public function progresos()
    {
        return $this->hasMany(ProgresoModulo::class);
    }

    public function isDesarrollador(): bool
    {
        return $this->hasRole('Desarrollador');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('Administrador');
    }

    public function hasAdminAccess(): bool
    {
        return $this->isDesarrollador() || $this->isAdmin();
    }

    public function isCapacitadorInterno(): bool
    {
        return $this->hasRole('Capacitador Interno');
    }

    public function isCapacitadorExterno(): bool
    {
        return $this->hasRole('Capacitador Externo');
    }

    public function isCapacitador(): bool
    {
        return $this->hasAnyRole(['Capacitador Interno', 'Capacitador Externo']);
    }

    public function isTrabajador(): bool
    {
        return $this->hasRole('Trabajador');
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function getHierarchyRank(): int
    {
        return app(UserHierarchyService::class)->getHierarchyRank($this);
    }

    public function canManageUser(User $targetUser): bool
    {
        return app(UserHierarchyService::class)->canManageUser($this, $targetUser);
    }
}

```

### app/Models/Certificado.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificado extends Model
{
    protected $fillable = [
        'user_id',
        'curso_id',
        'codigo_verificacion',
        'ruta_pdf',
        'fecha_emision',
    ];

    protected $casts = [
        'fecha_emision' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }
}

```

### app/Models/Sede.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sede extends Model
{
    use SoftDeletes;

    // Permitir la asignación masiva del campo nombre
    protected $fillable = ['nombre'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function planificaciones()
    {
        return $this->hasMany(PlanificacionCurso::class);
    }
}

```

### app/Models/Estamento.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Estamento extends Model
{
    use SoftDeletes;

    // Permitir la asignación masiva del campo nombre
    protected $fillable = ['nombre'];

    // Relación: Un estamento tiene muchos usuarios
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Relación: Un estamento tiene acceso a muchos cursos
    public function cursos()
    {
        return $this->belongsToMany(Curso::class);
    }
}

```

### app/Models/PlanificacionCurso.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanificacionCurso extends Model
{
    protected $table = 'planificaciones_cursos';

    protected $fillable = [
        'curso_id',
        'sede_id',
        'fecha_inicio',
        'fecha_fin',
        'notas',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    // --- RELACIONES ---

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    public function sede(): BelongsTo
    {
        return $this->belongsTo(Sede::class);
    }

    // --- LÓGICA ---

    public function estaActivo(): bool
    {
        $hoy = now()->startOfDay();

        return $this->fecha_inicio->lte($hoy) && $this->fecha_fin->gte($hoy);
    }
}

```

### app/Models/ProgresoModulo.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgresoModulo extends Model
{
    protected $table = 'progresos_modulo'; // Nombre exacto de la tabla

    protected $fillable = ['user_id', 'modulo_id', 'completado', 'fecha_completado'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function modulo()
    {
        return $this->belongsTo(Modulo::class);
    }
}

```

### app/Models/Modulo.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    use HasFactory;

    const TIPOS = ['video', 'pdf', 'ppt', 'texto', 'imagen', 'evaluacion'];

    const TIPO_LABELS = [
        'video' => 'video',
        'pdf' => 'documento',
        'ppt' => 'presentación',
        'texto' => 'texto',
        'imagen' => 'imagen',
        'evaluacion' => 'evaluación',
    ];

    protected $fillable = [
        'curso_id',
        'seccion_id',
        'titulo',
        'orden',
        'tipo_contenido',
        'ruta_archivo',
        'nombre_archivo_original',
        'contenido',
        'duracion_minutos',
    ];

    // --- RELACIONES ---

    public function curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function seccion()
    {
        return $this->belongsTo(SeccionCurso::class, 'seccion_id');
    }

    public function evaluacion()
    {
        return $this->hasOne(Evaluacion::class);
    }

    public function progresos()
    {
        return $this->hasMany(ProgresoModulo::class);
    }

    // --- MÉTODOS HELPER ---

    public function estaCompletadoPor(User $user): bool
    {
        // Usa relación cargada para evitar queries adicionales en bucles
        if ($this->relationLoaded('progresos')) {
            return $this->progresos
                ->where('user_id', $user->id)
                ->where('completado', true)
                ->isNotEmpty();
        }

        return $this->progresos()
            ->where('user_id', $user->id)
            ->where('completado', true)
            ->exists();
    }

    public function estaAccesiblePara(User $user, Curso $curso): bool
    {
        // Si el módulo ya está completado, siempre es accesible
        if ($this->estaCompletadoPor($user)) {
            return true;
        }

        // Buscar el módulo anterior basado en el orden global del curso
        $anterior = $curso->modulos->where('orden', '<', $this->orden)->last();

        if (! $anterior) {
            return true;
        }

        return $anterior->estaCompletadoPor($user);
    }
}

```

### app/Models/SeccionCurso.php
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeccionCurso extends Model
{
    use HasFactory;

    protected $table = 'seccion_cursos';

    protected $fillable = [
        'curso_id',
        'titulo',
        'orden',
    ];

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    public function modulos(): HasMany
    {
        return $this->hasMany(Modulo::class, 'seccion_id')->orderBy('orden');
    }
}

```

## BLOQUE 6 — Migraciones relevantes
### Coincidencias encontradas por nombre
- `cursos`: `0001_01_01_000003_create_users_table.php`, `2026_04_07_100001_add_es_secuencial_to_cursos_table.php`, `2026_04_13_230001_create_planificaciones_cursos_table.php`, `2026_04_13_230002_migrate_curso_dates_to_planificaciones.php`, `2026_04_15_000001_add_sede_id_to_planificaciones_cursos.php`, `2026_04_26_052432_drop_availability_dates_from_cursos_table.php`, `2026_04_26_053515_add_curso_original_id_to_cursos_table.php`, `2026_04_26_060221_remove_es_secuencial_from_cursos_table.php`, `2026_05_01_194502_add_color_promedio_to_cursos_table.php`, `2026_05_02_012216_create_seccion_cursos_table.php`
- `usuarios/users`: `0001_01_01_000003_create_users_table.php`, `2026_04_26_020915_migrate_existing_users_to_roles.php`, `2026_04_26_063057_add_firma_digital_to_users_table.php`, `2026_04_28_060748_add_rut_to_users_table.php`, `2026_05_02_120000_add_accessibility_preferences_to_users_table.php`, `2026_05_05_041410_add_indexes_to_users_table.php`
- `participantes`: [ARCHIVO NO ENCONTRADO]
- `inscripciones`: [ARCHIVO NO ENCONTRADO]
- `certificados`: [ARCHIVO NO ENCONTRADO]
- `sedes`: [ARCHIVO NO ENCONTRADO]
### database/migrations/0001_01_01_000003_create_users_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. TABLAS INDEPENDIENTES (Sin claves foráneas)
        Schema::create('sedes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->timestamps();
        });

        Schema::create('estamentos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->timestamps();
        });

        // 2. USUARIOS Y SISTEMA LARAVEL (Depende de sedes y estamentos)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->date('fecha_nacimiento')->nullable();
            $table->enum('sexo', ['F', 'M', 'Otro'])->nullable();
            $table->boolean('activo')->default(true);
            $table->foreignId('sede_id')->nullable()->constrained('sedes')->nullOnDelete();
            $table->foreignId('estamento_id')->nullable()->constrained('estamentos')->nullOnDelete();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // 3. CURSOS (Depende de users)
        Schema::create('cursos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->string('imagen_portada')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->foreignId('capacitador_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('curso_estamento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained()->cascadeOnDelete();
            $table->foreignId('estamento_id')->constrained()->cascadeOnDelete();
        });

        // 4. MÓDULOS (Depende de cursos)
        Schema::create('modulos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained()->cascadeOnDelete();
            $table->string('titulo');
            $table->integer('orden');
            $table->enum('tipo_contenido', ['video', 'pdf', 'ppt']);
            $table->string('ruta_archivo')->nullable();
            $table->timestamps();
        });

        Schema::create('progresos_modulo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('modulo_id')->constrained()->cascadeOnDelete();
            $table->boolean('completado')->default(false);
            $table->timestamp('fecha_completado')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'modulo_id']);
        });

        // 5. CERTIFICADOS
        Schema::create('certificados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('curso_id')->constrained()->cascadeOnDelete();
            $table->string('codigo_verificacion')->unique();
            $table->string('ruta_pdf');
            $table->timestamp('fecha_emision')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // El orden de borrado es inverso a la creación
        Schema::dropIfExists('certificados');
        Schema::dropIfExists('progresos_modulo');
        Schema::dropIfExists('modulos');
        Schema::dropIfExists('curso_estamento');
        Schema::dropIfExists('cursos');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('estamentos');
        Schema::dropIfExists('sedes');
    }
};

```

### database/migrations/2026_04_07_100001_add_es_secuencial_to_cursos_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->boolean('es_secuencial')->default(true)->after('capacitador_id');
        });
    }

    public function down(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->dropColumn('es_secuencial');
        });
    }
};

```

### database/migrations/2026_04_13_230001_create_planificaciones_cursos_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planificaciones_cursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('cursos')->cascadeOnDelete();
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index(['curso_id', 'fecha_inicio', 'fecha_fin']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planificaciones_cursos');
    }
};

```

### database/migrations/2026_04_13_230002_migrate_curso_dates_to_planificaciones.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('cursos')
            ->whereNotNull('fecha_inicio')
            ->whereNotNull('fecha_fin')
            ->orderBy('id')
            ->each(function ($curso) {
                DB::table('planificaciones_cursos')->insert([
                    'curso_id' => $curso->id,
                    'fecha_inicio' => $curso->fecha_inicio,
                    'fecha_fin' => $curso->fecha_fin,
                    'notas' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        // Pasar a nullable para no romper vistas que aún las leen
        Schema::table('cursos', function (Blueprint $table) {
            $table->date('fecha_inicio')->nullable()->change();
            $table->date('fecha_fin')->nullable()->change();
        });
    }

    public function down(): void
    {
        $planificaciones = DB::table('planificaciones_cursos')
            ->orderBy('curso_id')
            ->orderBy('fecha_inicio')
            ->get()
            ->unique('curso_id');

        foreach ($planificaciones as $p) {
            DB::table('cursos')->where('id', $p->curso_id)->update([
                'fecha_inicio' => $p->fecha_inicio,
                'fecha_fin' => $p->fecha_fin,
            ]);
        }

        DB::table('planificaciones_cursos')->delete();
    }
};

```

### database/migrations/2026_04_15_000001_add_sede_id_to_planificaciones_cursos.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planificaciones_cursos', function (Blueprint $table) {
            $table->foreignId('sede_id')
                ->nullable()
                ->after('curso_id')
                ->constrained('sedes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('planificaciones_cursos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sede_id');
        });
    }
};

```

### database/migrations/2026_04_26_020915_migrate_existing_users_to_roles.php
```php
<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Asegurarnos de que los roles existan (por si acaso no se corrió el seeder)
        $roles = [
            'Desarrollador',
            'Administrador',
            'Capacitador Interno',
            'Capacitador Externo',
            'Trabajador',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Obtener todos los usuarios con sus estamentos
        $users = User::with('estamento')->get();

        foreach ($users as $user) {
            if (! $user->estamento) {
                $user->assignRole('Trabajador');

                continue;
            }

            switch ($user->estamento->nombre) {
                case 'Desarrollador':
                    $user->assignRole('Desarrollador');
                    break;
                case 'Administrador':
                    $user->assignRole('Administrador');
                    break;
                case 'Capacitador Interno':
                    $user->assignRole('Capacitador Interno');
                    break;
                case 'Capacitador Externo':
                    $user->assignRole('Capacitador Externo');
                    break;
                default:
                    $user->assignRole('Trabajador');
                    break;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar todas las asignaciones de roles
        foreach (User::all() as $user) {
            $user->roles()->detach();
        }
    }
};

```

### database/migrations/2026_04_26_052432_drop_availability_dates_from_cursos_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->dropColumn(['fecha_inicio', 'fecha_fin']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
        });
    }
};

```

### database/migrations/2026_04_26_053515_add_curso_original_id_to_cursos_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->foreignId('curso_original_id')->after('capacitador_id')->nullable()->constrained('cursos')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('curso_original_id');
        });
    }
};

```

### database/migrations/2026_04_26_060221_remove_es_secuencial_from_cursos_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->dropColumn('es_secuencial');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->boolean('es_secuencial')->default(true)->after('capacitador_id');
        });
    }
};

```

### database/migrations/2026_04_26_063057_add_firma_digital_to_users_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('firma_digital')->nullable()->after('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('firma_digital');
        });
    }
};

```

### database/migrations/2026_04_28_060748_add_rut_to_users_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('rut')->nullable()->unique()->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('rut');
        });
    }
};

```

### database/migrations/2026_05_01_194502_add_color_promedio_to_cursos_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->string('color_promedio', 7)->nullable()->after('imagen_portada');
        });
    }

    public function down(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->dropColumn('color_promedio');
        });
    }
};

```

### database/migrations/2026_05_02_012216_create_seccion_cursos_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('seccion_cursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained()->cascadeOnDelete();
            $table->string('titulo');
            $table->integer('orden')->default(0);
            $table->timestamps();
        });

        Schema::table('modulos', function (Blueprint $table) {
            $table->foreignId('seccion_id')->nullable()->constrained('seccion_cursos')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('modulos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('seccion_id');
        });
        Schema::dropIfExists('seccion_cursos');
    }
};

```

### database/migrations/2026_05_02_120000_add_accessibility_preferences_to_users_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->json('accessibility_preferences')->nullable()->after('activo');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('accessibility_preferences');
        });
    }
};

```

### database/migrations/2026_05_05_041410_add_indexes_to_users_table.php
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index(['estamento_id', 'sede_id'], 'users_estamento_sede_index');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_estamento_sede_index');
        });
    }
};

```

## BLOQUE 7 — Navegación lateral actual
Archivo de navegación/sidebar principal para admin y capacitador: `resources/views/layouts/panel.blade.php`.
```blade
@php
    $accessibilityPreferences = \App\Support\AccessibilityPreferences::normalize(auth()->user()?->accessibility_preferences);
    $accessibilityFontSize = \App\Support\AccessibilityPreferences::fontSizeFor($accessibilityPreferences['fontLevel']);
@endphp

<!DOCTYPE html>
<html lang="es"
      style="--font-base: {{ $accessibilityFontSize }}px;"
      data-font="{{ $accessibilityPreferences['fontLevel'] }}"
      data-contrast="{{ $accessibilityPreferences['highContrast'] ? 'high' : 'default' }}"
      data-motion="{{ $accessibilityPreferences['reducedMotion'] ? 'reduced' : 'default' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Alumco - @yield('title', 'Panel')</title>
    
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    
    <script>
        (function() {
            try {
                var prefs = JSON.parse(localStorage.getItem('alumco-accessibility'));
                if (prefs) {
                    var levels = [18, 20, 22];
                    document.documentElement.style.setProperty('--font-base', levels[prefs.fontLevel || 0] + 'px');
                    document.documentElement.dataset.font = String(prefs.fontLevel || 0);
                    document.documentElement.dataset.contrast = prefs.highContrast ? 'high' : 'default';
                    document.documentElement.dataset.motion = prefs.reducedMotion ? 'reduced' : 'default';
                }
            } catch (e) {}
        })();
    </script>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @livewireStyles
    
    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 80px;
        }

        .sidebar-transition {
            transition: transform 0.28s cubic-bezier(0.4, 0, 0.2, 1), margin-left 0.28s cubic-bezier(0.4, 0, 0.2, 1);
        }
        [data-motion="reduced"] .sidebar-transition {
            transition: none !important;
        }

        .nav-item-active {
            background-color: rgba(255, 255, 255, 0.1);
            border-right: 4px solid var(--color-Alumco-cyan);
        }

        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(94, 94, 94, 0.2);
            border-radius: 9999px;
        }

        .custom-scrollbar-light::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar-light::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 9999px;
        }
        
        /* Context Menu Styles */
        .context-menu {
            position: fixed;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(94, 94, 94, 0.1);
            z-index: 100;
            min-width: 180px;
            padding: 6px;
            display: none;
        }
        .context-menu.active { display: block; }
        .context-menu-item {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 10px 12px;
            font-size: 14px;
            color: #4A4A4A;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .context-menu-item:hover {
            background-color: #F3F4F6;
            color: #205099;
        }
    </style>
    @stack('css')
    @stack('styles')
</head>

<body class="admin-shell font-sans text-Alumco-gray h-screen flex flex-col overflow-hidden antialiased"
      x-data="{ sidebarOpen: true, toggleSidebar() { this.sidebarOpen = !this.sidebarOpen; } }">
    @persist('admin-nav-progress')
        <div class="nav-progress-bar" data-nav-progress data-active="false" aria-hidden="true"></div>
    @endpersist

    <!-- Topbar -->
    @persist('admin-topbar')
    <header class="admin-topbar admin-topbar-persistent border-b border-white/10 px-6 py-3 flex items-center justify-between z-[80] shrink-0">
        <div class="flex items-center gap-4">
            <button
                @click="toggleSidebar()"
                :aria-label="sidebarOpen ? 'Ocultar menú lateral' : 'Mostrar menú lateral'"
                :aria-expanded="sidebarOpen"
                class="worker-focus admin-topbar-action"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <div class="flex items-center">
                <a href="{{ route('capacitador.dashboard') }}" wire:navigate.hover class="flex items-center text-white">
                    <x-logo-alumco class="h-8 w-auto" width="120" height="32" />
                </a>
            </div>
            <div class="admin-topbar-divider h-6 w-px hidden md:block"></div>
            <h1 class="hidden md:block font-display font-black text-lg text-white tracking-tight">
                @yield('header_title', 'Centro de Gestión')
            </h1>
        </div>

        <div class="flex items-center gap-4">
            @auth
            @include('partials.accessibility-modal', [
                'buttonClass' => 'worker-focus admin-topbar-action hidden sm:inline-flex',
            ])

            @if(auth()->user()->hasAdminAccess())
                <form action="{{ route('admin.preview.toggle') }}" method="POST">
                    @csrf
                    <button type="submit" 
                            data-active="{{ session('preview_mode') ? 'true' : 'false' }}"
                            class="worker-focus admin-topbar-action admin-topbar-action--preview">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        {{ session('preview_mode') ? 'Saliendo de Vista Previa' : 'Ver como Usuario' }}
                    </button>
                </form>
            @endif

            <div class="text-right hidden sm:block">
                <p class="text-[10px] font-black text-white/40 uppercase tracking-widest leading-none mb-0.5">Administrador</p>
                <p class="text-sm font-bold text-white leading-none tracking-tight">{{ auth()->user()->name }}</p>
            </div>
            @php
                $initials = collect(explode(' ', trim(auth()->user()->name)))
                    ->map(fn($w) => strtoupper($w[0] ?? ''))
                    ->take(2)
                    ->join('');
            @endphp
            <a href="{{ route('admin.perfil.index') }}"
               wire:navigate.hover
               class="worker-focus admin-avatar-button select-none">
                {{ $initials }}
            </a>
            <div class="sm:hidden">
                @include('partials.accessibility-modal', [
                    'buttonClass' => 'worker-focus admin-icon-button',
                    'showLabel' => false,
                ])
            </div>
            @endauth
        </div>
    </header>
    @endpersist

    <div class="flex-1 flex overflow-hidden">

        <!-- Expandable Sidebar -->
        <aside id="sidebar"
               class="admin-sidebar sidebar-transition bg-Alumco-blue flex flex-col z-[70] shrink-0 overflow-hidden w-72"
               :style="sidebarOpen ? '' : 'transform: translateX(-100%); margin-left: -18rem'">
            
            <div class="flex-1 py-5 px-2 flex flex-col gap-1.5 overflow-y-auto custom-scrollbar border-r border-white/10 min-w-[18rem]">
                
                @if(session('preview_mode'))
                    {{-- Opciones de Trabajador en Vista Previa --}}
                    <h2 class="admin-sidebar-section-label mb-2 select-none">Vista Previa: Trabajador</h2>
                    
                    <x-nav-link-admin href="{{ route('cursos.index') }}" :active="request()->routeIs('cursos.*')" title="Mis Cursos">
                        <x-slot name="icon">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </x-slot>
                        Mis Cursos
                    </x-nav-link-admin>

                    <x-nav-link-admin href="{{ route('calendario-cursos.index') }}" :active="request()->routeIs('calendario-cursos.*')" title="Calendario">
                        <x-slot name="icon">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </x-slot>
                        Calendario
                    </x-nav-link-admin>

                    <x-nav-link-admin href="{{ route('mis-certificados.index') }}" :active="request()->routeIs('mis-certificados.*')" title="Mis Certificados">
                        <x-slot name="icon">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </x-slot>
                        Mis Certificados
                    </x-nav-link-admin>

                @else
                {{-- Dashboard --}}
                @if(auth()->user()->isCapacitador() || auth()->user()->hasAdminAccess())
                <x-nav-link-admin href="{{ route('capacitador.dashboard') }}" :active="request()->routeIs('capacitador.dashboard')" title="Dashboard">
                    <x-slot name="icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </x-slot>
                    Dashboard
                </x-nav-link-admin>
                @endif

                {{-- Mis Cursos / Gestión Contenido --}}
                @if(auth()->user()->isCapacitador() || auth()->user()->hasAdminAccess())
                <x-nav-link-admin href="{{ route('capacitador.cursos.index') }}" :active="request()->routeIs('capacitador.*cursos*')" title="Contenido">
                    <x-slot name="icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </x-slot>
                    Cursos y Material
                </x-nav-link-admin>
                @endif

                {{-- Reportes --}}
                @if(auth()->user()->hasAdminAccess())
                <x-nav-link-admin href="{{ route('admin.reportes.index') }}" :active="request()->routeIs('admin.reportes.*')" title="Reportes">
                    <x-slot name="icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </x-slot>
                    Reportes Académicos
                </x-nav-link-admin>
                @endif

                {{-- Usuarios --}}
                @if(auth()->user()->hasAdminAccess())
                <x-nav-link-admin href="{{ route('admin.usuarios.index') }}" :active="request()->routeIs('admin.usuarios.*')" title="Usuarios">
                    <x-slot name="icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </x-slot>
                    Directorio de usuarios
                </x-nav-link-admin>
                @endif

                {{-- Configuración Global (Solo Dev) --}}
                @if(auth()->user()->isDesarrollador())
                <x-nav-link-admin href="{{ route('dev.configuracion') }}" :active="request()->routeIs('dev.configuracion')" title="Variables">
                    <x-slot name="icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </x-slot>
                    Variables de Sistema
                </x-nav-link-admin>
                @endif

                {{-- Calendario --}}
                @if(auth()->user()->isCapacitador() || auth()->user()->hasAdminAccess())
                <x-nav-link-admin href="{{ route('capacitador.calendario.index') }}" :active="request()->routeIs('capacitador.calendario.*')" title="Calendario">
                    <x-slot name="icon">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </x-slot>
                    Calendario Institucional
                </x-nav-link-admin>
                @endif
                @endif
            </div>

            <!-- Footer Sidebar: Cerrar sesión -->
            <div class="p-3 border-t border-white/10 min-w-[18rem]">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="admin-sidebar-link worker-focus w-full text-left text-white/70 hover:text-Alumco-coral hover:bg-Alumco-coral/10 group"
                            title="Cerrar sesión">
                        <svg class="w-6 h-6 shrink-0 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span class="font-medium whitespace-nowrap overflow-hidden text-ellipsis">Cerrar Sesión</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-6 lg:p-10">
            @php
                $navigationPageKind = trim($__env->yieldContent('page_kind')) ?: 'dashboard';
            @endphp
            <div id="admin-content-{{ md5(request()->fullUrl()) }}"
                 class="max-w-[1600px] mx-auto animate-page-entry"
                 data-nav-content
                 data-page-kind="{{ $navigationPageKind }}"
                 aria-busy="false">
                <div class="nav-skeleton nav-skeleton--dense" data-nav-skeleton aria-hidden="true">
                    <div class="nav-skeleton__row nav-skeleton__title"></div>
                    <div class="nav-skeleton__grid nav-skeleton__grid--three">
                        <div class="nav-skeleton__row"></div>
                        <div class="nav-skeleton__row"></div>
                        <div class="nav-skeleton__row"></div>
                    </div>
                    <div class="nav-skeleton__row nav-skeleton__table"></div>
                </div>
                @yield('content')
            </div>
        </main>
    </div>

    @yield('modals')

    @livewireScripts
    @stack('scripts')
    @include('partials.accessibility-scripts')
</body>
</html>

```

## BLOQUE 8 — Configuración de assets y gráficos
Se encontró `chart.js` en `package.json` y su import directa en `resources/js/app.js`. No encontré `apexcharts` en `package.json` ni en `resources/js/`.
### package.json
```json
{
    "$schema": "https://www.schemastore.org/package.json",
    "private": true,
    "type": "module",
    "scripts": {
        "build": "vite build",
        "dev": "vite"
    },
    "devDependencies": {
        "@tailwindcss/vite": "4.2.4",
        "axios": "1.16.0",
        "concurrently": "^9.0.1",
        "laravel-vite-plugin": "3.1.0",
        "tailwindcss": "4.2.4",
        "vite": "8.0.10"
    },
    "dependencies": {
        "@fontsource/fira-sans": "^5.2.7",
        "@fontsource/sora": "^5.2.8",
        "@fontsource/ubuntu": "^5.2.8",
        "chart.js": "^4.5.1",
        "pdfjs-dist": "^5.7.284"
    }
}

```

### vite.config.js
```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});

```

### resources/js/app.js
```js
import './bootstrap';
import Chart from 'chart.js/auto';

window.Chart = Chart;

const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

class ModulePdfViewer {
    constructor(element, pdfjsLib) {
        this.element = element;
        this.pdfjsLib = pdfjsLib;
        this.url = element.dataset.pdfUrl;
        this.canvas = element.querySelector('[data-pdf-canvas]');
        this.context = this.canvas?.getContext('2d');
        this.status = element.querySelector('[data-pdf-status]');
        this.currentPageOutput = element.querySelector('[data-pdf-current-page]');
        this.totalPagesOutput = element.querySelector('[data-pdf-total-pages]');
        this.previousButton = element.querySelector('[data-pdf-previous]');
        this.nextButton = element.querySelector('[data-pdf-next]');
        this.zoomInButton = element.querySelector('[data-pdf-zoom-in]');
        this.zoomOutButton = element.querySelector('[data-pdf-zoom-out]');
        this.scale = 1.1;
        this.pageNumber = 1;
        this.pageCount = 0;
        this.renderTask = null;
        this.pendingRender = false;
        this.document = null;

        if (! this.url || ! this.canvas || ! this.context) {
            return;
        }

        this.bindControls();
        this.load();
    }

    bindControls() {
        this.previousButton?.addEventListener('click', () => this.goToPage(this.pageNumber - 1));
        this.nextButton?.addEventListener('click', () => this.goToPage(this.pageNumber + 1));
        this.zoomOutButton?.addEventListener('click', () => this.setScale(this.scale - 0.15));
        this.zoomInButton?.addEventListener('click', () => this.setScale(this.scale + 0.15));
        window.addEventListener('resize', () => this.renderPage(), { passive: true });
    }

    async load() {
        this.setStatus('Cargando documento...');

        try {
            this.document = await this.pdfjsLib.getDocument({
                url: this.url,
                withCredentials: true,
            }).promise;

            this.pageCount = this.document.numPages;
            this.totalPagesOutput.textContent = String(this.pageCount);
            this.setStatus('');
            await this.renderPage();
        } catch (error) {
            console.error('No se pudo cargar el PDF del módulo.', error);
            this.setStatus('No se pudo mostrar el PDF en el visor.');
            this.element.dataset.pdfState = 'error';
        }
    }

    async renderPage() {
        if (! this.document) {
            return;
        }

        if (this.renderTask) {
            this.pendingRender = true;
            return;
        }

        const page = await this.document.getPage(this.pageNumber);
        const scaledViewport = page.getViewport({ scale: this.scale });
        const pixelRatio = window.devicePixelRatio || 1;

        this.canvas.width = Math.floor(scaledViewport.width * pixelRatio);
        this.canvas.height = Math.floor(scaledViewport.height * pixelRatio);
        this.canvas.style.width = `${Math.floor(scaledViewport.width)}px`;
        this.canvas.style.height = `${Math.floor(scaledViewport.height)}px`;

        this.context.setTransform(pixelRatio, 0, 0, pixelRatio, 0, 0);

        this.renderTask = page.render({
            canvasContext: this.context,
            viewport: scaledViewport,
        });

        await this.renderTask.promise;
        this.renderTask = null;
        this.updateControls();

        if (this.pendingRender) {
            this.pendingRender = false;
            await this.renderPage();
        }
    }

    goToPage(pageNumber) {
        this.pageNumber = clamp(pageNumber, 1, this.pageCount);
        this.renderPage();
    }

    setScale(scale) {
        this.scale = clamp(scale, 0.65, 2.25);
        this.renderPage();
    }

    setStatus(message) {
        if (this.status) {
            this.status.textContent = message;
        }
    }

    updateControls() {
        this.currentPageOutput.textContent = String(this.pageNumber);
        this.previousButton.disabled = this.pageNumber <= 1;
        this.nextButton.disabled = this.pageNumber >= this.pageCount;
    }
}

const initializeModulePdfViewers = async () => {
    const viewers = document.querySelectorAll('[data-module-pdf-viewer]:not([data-pdf-ready])');

    if (! viewers.length) {
        return;
    }

    const [pdfjsLib, { default: pdfWorkerUrl }] = await Promise.all([
        import('pdfjs-dist'),
        import('pdfjs-dist/build/pdf.worker.mjs?url'),
    ]);

    pdfjsLib.GlobalWorkerOptions.workerSrc = pdfWorkerUrl;

    document.querySelectorAll('[data-module-pdf-viewer]:not([data-pdf-ready])').forEach((element) => {
        element.dataset.pdfReady = 'true';
        new ModulePdfViewer(element, pdfjsLib);
    });
};

document.addEventListener('DOMContentLoaded', initializeModulePdfViewers);
document.addEventListener('livewire:navigated', initializeModulePdfViewers);

const setupNavigationProgress = () => {
    const bar = document.querySelector('[data-nav-progress]');

    if (! bar) {
        return;
    }

    let timer = null;
    let skeletonTimer = null;
    let progress = 10;
    let navigationStartedAt = null;
    let slowTransitionCount = 0;
    let cachedTransitionCount = 0;

    const setProgress = (value) => {
        progress = clamp(value, 0, 100);
        bar.style.transform = `scaleX(${progress / 100})`;
    };

    const toggleSkeleton = (active) => {
        const content = document.querySelector('[data-nav-content]');
        const skeleton = document.querySelector('[data-nav-skeleton]');

        if (! content || ! skeleton) {
            return;
        }

        content.setAttribute('aria-busy', active ? 'true' : 'false');
        content.dataset.loading = active ? 'true' : 'false';
    };

    const start = (event) => {
        clearInterval(timer);
        clearTimeout(skeletonTimer);

        const isReducedMotion = window.AlumcoAccessibility?.isReducedMotion();
        navigationStartedAt = performance.now();
        const isCachedNavigation = Boolean(event?.detail?.cached);

        if (isCachedNavigation) {
            cachedTransitionCount += 1;
        }

        bar.dataset.active = 'true';
        
        if (isReducedMotion) {
            setProgress(100);
            return;
        }

        setProgress(12);

        skeletonTimer = setTimeout(() => {
            toggleSkeleton(true);
            slowTransitionCount += 1;
        }, isCachedNavigation ? 130 : 70);

        timer = setInterval(() => {
            if (progress < 85) {
                setProgress(progress + (progress < 40 ? 12 : 6));
            }
        }, 120);
    };

    const finish = () => {
        clearInterval(timer);
        clearTimeout(skeletonTimer);
        toggleSkeleton(false);
        setProgress(100);

        const elapsed = navigationStartedAt ? Math.round(performance.now() - navigationStartedAt) : null;
        if (elapsed !== null) {
            console.debug('[nav-perf]', {
                elapsedMs: elapsed,
                slowTransitions: slowTransitionCount,
                cachedTransitions: cachedTransitionCount,
            });
        }

        setTimeout(() => {
            bar.dataset.active = 'false';
            setProgress(0);
        }, 180);
    };

    document.addEventListener('livewire:navigate', start);
    document.addEventListener('livewire:navigated', finish);
};

document.addEventListener('DOMContentLoaded', setupNavigationProgress);

```

### resources/js/bootstrap.js
```js
import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

```
