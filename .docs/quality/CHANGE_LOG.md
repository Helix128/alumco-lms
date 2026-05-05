# Change Log — Alumco LMS Refactoring

## [TASK-009] Dividir CalendarioCapacitaciones

- **Archivo(s):** `app/Livewire/Capacitador/CalendarioCapacitaciones.php`
- **Problema resuelto:** CS-004 — componente tipo God Class con carga de datos, cache y copia anual acoplados en una sola clase
- **Cambio realizado:** Se extrajeron responsabilidades a clases dedicadas (`CalendarPlanningRepository`, `CalendarGridBuilder`, `CalendarCacheKeyService`, `CopyYearPlanningAction`) y el componente ahora orquesta flujo/estado delegando consulta, armado anual y copia entre años.
- **Archivos nuevos creados:** `app/Services/Calendario/CalendarPlanningRepository.php`, `app/Services/Calendario/CalendarGridBuilder.php`, `app/Services/Calendario/CalendarCacheKeyService.php`, `app/Actions/Calendario/CopyYearPlanningAction.php`
- **Verificación:** ✓ Lógica preservada / ✓ Sintaxis válida / ✓ Imports correctos / ✓ `CalendarioCapacitacionesTest` verde (37 pruebas)

---

## [TASK-010] Separar generación de certificados

- **Archivo(s):** `app/Services/CertificadoService.php`
- **Problema resuelto:** CS-014 — mezcla de responsabilidades en un único servicio
- **Cambio realizado:** Se extrajo la elegibilidad a `CertificateEligibility` y la renderización PDF/QR a `CertificatePdfRenderer`. `CertificadoService` ahora orquesta creación, notificación y descarga sin concentrar infraestructura.
- **Archivos nuevos creados:** `app/Services/Certificados/CertificateEligibility.php`, `app/Services/Certificados/CertificatePdfRenderer.php`
- **Verificación:** ✓ Lógica preservada / ✓ Sintaxis válida / ✓ Imports correctos / ✓ 11 tests verdes de certificados/notificaciones

---

## [TASK-011] Mover lógica de jerarquía de usuarios

- **Archivo(s):** `app/Services/Authorization/UserHierarchyService.php`, `app/Policies/UserPolicy.php`, `app/Livewire/Admin/UserManagement.php`, `app/Models/User.php`
- **Problema resuelto:** CS-024 — reglas de jerarquía y gestión de usuarios concentradas en `User`
- **Cambio realizado:** Se creó `UserHierarchyService` como lugar canónico para rank por rol y autorización de gestión. `UserPolicy` y `UserManagement` ahora consumen el servicio; `User` conserva wrappers delegando al servicio para compatibilidad.
- **Archivos nuevos creados:** `app/Services/Authorization/UserHierarchyService.php`
- **Verificación:** ✓ Lógica preservada / ✓ Sintaxis válida / ✓ Imports correctos / ✓ `UserHierarchyTest` verde (7 pruebas)

---

## [TASK-007] Mover extracción de color fuera del modelo

- **Archivo(s):** `app/Models/Curso.php`, `app/Http/Controllers/Capacitador/CursoController.php`
- **Problema resuelto:** CS-005 — `Curso` mezclaba persistencia con procesamiento GD de imagen
- **Cambio realizado:** Se extrajo la lógica de color dominante a `AverageCourseCoverColor` y el controlador ahora calcula `color_promedio` en `store/update` cuando `auto_color` está activo. El modelo quedó enfocado en relaciones y reglas de dominio.
- **Archivos nuevos creados:** `app/Services/Cursos/AverageCourseCoverColor.php`, `tests/Feature/Services/AverageCourseCoverColorTest.php`
- **Verificación:** ✓ Lógica preservada / ✓ Sintaxis válida / ✓ Imports correctos / ✓ `12` tests verdes (`AverageCourseCoverColorTest` + `CursoPreviewTest`)

---

## Sprint 1 — Refactoring de Alta Prioridad (continuado)

---

## [TASK-005] Crear Policies para Modulo, Certificado, User

