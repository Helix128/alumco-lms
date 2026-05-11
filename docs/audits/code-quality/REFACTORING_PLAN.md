# Plan de Refactoring

## Criterios de priorización
- Impacto en seguridad o producción → Sprint 0 (inmediato)
- Deuda técnica alta con alto retorno → Sprint 1
- Mejoras de arquitectura → Sprint 2
- Optimizaciones y nice-to-haves → Sprint 3+

## Sprint 0 — Correcciones Críticas (esta semana)

### ✅ TASK-001: Actualizar PhpSpreadsheet vulnerable
- **Qué:** `phpoffice/phpspreadsheet 1.30.0` tiene 5 advisories reportados por `composer audit`.
- **Dónde:** `composer.lock` líneas 3650-3651; dependencia indirecta desde `composer.json` línea 18.
- **Cómo:** ejecutar `./vendor/bin/sail composer update phpoffice/phpspreadsheet maatwebsite/excel --with-all-dependencies`; luego `./vendor/bin/sail composer audit`.
- **Esfuerzo estimado:** 1h
- **Riesgo de regresión:** Medio, por exportación Excel.
- **Validación:** `./vendor/bin/sail artisan test --compact tests/Feature/Admin/ReportsTest.php` y exportación manual de reportes.

### ✅ TASK-002: Eliminar asignación automática de roles en login
- **Qué:** `AuthController@login` asigna roles por email en tiempo de autenticación.
- **Dónde:** `app/Http/Controllers/AuthController.php` líneas 38-43.
- **Cómo:** eliminar bloque; mover corrección de datos a seeder/comando idempotente; agregar test que verifique que login no eleva privilegios.
- **Esfuerzo estimado:** 2-4h
- **Riesgo de regresión:** Medio
- **Validación:** `./vendor/bin/sail artisan test --compact tests/Feature/UserAreaAccessTest.php tests/Feature/Admin/UserHierarchyTest.php`

### ✅ TASK-003: Corregir N+1 de participantes
- **Qué:** cada participante ejecuta queries individuales para progreso y certificado.
- **Dónde:** `app/Http/Controllers/Capacitador/ParticipanteController.php` líneas 41-51 y 82-92.
- **Cómo:** precalcular progresos agrupados por `user_id` y certificados con `whereIn()->get()->keyBy('user_id')`; reutilizar método privado para `index()` y `exportar()`.
- **Esfuerzo estimado:** 2-4h
- **Riesgo de regresión:** Bajo
- **Validación:** nuevo Feature Test con 3 participantes y assertion de datos; opcional `DB::enableQueryLog()` en test de performance.

### ✅ TASK-004: Agregar CI mínimo
- **Qué:** no hay workflow para bloquear regressions.
- **Dónde:** `.github/workflows/` ausente.
- **Cómo:** crear workflow con composer install, Pint dirty/test, composer audit y `sail` no aplica en CI; usar servicios MySQL/Redis o sqlite testing si la app lo permite.
- **Esfuerzo estimado:** 1 día
- **Riesgo de regresión:** Bajo
- **Validación:** PR verde ejecutando tests.

## Sprint 1 — Refactoring de Alta Prioridad (próximas 2 semanas)

### TASK-005: Crear Policies para `Curso`, `Modulo`, `Certificado` y `User`
- **Qué:** autorización está duplicada en controladores, middleware y modelos.
- **Dónde:** `app/Http/Controllers/Capacitador/*.php`, `app/Livewire/Admin/UserManagement.php`, `app/Models/User.php`.
- **Cómo:** `./vendor/bin/sail artisan make:policy CursoPolicy --model=Curso --no-interaction`; migrar `authorizeCurso()`, jerarquía de usuarios y descarga de certificados; usar `$this->authorize()` o `Gate::authorize()`.
- **Esfuerzo estimado:** 2-3 días
- **Riesgo de regresión:** Alto
- **Validación:** ampliar `Admin/UserHierarchyTest.php`, `UserAreaAccessTest.php`, `CursoPreviewTest.php`.

### ✅ TASK-006: Extraer lógica de participantes a Query/Service (método privado compartido)
- **Qué:** `index()` y `exportar()` duplican armado de participantes.
- **Dónde:** `app/Http/Controllers/Capacitador/ParticipanteController.php`.
- **Cómo:** crear `CourseParticipantProgressQuery` que devuelva DTO/array con usuario, progreso y certificado.
- **Esfuerzo estimado:** 1 día
- **Riesgo de regresión:** Medio
- **Validación:** tests de listado/exportación con usuarios duplicados por estamentos.

### ✅ TASK-007: Mover extracción de color fuera del modelo
- **Qué:** `Curso` procesa archivos e imágenes con GD.
- **Dónde:** `app/Models/Curso.php` líneas 22-186.
- **Cómo:** crear `AverageCourseCoverColor` service; invocarlo desde controller/action después del upload.
- **Esfuerzo estimado:** 1 día
- **Riesgo de regresión:** Medio
- **Validación:** test con `Storage::fake('public')` y factory de curso con portada.

