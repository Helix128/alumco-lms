# Reporte de Calidad — Alumco LMS
**Fecha:** 2026-05-04  **Laravel:** 13.1.1  **PHP:** 8.4

## Resumen Ejecutivo
Alumco LMS tiene una base funcional razonable, con rutas protegidas, uso consistente de Eloquent, pruebas Feature relevantes y varias decisiones correctas de rendimiento como eager loading, paginación y caché flexible. El proyecto, sin embargo, acumula lógica de negocio en controladores, modelos y componentes Livewire grandes, con baja separación entre autorización, validación, coordinación HTTP y reglas de dominio. La seguridad general está mejor que un prototipo, pero hay un hallazgo inmediato: `composer.lock` instala `phpoffice/phpspreadsheet` vulnerable por medio de `maatwebsite/excel`. También hay controles de autorización hechos a mano y dispersos, sin Policies/Gates, lo que eleva el riesgo de regresiones. El rendimiento tiene buenos esfuerzos puntuales, pero existen N+1 reales en participantes y operaciones pesadas dentro del ciclo de render de Livewire. Veredicto: **Necesita refactoring menor con correcciones críticas inmediatas**.

## Scorecard General
| Dimensión | Puntuación | Estado |
|-----------|-----------:|--------|
| Arquitectura | 5/10 | 🟡 Mejorable |
| SOLID | 5/10 | 🟡 Mejorable |
| Clean Code | 6/10 | 🟡 Mejorable |
| Base de datos | 7/10 | 🟢 Bueno |
| Seguridad | 6/10 | 🟡 Mejorable |
| Rendimiento | 6/10 | 🟡 Mejorable |
| Testing | 7/10 | 🟢 Bueno |
| Convenciones Laravel | 6/10 | 🟡 Mejorable |
| **TOTAL PONDERADO** | **6.0/10** | 🟡 Mejorable |

## Hallazgos Críticos (prioridad inmediata)

### 1. Dependencia vulnerable en exportación Excel
- **Archivo:** `composer.lock` (líneas 3650-3651), `composer.json` (línea 18)
- **Problema:** `maatwebsite/excel` arrastra `phpoffice/phpspreadsheet` `1.30.0`. `./vendor/bin/sail composer audit` reportó 5 advisories: CVE-2026-40902, CVE-2026-40863, CVE-2026-34084, CVE-2026-40296 y CVE-2026-35453.
- **Riesgo:** DoS de CPU y, si en el futuro se procesan archivos controlados por usuarios con IOFactory, SSRF/RCE. Hoy el proyecto exporta reportes, pero mantener el paquete vulnerable deja una superficie peligrosa.
- **Código problemático real:**
```json
"maatwebsite/excel": "^3.1"
```
```json
"name": "phpoffice/phpspreadsheet",
"version": "1.30.0"
```
- **Fix rápido:**
```bash
./vendor/bin/sail composer update phpoffice/phpspreadsheet maatwebsite/excel --with-all-dependencies
./vendor/bin/sail composer audit
./vendor/bin/sail artisan test --compact tests/Feature/Admin/ReportsTest.php
```

### 2. Failsafe de roles en login modifica permisos durante autenticación
- **Archivo:** `app/Http/Controllers/AuthController.php` (líneas 38-43)
- **Problema:** el login asigna roles por email hardcodeado (`dev@alumco.cl`, `admin@alumco.cl`). La autenticación no debería modificar autorización ni depender de correcciones de datos.
- **Riesgo:** si una cuenta con ese correo se crea/restaura sin rol, obtiene privilegios automáticamente al iniciar sesión. También oculta errores de migración/seeders.
- **Código problemático real:**
```php
if ($user->email === 'dev@alumco.cl' && ! $user->hasRole('Desarrollador')) {
    $user->assignRole('Desarrollador');
} elseif ($user->email === 'admin@alumco.cl' && ! $user->hasRole('Administrador')) {
    $user->assignRole('Administrador');
}
```
- **Fix rápido:**
```php
// AuthController@login: eliminar asignación de roles.
// Crear comando idempotente o seeder ejecutado en despliegue controlado.
```

