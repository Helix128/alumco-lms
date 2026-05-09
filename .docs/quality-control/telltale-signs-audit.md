# Auditoria telltale signs IA - Alumco LMS

Fecha: 2026-05-06  
Alcance: Laravel app actual en `app/`, `routes/`, `database/`, `tests/` y vistas relacionadas.  
Nota: varios archivos tienen cambios sin commit; este reporte audita el estado de trabajo actual sin revertir ni modificar codigo de produccion.

## Telltale Signs Encontrados

1. Variables genericas en flujos de dominio (`$data`, `$query`, `$sent`) donde el nombre no comunica el caso Alumco.
2. Controladores con demasiadas responsabilidades: filtrado, calculo, query building y presentacion en el mismo metodo.
3. Validacion duplicada o poco contextual en Form Requests y componentes Livewire.
4. `catch` amplio con mensajes genericos o perdida de contexto operacional.
5. Comandos de notificaciones casi gemelos, con copia estructural evidente.
6. Queries con riesgo de N+1 o recarga por trabajador en loops.
7. Comentarios que explican pasos obvios o scaffolding Laravel por defecto.
8. Migracion grande que mezcla varias preocupaciones de producto.
9. Factories con datos "Test" o faker generico poco representativo del dominio ONG/capacitacion.
10. Tests boilerplate y algunos tests que validan poco mas que status/render.
11. Middleware con mensajes genericos repetidos.
12. Respuestas Livewire demasiado genericas y sin contexto de negocio.

## Reporte Detallado

### Finding #1: Variables genericas en flujo de reporteria

Severidad: MEDIA  
Ubicacion: `app/Http/Controllers/ReporteController.php:21`

Evidencia:

```php
$data = $request->validated();
$estamentos = Estamento::all();
$cursos = Curso::all();
$sedes = Sede::all();
```

Por que grita "IA": `$data` es el contenedor generico clasico para todo lo validado. En un reporte administrativo con filtros por sede, estamento, curso, edad y estado, el nombre no ayuda a entender el dominio ni reduce la carga mental.

Indicador de humano: un developer senior separaria filtros normalizados de catalogos para la vista.

Transformacion sugerida:

```php
$reportFilters = $request->validated();
$filterCatalogs = [
    'estamentos' => Estamento::query()->orderBy('nombre')->get(),
    'cursos' => Curso::query()->orderBy('titulo')->get(),
    'sedes' => Sede::query()->orderBy('nombre')->get(),
];
```

Estrategia aplicada: 6, nombre explicito y especifico.

### Finding #2: Metodo `index` hace demasiado en reporteria

Severidad: ALTA  
Ubicacion: `app/Http/Controllers/ReporteController.php:21`

Evidencia:

```php
public function index(ReportFilterRequest $request)
{
    $data = $request->validated();
    ...
    $query = User::with(['estamento', 'sede', 'certificados.curso'])
        ->whereNotNull('estamento_id');
    ...
    $usuarios = $query->paginate(15)->withQueryString();
}
```

Por que grita "IA": el metodo arma catalogos, calcula limites de edad, normaliza filtros, construye consulta, aplica reglas de progreso y decide datos de vista. Es el patron "metodo que hace todo un poco".

Indicador de humano: mover la normalizacion y query a una clase con lenguaje del negocio, por ejemplo `AdminTrainingReportQuery`.

Transformacion sugerida:

```php
public function index(ReportFilterRequest $request): View
{
    $trainingReport = $this->trainingReportQuery->forFilters(
        ReportFilters::fromRequest($request)
    );

    return view('admin.reportes.index', [
        'usuarios' => $trainingReport->participants()->paginate(15)->withQueryString(),
        ...$trainingReport->catalogs(),
        ...$trainingReport->selectedFilters(),
    ]);
}
```

Estrategia aplicada: 5, extraer a domain class / value object.

### Finding #3: Comentarios numerados explicando pasos obvios