### TASK-008: Form Requests para curso, módulo, secciones y reportes
- **Qué:** validaciones inline repetidas.
- **Dónde:** `Capacitador/CursoController.php`, `Capacitador/ModuloController.php`, `SeccionCursoController.php`, `ReporteController.php`.
- **Cómo:** crear Requests con `rules()` y `authorize()`; usar `$request->validated()`.
- **Esfuerzo estimado:** 2-3 días
- **Riesgo de regresión:** Medio
- **Validación:** tests existentes de creación/edición; agregar failure paths.

## Sprint 2 — Mejoras Arquitectónicas (próximo mes)

### ✅ TASK-009: Dividir `CalendarioCapacitaciones`
- **Qué:** componente de 1.596 líneas con demasiadas responsabilidades.
- **Dónde:** `app/Livewire/Capacitador/CalendarioCapacitaciones.php`.
- **Cómo:** extraer `CalendarPlanningRepository`, `CalendarGridBuilder`, `YearPlanningCopyAction`, `CalendarCacheKey`; dejar Livewire como orquestador de estado.
- **Esfuerzo estimado:** 2-3 días
- **Riesgo de regresión:** Alto
- **Validación:** `tests/Feature/CalendarioCapacitacionesTest.php` completo.

### ✅ TASK-010: Separar generación de certificados
- **Qué:** `CertificadoService` valida, genera PDF/QR, notifica y limpia storage.
- **Dónde:** `app/Services/CertificadoService.php`.
- **Cómo:** extraer `CertificateEligibility`, `CertificatePdfRenderer`, `QrCodeGenerator`, `CertificateNotifier`.
- **Esfuerzo estimado:** 2 días
- **Riesgo de regresión:** Medio
- **Validación:** `CertificadoPdfGenerationTest.php`, `CourseNotificationTest.php`.

### ✅ TASK-011: Mover lógica de jerarquía de usuarios
- **Qué:** `User` contiene roles, jerarquía y reglas de gestión.
- **Dónde:** `app/Models/User.php` líneas 78-147.
- **Cómo:** mover a Policy o service de autorización; dejar helpers mínimos si son ampliamente usados.
- **Esfuerzo estimado:** 1 día
- **Riesgo de regresión:** Medio
- **Validación:** `Admin/UserHierarchyTest.php`.

## Sprint 3 — Optimización y Deuda Técnica Menor

### ✅ TASK-012: Endurecer configuración de producción
- **Qué:** defaults de sesión/upload no declaran seguridad explícita.
- **Dónde:** `.env.example`, `config/session.php`, `config/livewire.php`.
- **Cómo:** documentar `SESSION_SECURE_COOKIE=true`, reglas Livewire upload, throttle explícito y headers de seguridad.
- **Esfuerzo estimado:** 2-4h
- **Riesgo de regresión:** Bajo
- **Validación:** test de cookies/headers y revisión de entorno.

### TASK-013: Limpiar comentarios de “qué”
- **Qué:** comentarios narran pasos obvios.
- **Dónde:** `DuplicateCourseAction.php`, `AuthController.php`, varios controladores.
- **Cómo:** borrar ruido y dejar PHPDoc solo donde aporte contrato/razón.
- **Esfuerzo estimado:** 2-4h
- **Riesgo de regresión:** Bajo
- **Validación:** Pint.

### TASK-014: Reemplazar números mágicos por constantes
- **Qué:** años, tamaños, porcentajes, buckets y límites inline.
- **Dónde:** `Curso.php`, `CalendarioCapacitaciones.php`, `CertificadoService.php`.
- **Cómo:** constantes privadas con nombres de dominio.
- **Esfuerzo estimado:** 2-4h
- **Riesgo de regresión:** Bajo
- **Validación:** tests existentes.

## Dependencias entre tareas
- TASK-001 debe ejecutarse antes de ampliar exportaciones/importaciones Excel.
- TASK-002 debe completarse antes de centralizar Policies para no conservar bypasses.
- TASK-005 desbloquea TASK-008 y TASK-011.
- TASK-003 conviene hacerse antes de TASK-006 para bajar riesgo en producción rápido.
- TASK-009 depende de tests actuales del calendario; no iniciar sin suite verde.

## Métricas de éxito
- `composer audit` sin vulnerabilidades.
- 0 asignaciones de rol durante login.
- Participantes: queries constantes o agrupadas, no 2N.
- `CalendarioCapacitaciones` por debajo de 400 líneas y lógica pura testeable fuera de Livewire.
- Cobertura Feature para login, roles, certificados, calendario, reportes y sanitización XSS.
- CI ejecuta auditoría, Pint y tests en cada PR.