### 3. N+1 en listado y exportación de participantes
- **Archivo:** `app/Http/Controllers/Capacitador/ParticipanteController.php` (líneas 41-51 y 82-92)
- **Problema:** por cada usuario se ejecuta un `count()` de progreso y un `first()` de certificado.
- **Riesgo:** con muchos participantes, la página y la exportación hacen 2N queries adicionales.
- **Código problemático real:**
```php
$usuarios = $usuarios->map(function (User $user) use ($curso, $moduloIds) {
    $completados = ProgresoModulo::where('user_id', $user->id)
        ->whereIn('modulo_id', $moduloIds)
        ->where('completado', true)
        ->count();

    $user->certificado = Certificado::where('user_id', $user->id)
        ->where('curso_id', $curso->id)
        ->first();
});
```
- **Fix rápido:**
```php
$progresos = ProgresoModulo::query()
    ->whereIn('user_id', $usuarios->pluck('id'))
    ->whereIn('modulo_id', $moduloIds)
    ->where('completado', true)
    ->selectRaw('user_id, count(*) as completados')
    ->groupBy('user_id')
    ->pluck('completados', 'user_id');

$certificados = Certificado::query()
    ->whereIn('user_id', $usuarios->pluck('id'))
    ->where('curso_id', $curso->id)
    ->get()
    ->keyBy('user_id');
```

## Hallazgos por Dimensión

### Arquitectura y Estructura

#### 🟠 Alto — Componente Livewire tipo God Class
- **Archivo:** `app/Livewire/Capacitador/CalendarioCapacitaciones.php` (1-1596)
- **Descripción:** el componente concentra estado de UI, autorización, CRUD, copia anual, cacheo, generación de calendarios, detección de conflictos y render. Tiene 1.596 líneas y múltiples métodos largos.
- **Código problemático real:**
```php
public function copiarAnio(string $modo = 'auto'): void
{
    abort_unless(Auth::user()->hasAdminAccess(), 403);
    // valida, consulta, borra, copia, invalida caché y refresca UI
    DB::transaction(function () use (...) { ... });
}
```
- **Código mejorado propuesto:**
```php
public function copiarAnio(string $modo = 'auto', CopyYearPlanningAction $action): void
{
    abort_unless(Auth::user()->hasAdminAccess(), 403);

    $action->execute($this->anioOrigen, $this->anioDestino, $modo);

    $this->invalidateCalendarCaches();
    $this->cerrarModalCopiarAnio();
    $this->anioActual = $this->anioDestino;
    $this->cargarDatos();
}
```

#### 🟠 Alto — Modelo `Curso` mezcla persistencia con procesamiento de imagen
- **Archivo:** `app/Models/Curso.php` (líneas 22-186)
- **Descripción:** el modelo ejecuta lectura de storage, `getimagesize()`, GD, bucketización HSL y reglas estéticas. Es lógica de infraestructura/presentación dentro del modelo.
- **Código problemático real:**
```php
protected static function booted()
{
    static::saving(function (Curso $curso) {
        if ($curso->isDirty('imagen_portada') && empty($curso->color_promedio)) {
            $curso->color_promedio = $curso->extraerColorPromedio();
        }
    });
}
```
- **Código mejorado propuesto:**
```php
// En el controller/action, después de almacenar la portada:
$data['color_promedio'] = $request->boolean('auto_color')
    ? $averageColorExtractor->fromPublicPath($data['imagen_portada'])
    : $data['color_promedio'];
```

#### 🟡 Medio — Reglas de autorización duplicadas
- **Archivos:** `app/Http/Controllers/Capacitador/CursoController.php` (16-22), `ModuloController.php` (18-24), `ParticipanteController.php` (19-25), `SeccionCursoController.php` (13-19)
- **Descripción:** se repite `authorizeCurso()` en varios controladores, en lugar de usar `CursoPolicy`.
- **Código problemático real:**
```php
if (auth()->user()->hasAdminAccess()) {
    return;
}
abort_unless($curso->capacitador_id === auth()->id(), 403);
```
- **Código mejorado propuesto:**
```php
public function update(User $user, Curso $curso): bool
{
    return $user->hasAdminAccess() || $curso->capacitador_id === $user->id;
}
```

### SOLID

#### 🟠 Alto — SRP e inversión de dependencias débiles en `CertificadoService`
- **Archivo:** `app/Services/CertificadoService.php` (líneas 24-179)
- **Descripción:** valida aprobación, crea certificado, borra PDF, genera PDF, genera QR y notifica. Además instancia `Builder`, `PngWriter` y usa facades directamente.
- **Impacto:** difícil test unitario y cambios de PDF/QR/notificación obligan a tocar la misma clase.
- **Código problemático real:**
```php
return PdfFacade::loadView('capacitador.certificados.plantilla', compact(...))
    ->setOptions([...], true)
    ->setPaper('letter', 'portrait');
```
- **Código mejorado propuesto:**
```php
public function __construct(
    private CertificateEligibility $eligibility,
    private CertificatePdfRenderer $renderer,
    private CertificateNotifier $notifier,
) {}
```