Severidad: BAJA  
Ubicacion: `app/Http/Controllers/ReporteController.php:56`

Evidencia:

```php
// 1. Filtro por Estamento
// 2. Filtro por Sede
// 3. Filtro por Curso
```

Por que grita "IA": comentarios secuenciales que repiten el `if` siguiente parecen generados para narrar el codigo, no para justificar decisiones tecnicas.

Indicador de humano: comentar solo la regla no obvia, por ejemplo por que el filtro de cursos usa AND y no OR.

Transformacion sugerida:

```php
// Cuando se seleccionan varios cursos, el reporte busca trabajadores con todos
// esos certificados, no cualquiera de ellos. Esto evita inflar cumplimiento.
foreach ($selectedCourseIds as $courseId) {
    $participantsQuery->whereHas('certificados', fn ($certificateQuery) => (
        $certificateQuery->where('curso_id', $courseId)
    ));
}
```

Estrategia aplicada: 2, agregar razonamiento tecnico.

### Finding #4: Requests de curso duplicados sin customizacion

Severidad: MEDIA  
Ubicacion: `app/Http/Requests/Capacitador/StoreCursoRequest.php:15` y `app/Http/Requests/Capacitador/UpdateCursoRequest.php:19`

Evidencia:

```php
return [
    'titulo' => ['required', 'string', 'max:255'],
    'descripcion' => ['nullable', 'string'],
    'nota_capacitador' => ['nullable', 'string', 'max:1200'],
    'imagen_portada' => ['nullable', 'image', 'max:4096'],
    'color_promedio' => ['nullable', 'string', 'max:7'],
    'auto_color' => ['nullable', 'boolean'],
];
```

Por que grita "IA": `store` y `update` son practicamente identicos. Ademas `color_promedio` solo valida string de largo 7, sin formato hex ni relacion con `auto_color`.

Indicador de humano: extraer reglas compartidas y agregar reglas de negocio: formato hex, imagen obligatoria o no segun estado, mensajes en espanol.

Transformacion sugerida:

```php
private function courseContentRules(): array
{
    return [
        'titulo' => ['required', 'string', 'max:255'],
        'descripcion' => ['nullable', 'string'],
        'nota_capacitador' => ['nullable', 'string', 'max:1200'],
        'imagen_portada' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:4096'],
        'color_promedio' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
        'auto_color' => ['nullable', 'boolean'],
    ];
}
```

Estrategia aplicada: 4, edge cases y validaciones reales.

### Finding #5: Validacion de modulos duplicada y string-concatenated

Severidad: MEDIA  
Ubicacion: `app/Http/Requests/Capacitador/StoreModuloRequest.php:25` y `app/Http/Requests/Capacitador/UpdateModuloRequest.php:25`

Evidencia:

```php
$mimeRules = match ($tipoContenido) {
    'video' => 'mimes:mp4',
    'pdf' => 'mimes:pdf',
    'ppt' => 'mimes:ppt,pptx',
    'imagen' => 'mimes:jpeg,png,jpg,gif,webp',
    default => '',
};

$fileRule = 'nullable|file|max:512000'.($mimeRules ? '|'.$mimeRules : '');
```

Por que grita "IA": se repite la matriz de mimes y se construyen reglas como string. En Laravel moderno el estilo de array es mas claro y evita bugs al concatenar.

Indicador de humano: una clase o metodo dedicado como `ModuloContentFileRules::forType($tipo)` con limites por tipo.

Transformacion sugerida:

```php
$moduleFileRules = ModuleContentFileRules::forType($tipoContenido);

'ruta_archivo' => [
    'nullable',
    'file',
    ...$moduleFileRules->validationRules(),
],
```

Estrategia aplicada: 5, extraer a domain class / value object.

### Finding #6: `catch` amplio que expone mensaje interno

Severidad: ALTA  
Ubicacion: `app/Http/Controllers/Capacitador/CertificadoController.php:19`

Evidencia:

```php
try {
    $service->generarParaUsuario($user, $curso);

    return redirect()->back()->with('success', "Certificado generado para {$user->name}.");
} catch (\Throwable $e) {
    return redirect()->back()->with('error', 'No se pudo generar el certificado: '.$e->getMessage());
}
```

Por que grita "IA": captura todo y devuelve `getMessage()` al usuario. Eso mezcla errores de dominio con errores tecnicos y puede filtrar detalles internos.

Indicador de humano: excepciones de dominio para elegibilidad, log con contexto y mensaje publico estable.

Transformacion sugerida:

```php
try {
    $service->generarParaUsuario($user, $curso);
} catch (CertificateNotEligible $exception) {
    return back()->with('error', $exception->publicMessage());
} catch (\Throwable $exception) {
    report($exception);

    return back()->with('error', 'No se pudo generar el certificado. Revisa que el trabajador haya aprobado la evaluacion y vuelve a intentarlo.');
}
```

Estrategia aplicada: 4, edge cases y validaciones reales.

### Finding #7: Servicio de color con `catch` silencioso y variables ultra genericas

Severidad: MEDIA  
Ubicacion: `app/Services/Cursos/AverageCourseCoverColor.php:37`

Evidencia:

```php
try {
    $info = getimagesize($path);
    ...
    $img = match ($type) { ... };
    ...
    $tmp = imagecreatetruecolor($sampleW, $sampleH);
} catch (\Exception) {
    return null;
}
```

Por que grita "IA": `$info`, `$img`, `$tmp`, `$r`, `$g`, `$b` son aceptables en algoritmos, pero combinados con `catch (\Exception)` silencioso dificultan diagnosticar imagenes invalidas o GD faltante.

Indicador de humano: reportar solo fallos inesperados y devolver fallback consistente para portada.

Transformacion sugerida:

```php
try {
    $imageMetadata = getimagesize($path);
    ...
} catch (\Throwable $exception) {
    report($exception);

    return self::FALLBACK_COLOR;
}
```

Estrategia aplicada: 2, agregar razonamiento tecnico.

### Finding #8: Comandos de notificacion duplicados

Severidad: ALTA  
Ubicacion: `app/Console/Commands/SendCourseAvailableNotifications.php:54` y `app/Console/Commands/SendCourseDeadlineReminderNotifications.php:53`

Evidencia:

```php
private function notifyWorkersFor(PlanificacionCurso $planificacion): int
{
    $curso = $planificacion->curso;
    ...
    User::query()
        ->role('Trabajador')
        ->where('activo', true)
        ->whereIn('estamento_id', $estamentoIds)
        ->when($planificacion->sede_id, ...)
        ->chunkById(100, function ($workers) ...)
}
```

Por que grita "IA": ambos comandos tienen la misma estructura con pequenas variaciones: fecha objetivo, criterio de progreso, dedupe key y notification class. Es copia-pega funcional.

Indicador de humano: extraer un `CourseNotificationAudience` o `ScheduledCourseNotificationDispatcher` y dejar cada comando solo con su regla.

Transformacion sugerida:

```php
$sent = $this->dispatcher->sendForPlanningWindow(
    planningQuery: CoursePlanningWindow::availableToday(self::TIMEZONE),
    notificationRule: CourseNotificationRule::availableCourse(),
);
```

Estrategia aplicada: 5, extraer a domain classes.

### Finding #9: Riesgo N+1 por recargar modulos por trabajador

Severidad: ALTA  
Ubicacion: `app/Console/Commands/SendCourseAvailableNotifications.php:77` y `app/Console/Commands/SendCourseDeadlineReminderNotifications.php:76`

Evidencia:

```php
foreach ($workers as $worker) {
    if ($this->progressFor($worker, $curso) === 100) {
        continue;
    }
}

private function progressFor(User $user, Curso $curso): int
{
    $curso->unsetRelation('modulos');
    $curso->load(['modulos' => function ($query) use ($user): void {
        $query->orderBy('orden')
            ->with(['progresos' => fn ($query) => $query->where('user_id', $user->id)]);
    }]);
}
```

