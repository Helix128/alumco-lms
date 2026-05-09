# Auditoría 04 - Código

### [CODE-001] Controladores de usuario/módulos concentran demasiada lógica de dominio
- **Archivo:** `app/Http/Controllers/ModuloController.php` - línea 1
- **Severidad:** MEDIO
- **Evidencia:** `for f in app/Http/Controllers/**/*.php app/Http/Controllers/*.php; do echo "$f: $(wc -l < "$f") líneas"; done 2>/dev/null`
```text
app/Http/Controllers/Capacitador/CursoController.php: 161 líneas
app/Http/Controllers/CursoController.php: 182 líneas
app/Http/Controllers/ModuloController.php: 255 líneas
```
`grep -n "public function" app/Http/Controllers/Capacitador/CursoController.php app/Http/Controllers/CursoController.php app/Http/Controllers/ModuloController.php | wc -l`
```text
15
```
- **Problema técnico:** `ModuloController` mezcla autorización, carga de relaciones, reglas de secuencia, streaming de archivos, headers HTTP y navegación. `CursoController` mezcla clasificación de cursos, progreso, disponibilidad y preview mode.
- **Impacto:** Mayor riesgo de regresiones al tocar autorización/disponibilidad y dificultad para cubrir ramas con pruebas enfocadas.
- **Solución:**
```php
final readonly class ModuleAccessService
{
    public function authorizeWorkerAccess(Curso $curso, Modulo $modulo, User $user): Modulo
    {
        // Centralizar disponibilidad, pertenencia, secuencia y carga de relaciones.
    }
}
```

### [CODE-002] Generación de certificado oculta fallas completamente en evaluación
- **Archivo:** `app/Livewire/VerEvaluacion.php` - línea 146
- **Severidad:** MEDIO
- **Evidencia:** `grep -rn "catch (\|catch(" app/ --include="*.php"`
```text
app/Livewire/VerEvaluacion.php:146:                } catch (\Throwable) {
```
`cat app/Livewire/VerEvaluacion.php`
```text
try {
    app(CertificadoService::class)->generarParaUsuario(auth()->user(), $this->curso);
    $this->certificadoGenerado = true;
} catch (\Throwable) {
    // No bloquear al usuario si la generación falla
}
```
- **Problema técnico:** La excepción se descarta sin `report()`, métrica ni registro. La decisión de no bloquear al usuario es válida, pero perder la señal impide detectar fallos de PDF, storage o notificaciones.
- **Impacto:** Usuarios pueden completar cursos sin certificado generado y soporte no tendrá evidencia operativa del fallo.
- **Solución:**
```php
try {
    app(CertificadoService::class)->generarParaUsuario(auth()->user(), $this->curso);
    $this->certificadoGenerado = true;
} catch (\Throwable $exception) {
    report($exception);
}
```

### [CODE-003] Lógica de reportes duplicada entre servicio y exportación Excel
- **Archivo:** `app/Exports/ReporteExport.php` - líneas 113-170
- **Severidad:** MEDIO
- **Evidencia:** `cat app/Services/Reports/AdminTrainingReportQuery.php`
```text
public function participants(ReportFilters $reportFilters, ?Curso $selectedCourse, array $ageBounds): Builder
```
`cat app/Exports/ReporteExport.php`
```text
$query = User::with(['estamento', 'sede', 'certificados.curso'])
    ->whereNotNull('estamento_id');
...
$query = match ($this->estadoCapacitacion) {
```
- **Problema técnico:** El servicio `AdminTrainingReportQuery` ya encapsula filtros de participantes, edad, curso y certificados, pero `ReporteExport` reimplementa filtros equivalentes desde `Request`. Hay dos fuentes de verdad para el mismo reporte.
- **Impacto:** Diferencias entre pantalla y Excel ante cambios futuros de filtros, estado de capacitación o rango etario.
- **Solución:**
```php
public function query()
{
    return app(AdminTrainingReportQuery::class)->participants(
        ReportFilters::fromRequest($this->request),
        $this->cursoSeleccionado,
        $this->ageBounds
    );
}
```

### [CODE-004] Comentarios TODO/FIXME no aparecen, pero hay comentarios de lógica en controlador
- **Archivo:** `app/Http/Controllers/CursoController.php` - línea 132
- **Severidad:** BAJO
- **Evidencia:** `grep -rn "TODO\|FIXME\|HACK\|@deprecated" app/ --include="*.php"`
```text
app/Models/Modulo.php:62:    // --- MÉTODOS HELPER ---
app/Http/Controllers/CursoController.php:132:                // Cargamos TODOS los módulos para que la lógica de acceso sea coherente
```
- **Problema técnico:** No hay deuda explícita tipo TODO/FIXME, pero el comentario revela una decisión de carga completa para sostener reglas de acceso en controlador.
- **Impacto:** Mantiene acoplamiento entre consulta, autorización y vista.
- **Solución:**
```php
// Mover la decisión a un servicio probado y dejar el controlador como orquestador.
$curso = $courseAccessViewModel->forUser($curso, auth()->user());
```
