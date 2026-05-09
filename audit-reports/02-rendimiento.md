# Auditoría 02 - Rendimiento

### [PERF-001] Dashboard admin carga todos los cursos y usuarios relacionados en memoria
- **Archivo:** `app/Http/Controllers/Admin/DashboardController.php` - línea 35
- **Severidad:** MEDIO
- **Evidencia:** `grep -rn -- "->get()" app/ --include="*.php" | grep -v paginate | grep -v chunk`
```text
app/Http/Controllers/Admin/DashboardController.php:35:                Curso::with(['estamentos.users', 'planificaciones'])->get()
```
`cat app/Http/Controllers/Admin/DashboardController.php`
```text
return $analyticsService->summaryForCourses(
    Curso::with(['estamentos.users', 'planificaciones'])->get()
);
```
- **Problema técnico:** El dashboard administrativo hidrata todos los cursos con estamentos, usuarios y planificaciones para calcular métricas. Aunque está cacheado con `Cache::flexible`, cada regeneración puede consumir mucha memoria y bloquear la request.
- **Impacto:** Latencia alta, riesgo de memory exhaustion y timeouts cuando crezcan cursos, usuarios o planificaciones.
- **Solución:**
```php
$lmsStats = Cache::flexible('admin_dashboard_lms_stats_v2', [60, 300], function () use ($analyticsService): array {
    return $analyticsService->summaryFromAggregates();
});
```

### [PERF-002] Dashboard capacitador duplica carga completa de cursos para analítica
- **Archivo:** `app/Http/Controllers/Capacitador/DashboardController.php` - líneas 31-48
- **Severidad:** MEDIO
- **Evidencia:** `grep -rn -- "->get()\|->all()\|->paginate(" app/Http/Controllers/ --include="*.php"`
```text
app/Http/Controllers/Capacitador/DashboardController.php:32:                    ->get();
app/Http/Controllers/Capacitador/DashboardController.php:48:                        ->get()
app/Http/Controllers/Capacitador/DashboardController.php:66:                        ->all(),
```
- **Problema técnico:** Primero carga todos los cursos del scope con counts y después vuelve a consultar esos cursos con `estamentos.users` y `planificaciones` para `LearningAnalyticsService`.
- **Impacto:** Doble trabajo de base de datos y mayor uso de memoria por request cuando expira el cache.
- **Solución:**
```php
$courseIds = $cursosQuery->pluck('id');

$learningStats = $analyticsService->summaryForCourseIds($courseIds);
```

### [PERF-003] Listado de cursos del trabajador no pagina ni limita resultados
- **Archivo:** `app/Http/Controllers/CursoController.php` - líneas 18-24
- **Severidad:** BAJO
- **Evidencia:** `grep -rn -- "->get()\|->all()\|->paginate(" app/Http/Controllers/ --include="*.php"`
```text
app/Http/Controllers/CursoController.php:18:            $cursos = $this->previewCoursesQuery($user)->get();
app/Http/Controllers/CursoController.php:24:                    ->get()
```
`grep -rn "foreach\|->each(" app/Http/Controllers/ --include="*.php"`
```text
app/Http/Controllers/CursoController.php:32:        foreach ($cursos as $curso) {
```
- **Problema técnico:** La pantalla carga todos los cursos asignados y los procesa en memoria para separarlos en vigentes, completados y anteriores. No hay paginación ni límite.
- **Impacto:** Degradación progresiva en usuarios con muchos cursos históricos o en modo preview admin/dev.
- **Solución:**
```php
$cursos = $user->estamento
    ? $user->estamento->cursos()
        ->with($this->courseRelationsFor($user))
        ->latest()
        ->paginate(12)
    : collect();
```

### [PERF-004] Render de gestión de tickets ejecuta contadores completos en cada actualización Livewire
- **Archivo:** `app/Livewire/Support/ManageTickets.php` - líneas 224-230
- **Severidad:** MEDIO
- **Evidencia:** `grep -rn "Cache::\|cache(" app/ --include="*.php"` no incluye `app/Livewire/Support/ManageTickets.php`. `cat app/Livewire/Support/ManageTickets.php`
```text
'new' => SupportTicket::where('status', SupportTicket::StatusNew)->count(),
'critical' => SupportTicket::open()->where('priority', SupportTicket::PriorityCritical)->count(),
'waiting' => SupportTicket::where('status', SupportTicket::StatusWaitingUser)->count(),
'resolved_recent' => SupportTicket::where('status', SupportTicket::StatusResolved)
    ->where('resolved_at', '>=', now()->subDays(7))
    ->count(),
```
- **Problema técnico:** Cada render Livewire hace la consulta paginada y cuatro `count()` adicionales sin cache ni agregación única.
- **Impacto:** Búsquedas, cambios de filtro o polling futuro multiplicarán consultas contra `support_tickets`.
- **Solución:**
```php
$counters = Cache::flexible('support_ticket_counters', [15, 60], function (): array {
    return [
        'new' => SupportTicket::where('status', SupportTicket::StatusNew)->count(),
        'critical' => SupportTicket::open()->where('priority', SupportTicket::PriorityCritical)->count(),
        'waiting' => SupportTicket::where('status', SupportTicket::StatusWaitingUser)->count(),
        'resolved_recent' => SupportTicket::where('status', SupportTicket::StatusResolved)
            ->where('resolved_at', '>=', now()->subDays(7))
            ->count(),
    ];
});
```

### [PERF-005] Notificaciones de soporte se envían síncronamente tras crear ticket
- **Archivo:** `app/Actions/Support/CreateSupportTicketAction.php` - líneas 43-50
- **Severidad:** MEDIO
- **Evidencia:** `grep -rn "Mail::send\|Mail::queue\|Notification::send" app/ --include="*.php"`
```text
app/Actions/Support/CreateSupportTicketAction.php:44:            Notification::send($developers, new SupportTicketCreatedNotification($ticket));
```
`cat app/Actions/Support/CreateSupportTicketAction.php`
```text
Notification::send($developers, new SupportTicketCreatedNotification($ticket));
$requester->notify(new SupportTicketRequesterNotification($ticket, 'created'));
```
- **Problema técnico:** Aunque las notificaciones implementan `ShouldQueue`, la acción no fuerza `afterCommit()` ni encapsula el envío fuera de la request. En entornos con cola mal configurada o driver sync, la creación del ticket queda atada al envío de correo.
- **Impacto:** Latencia alta o error visible al usuario si SMTP/cola falla durante creación del ticket.
- **Solución:**
```php
Notification::send($developers, (new SupportTicketCreatedNotification($ticket))->afterCommit());

$requester?->notify((new SupportTicketRequesterNotification($ticket, 'created'))->afterCommit());
```