Por que grita "IA": el loop es correcto para pocos usuarios, pero a escala ONG por sede/estamento recarga modulos y progresos por trabajador. La intencion es clara, la implementacion parece happy-path.

Indicador de humano: precalcular progreso por lote de trabajadores con una query agregada.

Transformacion sugerida:

```php
$progressByWorker = $this->courseProgressRepository->percentagesForWorkers(
    curso: $curso,
    workerIds: $workers->pluck('id')
);

foreach ($workers as $worker) {
    if (($progressByWorker[$worker->id] ?? 0) >= $minimumProgressToSkip) {
        continue;
    }
}
```

Estrategia aplicada: 4, edge cases reales y optimizacion de query.

### Finding #10: Mensajes Livewire genericos

Severidad: BAJA  
Ubicacion: `app/Livewire/Cursos/CourseConsultations.php:43`, `app/Livewire/Feedback/PlatformFeedbackWidget.php:40`

Evidencia:

```php
$this->mensaje = 'Consulta enviada.';
$this->estado = 'Feedback enviado.';
```

Por que grita "IA": no hay contexto del negocio ni estado posterior util. En una capacitacion, el usuario espera saber si la pregunta quedo publica/privada o si la revisara un capacitador.

Indicador de humano: mensajes orientados a flujo real.

Transformacion sugerida:

```php
$this->mensaje = $data['visibilidad'] === ConsultaCurso::VisibilidadPrivada
    ? 'Consulta privada enviada al capacitador.'
    : 'Consulta publicada para el curso.';
```

Estrategia aplicada: 1, humanizar con dominio de negocio.

### Finding #11: Middleware con mensajes repetidos

Severidad: BAJA  
Ubicacion: `app/Http/Middleware/EnsureAdminAccess.php:18` y `app/Http/Middleware/EnsureCapacitadorAccess.php:13`

Evidencia:

```php
abort(403, 'No tienes permisos para acceder a esta área.');
```

Por que grita "IA": el mismo mensaje generico aparece para areas distintas. No distingue admin, capacitador o trabajador.

Indicador de humano: errores especificos segun area, o centralizar en una clase de acceso con codigos traducibles.

Transformacion sugerida:

```php
abort(403, 'Esta seccion requiere perfil de capacitador interno o administracion.');
```

Estrategia aplicada: 3, customizar para caso de uso.

### Finding #12: Migracion mezcla tres conceptos de producto

Severidad: MEDIA  
Ubicacion: `database/migrations/2026_05_06_100000_add_lms_communication_feedback_and_health_tables.php:14`

Evidencia:

```php
Schema::table('cursos', ... nota_capacitador ...);
Schema::create('consultas_curso', ...);
Schema::create('feedbacks', ...);
Schema::create('system_task_runs', ...);
```

Por que grita "IA": una sola migracion introduce notas del capacitador, consultas, feedback y health tracking. Son preocupaciones distintas, lo que dificulta revertir o auditar decisiones.

Indicador de humano: separar migraciones por dominio y dejar nombres que expliquen rollout.

Transformacion sugerida:

```php
2026_05_06_100000_add_facilitator_notes_to_courses.php
2026_05_06_100100_create_course_consultations_table.php
2026_05_06_100200_create_feedbacks_table.php
2026_05_06_100300_create_system_task_runs_table.php
```

Estrategia aplicada: 3, customizar para caso de uso.

### Finding #13: Comentarios scaffolding en migracion

Severidad: BAJA  
Ubicacion: `database/migrations/2026_05_06_100000_add_lms_communication_feedback_and_health_tables.php:9`

Evidencia:

```php
/**
 * Run the migrations.
 */
```

Por que grita "IA": es comentario por defecto de Laravel, no aporta contexto. En migraciones de producto seria mas valioso documentar por que existe un indice o unique.

