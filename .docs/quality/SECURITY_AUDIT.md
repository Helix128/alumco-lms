# Auditoría de Seguridad

## Alcance
Auditoría repository-wide del proyecto Laravel Sail, con revisión de dependencias, rutas, controladores, modelos, middleware, configuración, migraciones, vistas Blade, Livewire y tests. Se ejecutó `./vendor/bin/sail composer audit`.

## Resumen de Riesgo
El proyecto tiene controles básicos correctos: autenticación, CSRF por grupo `web`, rate limiting en login y verificación pública, sanitización de HTML en creación/edición de módulos y headers `nosniff` para archivos servidos inline. Los riesgos principales son dependencia vulnerable, elevación de roles por email en login, autorización dispersa sin Policies y algunos métodos Livewire que mutan recursos por id sin verificar pertenencia de forma robusta.

## Dependencias con Vulnerabilidades Conocidas

| Package | Versión | Severidad | CVE/Advisory | Evidencia | Acción |
|---------|---------|-----------|--------------|-----------|--------|
| `phpoffice/phpspreadsheet` | `1.30.0` | High | CVE-2026-40902 / GHSA-7c6m-4442-2x6m | `composer.lock` 3650-3651; `composer audit` | Actualizar con dependencias |
| `phpoffice/phpspreadsheet` | `1.30.0` | High | CVE-2026-40863 / GHSA-84wq-86v6-x5j6 | `composer audit` | Actualizar |
| `phpoffice/phpspreadsheet` | `1.30.0` | High | CVE-2026-34084 / GHSA-q4q6-r8wh-5cgh | `composer audit` | Actualizar urgentemente |
| `phpoffice/phpspreadsheet` | `1.30.0` | Medium | CVE-2026-40296 / GHSA-hrmw-qprp-wgmc | `composer audit` | Actualizar |
| `phpoffice/phpspreadsheet` | `1.30.0` | Medium | CVE-2026-35453 / GHSA-6wpp-88cp-7q68 | `composer audit` | Actualizar |

`phpoffice/phpspreadsheet` llega por `maatwebsite/excel` (`composer.json` línea 18). Aunque el proyecto revisado exporta Excel y no importa archivos de usuario, la dependencia queda en producción y debe corregirse.

## OWASP Top 10 Evaluado para Laravel

| OWASP | Estado | Evidencia |
|-------|--------|-----------|
| A01 Broken Access Control | 🟡 Parcial | Middleware y checks manuales existen; no hay `app/Policies`; `EditarEvaluacion` muta por ids. |
| A02 Cryptographic Failures | 🟡 Parcial | `SESSION_SECURE_COOKIE` no está declarado en `.env.example`; passwords usan cast `hashed`. |
| A03 Injection | 🟢 Controlado | No se encontró raw SQL con input de usuario. `selectRaw()` es estático. |
| A04 Insecure Design | 🟡 Parcial | Autorización dispersa y roles corregidos en login. |
| A05 Security Misconfiguration | 🟡 Parcial | No hay headers de seguridad globales; Livewire upload config queda implícita. |
| A06 Vulnerable Components | 🔴 Vulnerable | `phpoffice/phpspreadsheet 1.30.0` con advisories. |
| A07 Identification/Auth Failures | 🟡 Parcial | Login rate limited; password reset usa broker; login asigna roles por email. |
| A08 Software/Data Integrity Failures | 🟡 Parcial | Sin CI/CD; migraciones de datos no reversibles. |
| A09 Logging/Monitoring Failures | 🔵 No evaluado en profundidad | Config logging estándar; no hay auditoría de acciones admin. |
| A10 SSRF | 🟡 Dependencia | Advisory de PhpSpreadsheet incluye SSRF/RCE si se procesan filenames controlados por usuario. |

## Vulnerabilidades Encontradas

### SEC-001 — Dependencia vulnerable `phpoffice/phpspreadsheet`
- **CVSS estimado:** 8.1 High
- **Archivo:** `composer.lock` líneas 3650-3651
- **Problema:** versión `1.30.0` afectada por 5 advisories.
- **Fix:**
```bash
./vendor/bin/sail composer update phpoffice/phpspreadsheet maatwebsite/excel --with-all-dependencies
./vendor/bin/sail composer audit
```

