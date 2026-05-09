# Auditoría 03 - Servidor

### [SRV-001] Variables de entorno actuales están en modo local con debug activo
- **Archivo:** `.env` - líneas 1-3, 12-15
- **Severidad:** ALTO
- **Evidencia:** `cat .env 2>/dev/null | grep -v "PASSWORD\|SECRET\|KEY\|TOKEN"`
```text
APP_NAME=Alumco
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost
LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug
```
- **Problema técnico:** El entorno revisado no está preparado para producción: debug activo, entorno local, URL localhost y logging en nivel debug.
- **Impacto:** Si esta configuración llega a producción, errores de Laravel pueden exponer stack traces y detalles internos; además URLs absolutas y correos podrían generarse con dominio incorrecto.
- **Solución:**
```php
// .env producción
APP_ENV=production
APP_DEBUG=false
APP_URL=https://lms.alumco.cl
LOG_LEVEL=warning
```

### [SRV-002] `php artisan` no funciona en el host por versión PHP incompatible
- **Archivo:** `composer.json` - línea 11
- **Severidad:** MEDIO
- **Evidencia:** `php artisan route:list 2>&1 | head -80 || true`
```text
Composer detected issues in your platform:
Your Composer dependencies require a PHP version ">= 8.4.0". You are running 8.1.2-1ubuntu2.23.
```
`cat composer.json`
```text
"php": "^8.3",
"laravel/framework": "^13.0",
```
Laravel Boost reporta PHP runtime del proyecto:
```text
php_version: 8.4, laravel_version: 13.7.0
```
- **Problema técnico:** El CLI del host es PHP 8.1.2, pero las dependencias instaladas exigen PHP >= 8.4. Los comandos directos `php artisan` fallan antes de arrancar Laravel.
- **Impacto:** Operaciones de despliegue, cache, migraciones y diagnóstico fallarán si se ejecutan fuera de Sail/contenedor. También impide auditorías o cron jobs locales basados en `php artisan`.
- **Solución:**
```php
// Ejecutar comandos del proyecto dentro del runtime correcto:
// ./vendor/bin/sail artisan route:list
// ./vendor/bin/sail artisan config:cache
```

### [SRV-003] Sesiones persistidas sin cifrado explícito
- **Archivo:** `.env` - línea 22
- **Severidad:** BAJO
- **Evidencia:** `cat .env 2>/dev/null | grep -v "PASSWORD\|SECRET\|KEY\|TOKEN"`
```text
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
```
`php artisan config:show session 2>/dev/null || cat config/session.php`
```text
'encrypt' => env('SESSION_ENCRYPT', false),
'serialization' => 'json',
```
- **Problema técnico:** El contenido de sesión se guarda en base de datos sin cifrado de aplicación. Laravel serializa en JSON, lo que reduce riesgo de gadget chains, pero no protege datos de sesión ante lectura de la tabla.
- **Impacto:** Un acceso de solo lectura a la base de datos puede exponer datos de sesión almacenados.
- **Solución:**
```php
// .env
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
```

### [SRV-004] Comandos Composer definidos fuera de Sail
- **Archivo:** `composer.json` - líneas 35-40
- **Severidad:** BAJO
- **Evidencia:** `cat composer.json | grep '"scripts"' -A 20`
```text
"dev": [
    "Composer\\Config::disableProcessTimeout",
    "npx concurrently ... \"php artisan serve\" \"php artisan queue:listen --tries=1 --timeout=0\" \"php artisan pail --timeout=0\" \"npm run dev\" ..."
],
"test": [
    "@php artisan config:clear --ansi",
    "@php artisan test"
]
```
- **Problema técnico:** Los scripts usan `php artisan`, `npm` y `npx` directamente. En este proyecto el runtime operativo está en Sail; fuera del contenedor ya se observó que PHP del host no es compatible.
- **Impacto:** Nuevos desarrolladores pueden ejecutar scripts que fallen o usen versiones distintas a las del contenedor.
- **Solución:**
```php
// Documentar y usar wrappers Sail para desarrollo:
// ./vendor/bin/sail npm run dev
// ./vendor/bin/sail artisan test
```

### [SRV-005] Throttling limitado a login, soporte público y verificación de certificados
- **Archivo:** `routes/web.php` - líneas 27, 34, 36
- **Severidad:** BAJO
- **Evidencia:** `grep -rn "throttle:" routes/ --include="*.php"`
```text
routes/web.php:27:Route::middleware('throttle:30,1')->group(function () {
routes/web.php:34:    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
routes/web.php:36:        ->middleware('throttle:6,1')
```
- **Problema técnico:** Las rutas autenticadas de acciones mutantes no tienen throttling específico. Auth reduce exposición externa, pero Livewire/POST internos costosos pueden recibir ráfagas desde usuarios válidos.
- **Impacto:** Posible degradación por abuso autenticado en acciones como reportes, tickets o calendario.
- **Solución:**
```php
Route::middleware(['auth', 'throttle:120,1'])->group(function () {
    // rutas POST/PUT/DELETE de alta frecuencia
});
```