Indicador de humano: eliminarlo o reemplazar por razonamiento de indice.

Transformacion sugerida:

```php
// Un feedback de curso por usuario evita duplicados al reabrir el formulario;
// el feedback de plataforma queda fuera porque no tiene curso asociado.
$table->unique(['user_id', 'curso_id', 'tipo'], 'feedbacks_user_course_type_unique');
```

Estrategia aplicada: 2, agregar razonamiento tecnico.

### Finding #14: Factories con datos poco realistas

Severidad: MEDIA  
Ubicacion: `database/factories/ConsultaCursoFactory.php:24`, `database/factories/FeedbackFactory.php:27`, `tests/Feature/LmsEnhancementsTest.php:126`

Evidencia:

```php
'pregunta' => $this->faker->sentence(12),
'mensaje' => $this->faker->sentence(),
$sede = Sede::create(['nombre' => 'Sede Test']);
$estamento = Estamento::create(['nombre' => 'Estamento Test']);
```

Por que grita "IA": `Test` y frases aleatorias no reflejan capacitaciones de salud, seguridad o procedimientos internos. Los tests pierden valor como documentacion.

Indicador de humano: datos semanticos y estados de factory que expresen flujos reales.

Transformacion sugerida:

```php
'pregunta' => '¿El protocolo de higiene aplica tambien al turno nocturno?',
'mensaje' => 'La capsula de RCP fue clara, pero falta un ejemplo para pacientes pediatricos.',
$sede = Sede::create(['nombre' => 'Hospital San Jose']);
$estamento = Estamento::create(['nombre' => 'TENS']);
```

Estrategia aplicada: 1, humanizar con dominio de negocio.

### Finding #15: Tests ejemplo boilerplate

Severidad: ALTA  
Ubicacion: `tests/Unit/ExampleTest.php:9` y `tests/Feature/ExampleTest.php:9`

Evidencia:

```php
/**
 * A basic test example.
 */
public function test_that_true_is_true(): void
{
    $this->assertTrue(true);
}
```

Por que grita "IA": es scaffolding intacto. No prueba comportamiento de Alumco LMS y es una firma clara de proyecto generado.

Indicador de humano: borrar o reemplazar por un test minimo de dominio.

Transformacion sugerida:

```php
public function test_trabajador_without_session_is_redirected_to_login_from_courses(): void
{
    $this->get(route('cursos.index'))
        ->assertRedirect(route('login'));
}
```

Estrategia aplicada: 4, agregar caso real.

### Finding #16: Tests de dashboard validan render, no cache real

Severidad: MEDIA  
Ubicacion: `tests/Feature/CapacitadorDashboardTest.php:24`

Evidencia:

```php
$this->actingAs($capacitador)
    ->get(route('capacitador.dashboard'))
    ->assertOk()
    ->assertSee('Curso programado');

$this->actingAs($capacitador)
    ->get(route('capacitador.dashboard'))
    ->assertOk()
    ->assertSee('Curso programado');
```

Por que grita "IA": el nombre dice que prueba summaries cacheables, pero repite la request y aserciones de texto. No verifica que el cache exista, que use arrays serializables, ni que no haya modelos Eloquent en cache.

Indicador de humano: aserciones sobre cache key y estructura guardada.

Transformacion sugerida:

```php
$cacheKey = "dashboard_summary_v2_capacitador_{$capacitador->id}";

$this->actingAs($capacitador)->get(route('capacitador.dashboard'))->assertOk();

$cachedSummary = Cache::get($cacheKey);

$this->assertIsArray($cachedSummary);
$this->assertSame('Curso programado', $cachedSummary['cursos'][0]['titulo']);
```

Estrategia aplicada: 4, edge cases y validaciones reales.

## Codigo Refactorizado: Antes -> Despues

### Reporteria

Antes:

