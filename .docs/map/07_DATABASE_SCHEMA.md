---
updated: 2026-05-04
covers: "migraciones tablas indices llaves"
when_to_use: "cuando necesites tocar base de datos"
---

# Database Schema

## Tablas principales

### `users`

- Columnas: `id`, `name`, `email` unique, `rut` unique nullable, `email_verified_at`, `password`, `fecha_nacimiento`, `sexo` enum(`F`,`M`,`Otro`), `activo` bool default true, `accessibility_preferences` json nullable, `firma_digital` nullable, `sede_id` FK nullable, `estamento_id` FK nullable, `remember_token`, timestamps, `deleted_at`
- Indices/FKs:
  - FK `sede_id -> sedes.id` `nullOnDelete`
  - FK `estamento_id -> estamentos.id` `nullOnDelete`
- Migraciones clave:
  - `0001_01_01_000003_create_users_table.php`
  - `2026_04_28_060748_add_rut_to_users_table.php`
  - `2026_04_26_063057_add_firma_digital_to_users_table.php`
  - `2026_05_02_120000_add_accessibility_preferences_to_users_table.php`

### `sedes`

- Columnas: `id`, `nombre` unique, timestamps, `deleted_at`
- Uso: sedes de usuario y planificacion

### `estamentos`

- Columnas: `id`, `nombre` unique, timestamps, `deleted_at`
- Uso: segmentacion de acceso y reportes

### `cursos`

- Columnas: `id`, `titulo`, `descripcion` nullable, `imagen_portada` nullable, `color_promedio` nullable(7), `capacitador_id` FK, `curso_original_id` FK nullable, timestamps
- Indices/FKs:
  - FK `capacitador_id -> users.id` `cascadeOnDelete`
  - FK `curso_original_id -> cursos.id` `nullOnDelete`
- Notas:
  - Antes tuvo `fecha_inicio/fecha_fin` y `es_secuencial`; hoy la disponibilidad vive en `planificaciones_cursos`.

### `curso_estamento`

- Pivot many-to-many entre cursos y estamentos
- Columnas: `id`, `curso_id`, `estamento_id`
- Indices/FKs:
  - `curso_id -> cursos.id` `cascadeOnDelete`
  - `estamento_id -> estamentos.id` `cascadeOnDelete`

### `planificaciones_cursos`

- Columnas: `id`, `curso_id`, `sede_id` nullable, `fecha_inicio` date, `fecha_fin` date, `notas` nullable, timestamps
- Indices/FKs:
  - Index compuesto `curso_id, fecha_inicio, fecha_fin`
  - FK `curso_id -> cursos.id` `cascadeOnDelete`
  - FK `sede_id -> sedes.id` `nullOnDelete`

### `seccion_cursos`

- Columnas: `id`, `curso_id`, `titulo`, `orden`, timestamps
- FK `curso_id -> cursos.id` `cascadeOnDelete`

### `modulos`

- Columnas: `id`, `curso_id`, `seccion_id` nullable, `titulo`, `orden`, `tipo_contenido` enum(`video`,`pdf`,`ppt`,`texto`,`imagen`,`evaluacion`), `ruta_archivo` nullable, `nombre_archivo_original` nullable, `contenido` nullable longtext, `duracion_minutos` nullable unsigned smallint, timestamps
- FKs:
  - `curso_id -> cursos.id` `cascadeOnDelete`
  - `seccion_id -> seccion_cursos.id` `nullOnDelete`

### `evaluaciones`

- Columnas: `id`, `modulo_id`, timestamps
- FK `modulo_id -> modulos.id` `cascadeOnDelete`
- Historial: se eliminaron columnas `puntos_aprobacion` y `max_intentos_semanales` y su valor vive en `global_settings`

### `preguntas`

- Columnas: `id`, `evaluacion_id`, `enunciado`, `orden`, timestamps
- FK `evaluacion_id -> evaluaciones.id` `cascadeOnDelete`

### `opciones`

- Columnas: `id`, `pregunta_id`, `texto`, `es_correcta` bool default false, `orden`, timestamps
- FK `pregunta_id -> preguntas.id` `cascadeOnDelete`

### `intentos_evaluacion`

- Columnas: `id`, `user_id`, `evaluacion_id`, `puntaje` smallint unsigned, `total_preguntas` smallint unsigned, `aprobado` bool default false, timestamps
- FKs:
  - `user_id -> users.id` `cascadeOnDelete`
  - `evaluacion_id -> evaluaciones.id` `cascadeOnDelete`

### `respuestas_evaluacion`

- Columnas: `id`, `intento_id`, `pregunta_id`, `opcion_id`
- FKs:
  - `intento_id -> intentos_evaluacion.id` `cascadeOnDelete`
  - `pregunta_id -> preguntas.id` `cascadeOnDelete`
  - `opcion_id -> opciones.id` `cascadeOnDelete`

### `progresos_modulo`

- Columnas: `id`, `user_id`, `modulo_id`, `completado` bool default false, `fecha_completado` nullable, timestamps
- Indices/FKs:
  - Unique `(user_id, modulo_id)`
  - `user_id -> users.id` `cascadeOnDelete`
  - `modulo_id -> modulos.id` `cascadeOnDelete`

### `certificados`

- Columnas: `id`, `user_id`, `curso_id`, `codigo_verificacion` unique, `ruta_pdf`, `fecha_emision` nullable, timestamps
- FKs:
  - `user_id -> users.id` `cascadeOnDelete`
  - `curso_id -> cursos.id` `cascadeOnDelete`

### `global_settings`

- Columnas: `id`, `key` unique, `value`, `description` nullable, timestamps
- Datos iniciales:
  - `evaluacion_puntos_aprobacion = 70`
  - `evaluacion_max_intentos_semanales = 3`

### `reporte_presets`

- Columnas: `id`, `nombre`, `columnas` json, timestamps

### `notification_deliveries`

- Columnas: `id`, `user_id`, `curso_id`, `planificacion_curso_id` nullable, `certificado_id` nullable, `type` varchar(80), `dedupe_key` unique, `sent_at`, timestamps
- Indices:
  - `(user_id, curso_id, type)`
  - `(type, sent_at)`
- FKs:
  - `user_id -> users.id` `cascadeOnDelete`
  - `curso_id -> cursos.id` `cascadeOnDelete`
  - `planificacion_curso_id -> planificaciones_cursos.id` `cascadeOnDelete`
  - `certificado_id -> certificados.id` `cascadeOnDelete`

### `evento_calendarios`

- Columnas: `id`, `titulo`, `fecha`, `hora` nullable, `descripcion` nullable, timestamps

## Infra tables

- `cache`, `cache_locks`
- `jobs`, `job_batches`, `failed_jobs`
- `sessions`
- Spatie permissions: `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`

## ERD simplificado

```text
users 1---* certificados
users 1---* progresos_modulo
users 1---* cursos (capacitador_id)
users *---* roles (Spatie)

cursos 1---* modulos 1---1 evaluaciones 1---* preguntas 1---* opciones
cursos 1---* planificaciones_cursos
cursos *---* estamentos
cursos 1---* seccion_cursos
cursos 1---* certificados

modulos 1---* progresos_modulo
intentos_evaluacion 1---* respuestas_evaluacion
notification_deliveries *---1 users/cursos/planificaciones_cursos/certificados
```