### SEC-002 — Elevación de roles por email durante login
- **CVSS estimado:** 7.4 High
- **Archivo:** `app/Http/Controllers/AuthController.php` líneas 38-43
- **Problema:** asigna roles privilegiados si el email coincide.
- **Fix específico:**
```php
// Eliminar este bloque del login.
// Ejecutar reparación por comando protegido:
// ./vendor/bin/sail artisan alumco:repair-system-roles --no-interaction
```

### SEC-003 — Autorización de recursos sin Policies
- **CVSS estimado:** 6.5 Medium
- **Archivos:** `app/Http/Controllers/Capacitador/*.php`, `app/Livewire/Admin/UserManagement.php`, ausencia de `app/Policies`
- **Problema:** reglas duplicadas manualmente. En controladores nuevos es fácil olvidar un check.
- **Fix específico:**
```php
Gate::authorize('update', $curso);
Gate::authorize('download', $certificado);
```

### SEC-004 — Mutaciones Livewire por id sin verificar pertenencia
- **CVSS estimado:** 6.8 Medium
- **Archivo:** `app/Livewire/Capacitador/EditarEvaluacion.php` líneas 98-100, 143-145, 154-162
- **Problema:** `Pregunta::destroy($preguntaId)` y `Opcion::destroy($opcionId)` no verifican que el registro pertenezca a `$this->evaluacion`.
- **Código problemático real:**
```php
public function eliminarPregunta(int $preguntaId): void
{
    Pregunta::destroy($preguntaId);
}
```
- **Fix específico:**
```php
Pregunta::query()
    ->whereKey($preguntaId)
    ->where('evaluacion_id', $this->evaluacion->id)
    ->delete();
```

### SEC-005 — Sesión segura no explícita para producción
- **CVSS estimado:** 4.8 Medium
- **Archivo:** `config/session.php` línea 172, `.env.example` líneas 30-34
- **Problema:** `secure` depende de `SESSION_SECURE_COOKIE`, no documentado en `.env.example`.
- **Fix específico:**
```env
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

### SEC-006 — Contenido HTML renderizado sin escape depende de sanitización previa
- **CVSS estimado:** 5.4 Medium
- **Archivo:** `resources/views/modulos/capsula.blade.php` línea 114
- **Problema:** `{!! $modulo->contenido !!}` es correcto solo si todo contenido, incluido legacy/seeds/imports, pasó por Purifier.
- **Control existente:** `clean()` en `Capacitador/ModuloController.php` líneas 66 y 119.
- **Fix específico:** agregar test XSS y sanitizar al mostrar si hay datos legacy.
```php
{!! clean($modulo->contenido) !!}
```

## Configuraciones Recomendadas

### Middleware/Headers
Agregar middleware global de headers si no se termina en proxy/CDN:
```php
$response->headers->set('X-Frame-Options', 'SAMEORIGIN');
$response->headers->set('X-Content-Type-Options', 'nosniff');
$response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
$response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
```

### Rate Limiting
- Mantener `throttle:login` en `routes/web.php` línea 29.
- Agregar throttle explícito a password reset:
```php
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])
    ->middleware('throttle:5,1')
    ->name('password.email');
```

### Livewire Uploads
```php
'temporary_file_upload' => [
    'rules' => ['required', 'file', 'max:12288'],
    'middleware' => 'throttle:60,1',
]
```

### CI Security Gate
```yaml
- run: composer audit
- run: vendor/bin/pint --test
- run: php artisan test --compact
```

## No Hallazgos Confirmados
- No se encontró uso de `env()` fuera de `config/` salvo seeders de testing (`database/seeders/Testing/*`).
- No se encontraron secretos reales hardcodeados en `.env.example`; hay placeholders vacíos.
- No se encontró `whereRaw()` con input de usuario.
- CSRF está cubierto por rutas `web` y formularios revisados usan `@csrf`.
