# Auditoría 01 - Seguridad

### [SEG-001] Adjuntos internos de soporte accesibles por solicitantes
- **Archivo:** `app/Http/Controllers/SupportTicketAttachmentController.php` - línea 15
- **Severidad:** ALTO
- **Evidencia:** `cat app/Http/Controllers/SupportTicketAttachmentController.php`
```text
$attachment->loadMissing('ticket');
$this->authorize('view', $attachment->ticket);
return Storage::disk('local')->download($attachment->path, $attachment->original_name);
```
`cat app/Policies/SupportTicketPolicy.php`
```text
return $user->isDesarrollador() || $supportTicket->requester_user_id === $user->id;
```
`cat app/Livewire/Support/ManageTickets.php`
```text
'is_internal' => $this->isInternalReply,
$attachmentsAction->storeAttachments($ticket, $message, $data['replyAttachments'] ?? []);
```
- **Problema técnico:** La descarga autoriza solo por ticket, no por el mensaje ni por `is_internal`. Si un desarrollador adjunta archivo a una respuesta interna, el solicitante autenticado del ticket cumple la policy `view` y puede descargar ese adjunto si obtiene la URL/id.
- **Impacto:** Exposición de capturas, notas o evidencias internas del equipo técnico al usuario solicitante.
- **Solución:**
```php
public function __invoke(SupportTicketAttachment $attachment): StreamedResponse|Response
{
    $attachment->loadMissing(['ticket', 'message']);
    $this->authorize('view', $attachment->ticket);

    abort_if(
        $attachment->message?->is_internal && ! auth()->user()?->isDesarrollador(),
        403
    );

    abort_unless(Storage::disk('local')->exists($attachment->path), 404);

    return Storage::disk('local')->download($attachment->path, $attachment->original_name);
}
```

### [SEG-002] Descarga directa de archivos de módulos omite disponibilidad y secuencia
- **Archivo:** `app/Http/Controllers/ModuloController.php` - línea 66
- **Severidad:** MEDIO
- **Evidencia:** `cat app/Http/Controllers/ModuloController.php`
```text
if ($this->belongsToUserEstamento($curso, $user)) {
    // Nota: No validamos estaAccesiblePara() o estaDisponiblePara() aquí
    // para permitir descargas si el usuario ya llegó a la vista del módulo.
    return;
}
```
`cat routes/web.php routes/api.php`
```text
Route::get('/cursos/{curso}/modulos/{modulo}/archivo', [ModuloController::class, 'verArchivo'])->name('modulos.archivo');
Route::get('/cursos/{curso}/modulos/{modulo}/descargar', [ModuloController::class, 'descargarArchivo'])->name('modulos.descargar');
```
- **Problema técnico:** Las rutas de archivo permiten a cualquier trabajador asociado al estamento del curso descargar o ver el archivo sin validar `estaAccesiblePara()` ni `estaDisponiblePara()`. La vista del módulo sí valida esas reglas, pero el endpoint de archivo queda como bypass directo.
- **Impacto:** Usuarios pueden acceder a contenidos bloqueados por secuencia o fuera de periodo si conocen o construyen la URL.
- **Solución:**
```php
private function authorizeFileAccess(Curso $curso, Modulo $modulo): void
{
    abort_if($modulo->curso_id !== $curso->id, 404);

    $user = auth()->user();
    abort_unless($user instanceof User, 403);

    if ($user->hasAdminAccess() || $curso->capacitador_id === $user->id) {
        return;
    }

    $this->authorizeCourseAccess($curso, $user);
    $this->loadCourseModulesFor($curso, $user);

    $moduloCargado = $curso->modulos->find($modulo->id);
    abort_unless($moduloCargado?->estaAccesiblePara($user, $curso), 403);
}
```

### [SEG-003] Entorno local con debug activo y correo real en `.env`
- **Archivo:** `.env` - líneas 1-3, 31-34
- **Severidad:** ALTO
- **Evidencia:** `cat .env 2>/dev/null | grep -v "PASSWORD\|SECRET\|KEY\|TOKEN"`
```text
APP_NAME=Alumco
APP_ENV=local
APP_DEBUG=true
MAIL_MAILER=smtp
MAIL_SCHEME=smtps
MAIL_HOST=smtp.zoho.com
MAIL_PORT=465
MAIL_USERNAME=diego@noseprogramar.cl
```
- **Problema técnico:** La configuración local tiene `APP_DEBUG=true`, `APP_ENV=local`, `LOG_LEVEL=debug` y datos operativos de SMTP. Si este archivo se despliega o queda versionado, Laravel puede exponer trazas, rutas, variables y detalles internos ante errores.
- **Impacto:** Filtración de información sensible y aumento fuerte de capacidad de explotación en producción.
- **Solución:**
```php
// .env de producción
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=warning
SESSION_SECURE_COOKIE=true
```

### [SEG-004] No se encontraron excepciones CSRF explícitas
- **Archivo:** `app/Http/Middleware/VerifyCsrfToken.php` - no existe
- **Severidad:** BAJO
- **Evidencia:** `grep -rn "VerifyCsrfToken" app/Http/ --include="*.php"` no devolvió resultados. `grep -rn "except" app/Http/Middleware/VerifyCsrfToken.php 2>/dev/null` no devolvió resultados.
- **Problema técnico:** No hay bypass CSRF local visible; Laravel 13 maneja middleware desde bootstrap/framework. Este punto queda documentado como control revisado sin hallazgo explotable.
- **Impacto:** Sin impacto directo observado.
- **Solución:**
```php
// Mantener formularios POST/PUT/PATCH/DELETE con @csrf y evitar excepciones globales.
```
