# Catálogo de Code Smells

| ID | Tipo | Archivo | Línea aprox. | Descripción | Severidad | Sprint |
|----|------|---------|-------------|-------------|-----------|--------|
| CS-001 | Hardcoded Secret | `app/Http/Controllers/AuthController.php` | 38-43 | Emails privilegiados asignan roles durante login. | 🔴 | S0 |
| CS-002 | N+1 Query | `app/Http/Controllers/Capacitador/ParticipanteController.php` | 41-51 | Query de progreso y certificado por cada usuario en listado. | 🟠 | S0 |
| CS-003 | N+1 Query | `app/Http/Controllers/Capacitador/ParticipanteController.php` | 82-92 | Misma N+1 en exportación Excel. | 🟠 | S0 |
| CS-004 | God Class | `app/Livewire/Capacitador/CalendarioCapacitaciones.php` | 1-1596 | Componente concentra UI, CRUD, autorización, caché, calendario y copia anual. | 🟠 | S2 |
| CS-005 | Fat Model | `app/Models/Curso.php` | 22-186 | Modelo procesa imagen con GD y reglas de color. | 🟠 | S1 |
| CS-006 | Long Method | `app/Http/Controllers/CursoController.php` | 11-73 | `index()` clasifica cursos, calcula progreso y prepara vista. | 🟡 | S1 |
| CS-007 | Long Method | `app/Http/Controllers/CursoController.php` | 76-142 | `show()` valida disponibilidad/acceso y carga estructura. | 🟡 | S1 |
| CS-008 | Long Method | `app/Http/Controllers/ReporteController.php` | 52-142 | `index()` sanitiza filtros, calcula edades, arma query y vista. | 🟠 | S1 |
| CS-009 | Duplicate Logic | `app/Http/Controllers/ReporteController.php` | 16-50 | Tres métodos `sanitize*Ids()` casi idénticos. | 🟡 | S1 |
| CS-010 | Duplicate Logic | `app/Http/Controllers/Capacitador/*Controller.php` | varios | `authorizeCurso()` repetido en múltiples controladores. | 🟠 | S1 |
| CS-011 | Missing Validation | `app/Http/Controllers/Capacitador/SeccionCursoController.php` | 67-72 | `reordenar()` valida forma parcial, no `exists`/pertenencia explícita de ids. | 🟡 | S1 |
| CS-012 | Missing Transaction | `app/Http/Controllers/Capacitador/SeccionCursoController.php` | 75-106 | Reordenamiento multi-update sin transacción. | 🟡 | S1 |
| CS-013 | Missing Transaction | `app/Http/Controllers/Capacitador/ModuloController.php` | 146-155 | Delete + reordenamiento sin transacción. | 🟡 | S1 |
| CS-014 | SOLID Violation | `app/Services/CertificadoService.php` | 24-179 | Servicio mezcla elegibilidad, PDF, QR, storage y notificación. | 🟠 | S2 |
| CS-015 | N+1 Query | `app/Actions/Cursos/DuplicateCourseAction.php` | 24-44 | Recorre relaciones sin `loadMissing('modulos.evaluacion.preguntas.opciones')`. | 🟡 | S1 |
| CS-016 | Missing Test | `resources/views/modulos/capsula.blade.php` | 114 | Render `{!! $modulo->contenido !!}` no tiene test XSS dedicado. | 🟡 | S0 |
| CS-017 | Magic Number | `app/Models/Curso.php` | 62-64 | Muestra 20x20 hardcodeada para imagen. | 🔵 | S3 |
| CS-018 | Magic Number | `app/Models/Curso.php` | 79-81 | Umbrales HSL y 36 buckets inline. | 🔵 | S3 |
| CS-019 | Magic Number | `app/Services/CertificadoService.php` | 122-123 | QR size/margin inline. | 🔵 | S3 |
| CS-020 | Magic Number | `app/Livewire/Capacitador/CalendarioCapacitaciones.php` | 1417-1418 | Rango de años `2020-2099` inline. | 🔵 | S3 |
| CS-021 | Dead Code | `tests/Feature/ExampleTest.php` | 5 | Comentario de `RefreshDatabase` heredado. | 🔵 | S3 |
| CS-022 | Raw Query | `database/migrations/2026_04_07_100000_alter_modulos_for_all_content_types.php` | 13-15 | `DB::statement()` para enum MySQL. No hay input de usuario. | 🔵 | S3 |
| CS-023 | Missing Test | `.github/workflows/` | n/a | No hay CI para tests/audit/format. | 🟡 | S0 |
| CS-024 | SOLID Violation | `app/Models/User.php` | 78-147 | Modelo contiene jerarquía y reglas de gestión de usuarios. | 🟡 | S2 |
| CS-025 | Primitive Obsession | `app/Livewire/Admin/UserManagement.php` | 95-99 | Ranks de roles hardcodeados con enteros. | 🟡 | S1 |
| CS-026 | Missing Validation | `app/Livewire/Capacitador/EditarEvaluacion.php` | 98-100 | `eliminarPregunta()` destruye por id sin verificar pertenencia a evaluación. | 🟠 | S0 |
| CS-027 | Missing Validation | `app/Livewire/Capacitador/EditarEvaluacion.php` | 143-145 | `eliminarOpcion()` destruye por id sin verificar pertenencia a evaluación. | 🟠 | S0 |
| CS-028 | Missing Validation | `app/Livewire/Capacitador/EditarEvaluacion.php` | 154-162 | `toggleCorrecta()` actualiza opciones por ids del estado local, sin transacción. | 🟡 | S1 |
| CS-029 | Missing Transaction | `app/Livewire/VerEvaluacion.php` | 475-489 | Crea intento y respuestas sin transacción. | 🟡 | S1 |
| CS-030 | Missing Transaction | `app/Services/CertificadoService.php` | 60-68 | Crea certificado y registra notificación sin transacción/afterCommit. | 🟡 | S1 |
| CS-031 | Fat Controller | `app/Http/Controllers/Capacitador/CursoController.php` | 71-90 | `show()` sana datos creando evaluaciones faltantes durante lectura. | 🟠 | S1 |
| CS-032 | Missing Transaction | `app/Http/Controllers/Capacitador/CursoController.php` | 136-148 | Borra archivos y curso sin transacción ni job de limpieza. | 🟡 | S1 |
| CS-033 | Missing Validation | `app/Livewire/Capacitador/CalendarioCapacitaciones.php` | 321-327 | Creación rápida anual no valida `cursoId`/`sedeId` con reglas Livewire. | 🟡 | S1 |
| CS-034 | Missing Validation | `app/Livewire/Capacitador/CalendarioCapacitaciones.php` | 353-359 | Creación desde sidebar no valida curso/sede con `exists`. | 🟡 | S1 |
| CS-035 | Feature Envy | `app/Http/Controllers/CursoController.php` | 32-69 | Controller conoce reglas de vigencia/progreso que deberían vivir en dominio/query object. | 🟡 | S1 |
| CS-036 | Missing Index | `database/migrations/0001_01_01_000003_create_users_table.php` | 34-35 | FKs indexadas por Laravel; falta índice compuesto opcional `(estamento_id, sede_id)` para filtros de reportes. | 🔵 | S3 |
| CS-037 | Missing Index | `database/migrations/2026_04_07_100002_create_evaluation_tables.php` | 41-42 | Falta índice compuesto opcional `(user_id, evaluacion_id, created_at)` para gate semanal. | 🟡 | S2 |
| CS-038 | SQL Injection Risk | `app/Livewire/Capacitador/EstadisticasDashboard.php` | 64-67 | `selectRaw()` usa SQL estático sin input; riesgo bajo, revisar si se parametriza en el futuro. | 🔵 | S3 |
| CS-039 | Missing Test | `composer.lock` | 3650-3651 | `composer audit` no está automatizado en CI. | 🟡 | S0 |
| CS-040 | Duplicate Logic | `app/Livewire/Capacitador/CalendarioCapacitaciones.php` | 129-190 | Navegación mensual/anual repetitiva; candidata a helpers pequeños. | 🔵 | S3 |