```php
$data = $request->validated();
$selectedSedes = $this->sanitizeIdFilter($data['sede_id'] ?? []);
```

Despues:

```php
$reportFilters = ReportFilters::fromValidatedInput($request->validated());
$selectedSedeIds = $reportFilters->sedeIds();
```

### Notificaciones

Antes:

```php
foreach ($workers as $worker) {
    $progreso = $this->progressFor($worker, $curso);
}
```

Despues:

```php
$progressByWorker = $this->courseProgressRepository->percentagesForWorkers($curso, $workers->pluck('id'));

foreach ($workers as $worker) {
    $workerProgress = $progressByWorker[$worker->id] ?? 0;
}
```

### Certificados

Antes:

```php
} catch (\Throwable $e) {
    return redirect()->back()->with('error', 'No se pudo generar el certificado: '.$e->getMessage());
}
```

Despues:

```php
} catch (CertificateNotEligible $exception) {
    return back()->with('error', $exception->publicMessage());
} catch (\Throwable $exception) {
    report($exception);

    return back()->with('error', 'No se pudo generar el certificado. Revisa aprobacion y progreso del trabajador.');
}
```

### Tests

Antes:

```php
public function test_that_true_is_true(): void
{
    $this->assertTrue(true);
}
```

Despues:

```php
public function test_worker_cannot_send_feedback_before_finishing_course(): void
{
    [$trabajador, $curso] = $this->assignedWorkerAndCourse();

    Livewire::actingAs($trabajador)
        ->test(CourseFeedbackForm::class, ['curso' => $curso, 'progreso' => 80])
        ->set('rating', 5)
        ->set('categoria', 'utilidad')
        ->call('guardar')
        ->assertForbidden();
}
```

## Checklist de Eliminacion

- Nombres especificos del dominio: parcial. Existen buenos nombres como `cursoSeleccionado`, `estamentoIdsCurso`, `NotificationDelivery`, pero quedan `$data`, `$query`, `$sent`, `$workers`.
- Validaciones de negocio: parcial. Hay reglas de roles y progreso, pero faltan mensajes custom y formato estricto para color/archivos.
- Error handling robusto: parcial. `NotificationDelivery::recordOnce()` maneja duplicados de DB bien; `CertificadoController` y `AverageCourseCoverColor` necesitan mas contexto.
- Documentacion tecnica: parcial. Hay comentarios utiles en carga de modulos, pero tambien comentarios obvios/scaffolding.
- Queries optimizadas: parcial. Reporteria usa eager loading; comandos de notificacion tienen riesgo de recarga por trabajador.
- Testing realista: parcial. `LmsEnhancementsTest` cubre flujos reales, pero quedan ExampleTest y aserciones de dashboard superficiales.
- Inconsistencias intencionales: bien encaminado. Hay Actions, Services, Livewire y Controllers, no todo sigue controller-service-repository.
- Deuda tecnica documentada: baja. No hay TODOs abusivos, pero algunas decisiones de migracion/indices no explican el motivo.

## Resumen de Cambios para Sonar Mas Humano

1. Cambiar nombres genericos por nombres de dominio: `reportFilters`, `selectedCourseIds`, `eligibleWorkers`, `workerProgressPercentages`.
2. Extraer `ReportFilters`, `AdminTrainingReportQuery` y un repositorio de progreso por lote para quitar logica pesada de controladores/comandos.
3. Reemplazar comentarios narrativos por comentarios de decision: por que un filtro usa AND, por que se deduplica una notificacion, por que un indice es compuesto.
4. Sustituir `catch (\Throwable)` de UI por excepciones de dominio y mensajes publicos seguros.
5. Separar migraciones grandes por preocupacion para que el historial cuente el rollout real.
6. Reemplazar tests ejemplo y datos `Test` por casos de capacitacion realista: TENS, RCP, higiene, turnos, vencimientos y certificados.
7. Verificar performance con queries por lote en notificaciones antes de escalar a muchas sedes/estamentos.