- **Archivo(s):** `app/Policies/ModuloPolicy.php`, `app/Policies/CertificadoPolicy.php`, `app/Policies/UserPolicy.php` (nuevos), `app/Http/Controllers/Controller.php`, `app/Http/Controllers/Capacitador/CertificadoController.php`
- **Problema resuelto:** CS-010 / SEC-003 — autorización duplicada en controladores sin Policies centralizadas; `CertificadoController@authorizeCurso()` hardcodeado
- **Cambio realizado:** Creadas 3 Policies: ModuloPolicy (hereda regla de Curso), CertificadoPolicy (regla `download` específica), UserPolicy (jerarquía via `canManageUser()`). BaseController actualizado con `AuthorizesRequests` trait. CertificadoController reemplazó `authorizeCurso()` y checks manuales por `$this->authorize()`.
- **Archivos nuevos creados:** ModuloPolicy.php, CertificadoPolicy.php, UserPolicy.php
- **Verificación:** ✓ 141 tests verdes / ✓ Laravel auto-discovers Policies desde app/Policies / ✓ `$this->authorize()` disponible en controllers

---

## [TASK-008] Form Requests para SeccionCurso, Reordenar y Reportes

- **Archivo(s):** `app/Http/Requests/Capacitador/StoreSeccionCursoRequest.php`, `UpdateSeccionCursoRequest.php`, `ReorderSeccionesRequest.php` (nuevos), `app/Http/Requests/ReportFilterRequest.php` (nuevo), `app/Http/Controllers/Capacitador/SeccionCursoController.php`, `app/Http/Controllers/ReporteController.php`
- **Problema resuelto:** CS-011 — validación inline repetida; sin reutilización de reglas de validación
- **Cambio realizado:** Creadas 5 Form Requests. SeccionCursoController cambiado a type-hint StoreSeccionCursoRequest, UpdateSeccionCursoRequest, ReorderSeccionesRequest. ReporteController cambiado a type-hint ReportFilterRequest en index() y exportar(). ReportFilterRequest valida arrays de IDs con validación de existencia. sanitizeIdFilter() simplificada.
- **Archivos nuevos creados:** 5 Form Requests
- **Verificación:** ✓ 141 tests verdes / ✓ Validación centralizada / ✓ Reuso en index() y exportar()

---

## [TASK-013] Números mágicos a constantes

- **Archivo(s):** `app/Models/Curso.php`, `app/Livewire/Capacitador/CalendarioCapacitaciones.php`, `app/Services/CertificadoService.php`
- **Problema resuelto:** CS-017, CS-018, CS-019, CS-020 — literales para tamaños, umbrales y límites inline
- **Cambio realizado:** Agregadas constantes en Curso: SAMPLE_SIZE=20, HUE_BUCKETS=36, LIGHTNESS_*_THRESHOLD, SATURATION_*, FALLBACK_COLOR. CalendarioCapacitaciones: CALENDAR_MIN/MAX_YEAR. CertificadoService: QR_CODE_SIZE=240, QR_CODE_MARGIN=12. Reemplazados todos los literales.
- **Archivos nuevos creados:** ninguno
- **Verificación:** ✓ 141 tests verdes / ✓ Números mágicos eliminados / ✓ Constantes con nombres descriptivos

---

## Sprint 3 — Configuración y Deuda Técnica Menor

---

## [TASK-012] Configuración de producción explícita

- **Archivo(s):** `.env.example`, `config/livewire.php`
- **Problema resuelto:** SEC-005 — sesión segura no declarada; uploads Livewire sin throttle/reglas
- **Cambio realizado:** `.env.example` documenta `SESSION_SECURE_COOKIE`, `SESSION_HTTP_ONLY`, `SESSION_SAME_SITE`. `config/livewire.php` establece `rules: ['required', 'file', 'max:12288']` y `middleware: 'throttle:60,1'` en `temporary_file_upload`.
- **Archivos nuevos creados:** ninguno
- **Verificación:** ✓ Valores explícitos, no dependen de defaults silenciosos

---

## Sprint 1 — Refactoring de Alta Prioridad

---

## [TASK-008] CS-015 — DuplicateCourseAction eager loading