#### 🟡 Medio — `DuplicateCourseAction` depende de relaciones cargadas implícitamente
- **Archivo:** `app/Actions/Cursos/DuplicateCourseAction.php` (líneas 16-50)
- **Descripción:** usa `$cursoOriginal->modulos`, `$moduloOriginal->evaluacion`, `$evaluacionOriginal->preguntas` y `$preguntaOriginal->opciones` sin asegurar eager loading.
- **Impacto:** N+1 silencioso si se duplica un curso grande.
- **Código mejorado propuesto:**
```php
$cursoOriginal->loadMissing('modulos.evaluacion.preguntas.opciones');
```

### Clean Code

#### 🟠 Alto — Métodos largos en controladores
- **Archivos:** `app/Http/Controllers/CursoController.php` `index()` (11-73), `show()` (76-142); `ReporteController.php` `index()` (52-142)
- **Descripción:** coordinan consulta, reglas de negocio, filtros, categorización y preparación de vista.
- **Código problemático real:**
```php
foreach ($cursos as $curso) {
    $progreso = $curso->progresoParaUsuario($user);
    $curso->progreso_calculado = $progreso;
    // clasifica vigentes, completados y anteriores
}
```
- **Código mejorado propuesto:**
```php
[$vigentes, $completados, $anteriores] =
    $courseCatalog->forUser($user, preview: session('preview_mode', false));
```

#### 🟡 Medio — Números mágicos y literals repetidos
- **Archivos:** `app/Models/Curso.php` (62-64, 80-81, 95, 111-115), `app/Livewire/Capacitador/CalendarioCapacitaciones.php` (1417-1418), `app/Services/CertificadoService.php` (122-123)
- **Descripción:** valores como `20`, `36`, `0.15`, `0.85`, `2020`, `2099`, `240`, `12` viven inline.
- **Código mejorado propuesto:**
```php
private const CALENDAR_MIN_YEAR = 2020;
private const CALENDAR_MAX_YEAR = 2099;
private const QR_SIZE = 240;
```

#### 🔵 Bajo — Comentarios que repiten pasos
- **Archivos:** `app/Actions/Cursos/DuplicateCourseAction.php` (17, 23, 29, 37, 43), `app/Http/Controllers/AuthController.php` (24, 32, 48)
- **Descripción:** varios comentarios explican el “qué” inmediato, no el “por qué”.

### Base de Datos y Eloquent

#### 🟢 Bueno — Índices y constraints relevantes existen
- **Archivos:** `database/migrations/0001_01_01_000003_create_users_table.php` (63, 69-70, 86-91, 97-99), `2026_04_13_230001_create_planificaciones_cursos_table.php` (13, 19), `2026_05_03_004448_create_notification_deliveries_table.php` (21, 25-26)
- **Descripción:** hay FKs, índices compuestos y unique para deduplicación.

#### 🟡 Medio — Migraciones con cambios de datos no reversibles
- **Archivos:** `database/migrations/2026_04_26_080000_fix_missing_spatie_roles_for_admins_and_devs.php` (145-148), `2026_04_28_110000_assign_trabajador_role_to_orphans.php` (171-174)
- **Descripción:** los `down()` no revierten cambios, documentado en comentarios. Aceptable si es intencional, pero debe estar en plan de despliegue.

#### 🟡 Medio — Raw SQL en migración
- **Archivo:** `database/migrations/2026_04_07_100000_alter_modulos_for_all_content_types.php` (13-15, 28-29)
- **Descripción:** `DB::statement()` modifica enum. No hay entrada de usuario, así que no es SQL injection, pero es acoplamiento fuerte a MySQL.

### Seguridad

#### 🟠 Alto — Sin Policies/Gates para recursos principales
- **Archivos:** `app/Http/Middleware/*.php`, `app/Http/Controllers/Capacitador/*.php`, ausencia de `app/Policies`
- **Descripción:** autorización dispersa en middleware/controladores/modelos. Laravel recomienda centralizar reglas en Policies cuando hay recursos como `Curso`, `Modulo`, `Certificado`.
- **Riesgo:** nuevas rutas pueden omitir un `abort_unless()` o copiar una regla incompleta.

#### 🟡 Medio — Sesiones sin cookie segura por defecto
- **Archivo:** `config/session.php` (línea 172), `.env.example` (no define `SESSION_SECURE_COOKIE=true`)
- **Descripción:** `secure` depende de env y `.env.example` no guía producción.
- **Código mejorado propuesto:**
```env
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
```