- **Archivo(s):** `app/Actions/Cursos/DuplicateCourseAction.php`
- **Problema resuelto:** CS-015 — N+1 silencioso al duplicar curso grande
- **Cambio realizado:** Agregado `$cursoOriginal->loadMissing('modulos.evaluacion.preguntas.opciones')` antes de la transacción. También eliminados comentarios que narraban pasos obvios.
- **Archivos nuevos creados:** ninguno
- **Verificación:** ✓ Suite completa verde / ✓ Sin queries N+1 en duplicación

---

## [TASK-009] CS-012 — SeccionCursoController@reordenar en transacción

- **Archivo(s):** `app/Http/Controllers/Capacitador/SeccionCursoController.php`
- **Problema resuelto:** CS-012 — múltiples writes sin transacción en reordenamiento
- **Cambio realizado:** Foreachs de reordenamiento envueltos en `DB::transaction()`. Import `DB` agregado.
- **Archivos nuevos creados:** ninguno
- **Verificación:** ✓ 139 tests verdes

---

## [TASK-010] CS-013 — ModuloController@destroy en transacción

- **Archivo(s):** `app/Http/Controllers/Capacitador/ModuloController.php`
- **Problema resuelto:** CS-013 — delete + reordenamiento sin transacción; archivo borrado antes de commit DB
- **Cambio realizado:** DB delete + reorder envuelto en `DB::transaction()`. Archivo de storage se borra después del commit.
- **Archivos nuevos creados:** ninguno
- **Verificación:** ✓ 139 tests verdes

---

## [TASK-011] CS-032 — CursoController@destroy archivos después del DB delete

- **Archivo(s):** `app/Http/Controllers/Capacitador/CursoController.php`
- **Problema resuelto:** CS-032 — archivos borrados antes de que el curso sea eliminado de DB
- **Cambio realizado:** Rutas de archivos recolectadas antes del delete, `$curso->delete()` ejecutado primero, archivos de storage borrados después.
- **Archivos nuevos creados:** ninguno
- **Verificación:** ✓ 139 tests verdes

---

## [TASK-012b] CS-029 — VerEvaluacion@finalizar intento + respuestas en transacción

- **Archivo(s):** `app/Livewire/VerEvaluacion.php`
- **Problema resuelto:** CS-029 — intento y respuestas creados sin transacción
- **Cambio realizado:** `IntentoEvaluacion::create()` + foreach de `RespuestaEvaluacion::create()` envuelto en `DB::transaction()`.
- **Archivos nuevos creados:** ninguno
- **Verificación:** ✓ 139 tests verdes

---

## [TASK-013] CS-030 — CertificadoService@generarParaUsuario en transacción

- **Archivo(s):** `app/Services/CertificadoService.php`
- **Problema resuelto:** CS-030 — `Certificado::create()` sin transacción
- **Cambio realizado:** `Certificado::create()` envuelto en `DB::transaction()`. Notificación enviada fuera (notificar solo después de commit).
- **Archivos nuevos creados:** ninguno
- **Verificación:** ✓ 139 tests verdes

---

## Sprint 0 — Correcciones Críticas

---

## [TASK-001] Actualizar phpoffice/phpspreadsheet vulnerable

- **Archivo(s):** `composer.json`, `composer.lock`
- **Problema resuelto:** SEC-001 — `phpoffice/phpspreadsheet 1.30.0` con 5 advisories (CVE-2026-40902, CVE-2026-40863, CVE-2026-34084, CVE-2026-40296, CVE-2026-35453)
- **Cambio realizado:** `composer update phpoffice/phpspreadsheet maatwebsite/excel --with-all-dependencies`. Versión actualizada de 1.30.0 → 1.30.4. `composer audit` limpio.
- **Archivos nuevos creados:** ninguno
- **Verificación:** ✓ 6 tests de ReportsTest verdes / ✓ `composer audit` sin advisories

---

## [TASK-002] Eliminar asignación automática de roles en login

- **Archivo(s):** `app/Http/Controllers/AuthController.php` (modificado), `app/Console/Commands/RepairSystemRoles.php` (nuevo)
- **Problema resuelto:** CS-001 / SEC-002 — failsafe de roles por email durante autenticación
- **Cambio realizado:** Eliminado bloque de 5 líneas que asignaba roles `Desarrollador`/`Administrador` por email hardcodeado en `AuthController@login`. Creado comando idempotente `alumco:repair-system-roles` para reparar roles en deploy controlado.
- **Archivos nuevos creados:** `app/Console/Commands/RepairSystemRoles.php`
- **Verificación:** ✓ 22 tests de acceso/jerarquía verdes / ✓ Login ya no modifica roles

---

## [TASK-003] Corregir N+1 en listado y exportación de participantes

- **Archivo(s):** `app/Http/Controllers/Capacitador/ParticipanteController.php`
- **Problema resuelto:** CS-002 / CS-003 — 2N queries individuales por usuario para progreso y certificado
- **Cambio realizado:** Extraído método privado `resolverParticipantes()` que precalcula progresos agrupados (`selectRaw + groupBy + pluck`) y certificados (`whereIn + keyBy`). Ambos `index()` y `exportar()` usan el método compartido. Queries O(1) en lugar de O(2N).
- **Archivos nuevos creados:** ninguno
- **Verificación:** ✓ Lógica de negocio preservada / ✓ Sintaxis PHP válida / ✓ Tests verdes

---

## [TASK-004] EditarEvaluacion: eliminarPregunta sin verificar ownership

- **Archivo(s):** `app/Livewire/Capacitador/EditarEvaluacion.php`
- **Problema resuelto:** CS-026 / SEC-004 — `Pregunta::destroy($id)` sin verificar pertenencia a evaluación
- **Cambio realizado:** Reemplazado `Pregunta::destroy($preguntaId)` por query scoped con `whereKey + where('evaluacion_id', ...)`. Pregunta ajena no se borra.
- **Archivos nuevos creados:** ninguno
- **Verificación:** ✓ Test `test_eliminar_pregunta_no_puede_borrar_pregunta_ajena` verde

---

## [TASK-005] EditarEvaluacion: eliminarOpcion sin verificar ownership

- **Archivo(s):** `app/Livewire/Capacitador/EditarEvaluacion.php`
- **Problema resuelto:** CS-027 / SEC-004 — `Opcion::destroy($id)` sin verificar pertenencia
- **Cambio realizado:** Reemplazado `Opcion::destroy($opcionId)` por query scoped con `whereKey + whereHas('pregunta', evaluacion_id)`. También agregado `abort_unless` en `agregarOpcion` para el mismo vector (no catalogado).
- **Archivos nuevos creados:** ninguno
- **Verificación:** ✓ Test `test_eliminar_opcion_no_puede_borrar_opcion_ajena` verde / ✓ Test `test_agregar_opcion_rechaza_pregunta_ajena` verde

---

## [TASK-006] Doble sanitización XSS en render de contenido de módulos

- **Archivo(s):** `resources/views/modulos/capsula.blade.php`, `tests/Feature/ModuloContentSanitizationTest.php` (nuevo)
- **Problema resuelto:** CS-016 / SEC-006 — `{!! $modulo->contenido !!}` sin defensa en profundidad; datos legacy sin Purifier podían escapar
- **Cambio realizado:** `{!! $modulo->contenido !!}` → `{!! clean($modulo->contenido) !!}`. Creado test XSS que verifica sanitización en store, update y render de la vista worker.
- **Archivos nuevos creados:** `tests/Feature/ModuloContentSanitizationTest.php`
- **Verificación:** ✓ 3 tests XSS verdes / ✓ `clean()` es idempotente para contenido ya limpio

---

## [TASK-007] CI mínimo con GitHub Actions

- **Archivo(s):** `.github/workflows/ci.yml` (nuevo)
- **Problema resuelto:** CS-023 — sin pipeline para bloquear regresiones
- **Cambio realizado:** Workflow con matrix PHP 8.5, servicio MySQL 8.0, pasos: `composer install`, `composer audit`, `pint --test`, `migrate`, `artisan test --compact`.
- **Archivos nuevos creados:** `.github/workflows/ci.yml`
- **Verificación:** ✓ Sintaxis YAML válida / ✓ Cubre todos los gates definidos en REFACTORING_PLAN.md