#### 🟡 Medio — Livewire uploads sin throttle/config explícita
- **Archivo:** `config/livewire.php` (líneas 131-135)
- **Descripción:** reglas y middleware están en `null`. Laravel/Livewire tienen defaults, pero para producción conviene definirlos explícitamente.
- **Código mejorado propuesto:**
```php
'rules' => ['required', 'file', 'max:12288'],
'middleware' => 'throttle:60,1',
```

#### 🔵 Bajo — `{!! !!}` existe, pero con sanitización parcial
- **Archivo:** `resources/views/modulos/capsula.blade.php` (línea 114), `app/Http/Controllers/Capacitador/ModuloController.php` (66, 119)
- **Descripción:** el contenido se limpia con `clean()` antes de guardar. Es positivo, pero debe cubrir datos legacy y tener test XSS.

### Rendimiento

#### 🟠 Alto — Trabajo de datos dentro de `render()`
- **Archivo:** `app/Livewire/Capacitador/CalendarioCapacitaciones.php` (líneas 1525-1528)
- **Descripción:** `render()` llama `cargarCursosDisponibles()`, que usa caché pero puede recalcular y filtrar en cada render.
- **Código problemático real:**
```php
public function render()
{
    $this->cargarCursosDisponibles();
    ...
}
```
- **Código mejorado propuesto:**
```php
public function updatedBusquedaSidebar(): void
{
    $this->refreshFilteredCourseLists();
}
```

#### 🟡 Medio — Duplicación de consultas de participantes
- **Archivo:** `app/Http/Controllers/Capacitador/ParticipanteController.php` (31-56 y 77-103)
- **Descripción:** `index()` y `exportar()` repiten cálculo de participantes/progreso/certificado. Extraer Query Object reduce errores y mejora batching.

### Testing

#### 🟢 Bueno — Suite Feature amplia
- **Archivos:** `tests/Feature/*.php`
- **Descripción:** hay pruebas de notificaciones, calendario, certificados, reportes, jerarquía de usuarios, preview, errores y accesibilidad. Uso frecuente de `RefreshDatabase`, factories, `Notification::fake()` y `Storage::fake()`.

#### 🟡 Medio — Falta test directo para XSS en contenido HTML
- **Archivos:** `resources/views/modulos/capsula.blade.php` (114), `app/Http/Controllers/Capacitador/ModuloController.php` (66, 119)
- **Descripción:** existe sanitización, pero no se encontró prueba específica que demuestre que `script`, `onerror` o atributos peligrosos se eliminan.

#### 🟡 Medio — No hay pipeline CI/CD
- **Archivo:** `.github/workflows/` ausente
- **Descripción:** no se encontró workflow para ejecutar `composer audit`, Pint y tests automáticamente.

### Convenciones Laravel

#### 🟡 Medio — Validación inline donde convendrían Form Requests
- **Archivos:** `app/Http/Controllers/Capacitador/CursoController.php` (46-52, 106-112), `ModuloController.php` (54-60, 111-116), `ReporteController.php` (16-50 sanitización manual)
- **Descripción:** funciona, pero dificulta reuso, autorización por request y test unitario de reglas.

#### 🟡 Medio — No se usa API Resources porque no hay API pública
- **Archivo:** `routes/api.php` ausente
- **Descripción:** no es problema actual. Si se agrega API, usar Resources y versionado.

## Puntos Positivos
1. **Rate limiting en login y verificación pública:** `routes/web.php` (22-29) y `AppServiceProvider.php` (37-39).
2. **Uso de middleware persistente Livewire:** `AppServiceProvider.php` (30-35), reduce bypass por requests Livewire.
3. **Eager loading consciente en vistas críticas:** `CursoController.php` (122-138), `VerificarCertificadoController.php` (242-245).
4. **Deduplicación de notificaciones:** `NotificationDelivery::recordOnce()` en `app/Models/NotificationDelivery.php` (86-102) y unique en migración.
5. **Sanitización de HTML de módulos:** `Capacitador/ModuloController.php` (66, 119) antes de renderizar con `{!! !!}`.
6. **Tests Feature con factories/fakes:** `tests/Feature/CourseNotificationTest.php`, `CursoPreviewTest.php`, `Admin/UserManagementTest.php`.
7. **Índices compuestos en planificación:** `database/migrations/2026_04_13_230001_create_planificaciones_cursos_table.php` (19).
8. **Caché con invalidación versionada en calendario:** `CalendarioCapacitaciones.php` (945-960).
