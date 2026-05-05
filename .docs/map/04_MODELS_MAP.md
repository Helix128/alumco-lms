---
updated: 2026-05-04
covers: "modelos relaciones scopes casts"
when_to_use: "cuando necesites consultar dominio o Eloquent"
---

# Models Map

## `User`

- Tabla: `users`
- Fillable: `name`, `email`, `rut`, `password`, `fecha_nacimiento`, `sexo`, `activo`, `accessibility_preferences`, `firma_digital`, `sede_id`, `estamento_id`
- Guarded: no definido
- Casts: `email_verified_at: datetime`, `password: hashed`
- Accessor/mutator: `accessibility_preferences` normaliza JSON a `{fontLevel, highContrast, reducedMotion}`
- Relaciones:
  - `sede() -> belongsTo(Sede::class)`
  - `estamento() -> belongsTo(Estamento::class)`
  - `cursosImpartidos() -> hasMany(Curso::class, capacitador_id)`
  - `certificados() -> hasMany(Certificado::class)`
  - `progresos() -> hasMany(ProgresoModulo::class)`
- Helpers:
  - `isDesarrollador()`, `isAdmin()`, `hasAdminAccess()`
  - `isCapacitadorInterno()`, `isCapacitadorExterno()`, `isCapacitador()`, `isTrabajador()`
  - `getHierarchyRank()`, `canManageUser(User $targetUser)`
- Uso comun: auth, area routing y gestion de usuarios.

## `Curso`

- Tabla: `cursos`
- Fillable: `titulo`, `descripcion`, `imagen_portada`, `color_promedio`, `capacitador_id`, `curso_original_id`
- Casts: ninguno definido
- Relaciones:
  - `capacitador() -> belongsTo(User::class, capacitador_id)`
  - `cursoOriginal() -> belongsTo(Curso::class, curso_original_id)`
  - `versionesDerivadas() -> hasMany(Curso::class, curso_original_id)`
  - `estamentos() -> belongsToMany(Estamento::class)`
  - `modulos() -> hasMany(Modulo::class)->orderBy('orden')`
  - `secciones() -> hasMany(SeccionCurso::class, curso_id)->orderBy('orden')`
  - `planificaciones() -> hasMany(PlanificacionCurso::class)`
- Scopes/helpers: `booted()` auto extrae `color_promedio` si cambia portada
- Helpers: `extraerColorPromedio()`, `estaDisponible()`, `estaDisponiblePara(User $user)`, `progresoParaUsuario(User $user)`
- Uso comun: listado, detalle, duplicacion y calendario.

## `Modulo`

- Tabla: `modulos`
- Fillable: `curso_id`, `seccion_id`, `titulo`, `orden`, `tipo_contenido`, `ruta_archivo`, `nombre_archivo_original`, `contenido`, `duracion_minutos`
- Constantes: `TIPOS`, `TIPO_LABELS`
- Relaciones:
  - `curso() -> belongsTo(Curso::class)`
  - `seccion() -> belongsTo(SeccionCurso::class, seccion_id)`
  - `evaluacion() -> hasOne(Evaluacion::class)`
  - `progresos() -> hasMany(ProgresoModulo::class)`
- Helpers: `estaCompletadoPor(User $user)`, `estaAccesiblePara(User $user, Curso $curso)`
- Uso comun: contenido de cursos y gating secuencial.

## `SeccionCurso`

- Tabla: `seccion_cursos`
- Fillable: `curso_id`, `titulo`, `orden`
- Relaciones:
  - `curso() -> belongsTo(Curso::class)`
  - `modulos() -> hasMany(Modulo::class, seccion_id)->orderBy('orden')`
- Uso comun: agrupar modulos dentro del curso.

## `PlanificacionCurso`

- Tabla: `planificaciones_cursos`
- Fillable: `curso_id`, `sede_id`, `fecha_inicio`, `fecha_fin`, `notas`
- Casts: `fecha_inicio: date`, `fecha_fin: date`
- Relaciones:
  - `curso() -> belongsTo(Curso::class)`
  - `sede() -> belongsTo(Sede::class)`
- Helper: `estaActivo()`
- Uso comun: disponibilidad por sede y calendario.

## `Estamento`

- Tabla: `estamentos`
- Fillable: `nombre`
- Soft deletes: si
- Relaciones:
  - `users() -> hasMany(User::class)`
  - `cursos() -> belongsToMany(Curso::class)`
- Uso comun: segmentacion de usuarios y permisos de cursos.

## `Sede`

- Tabla: `sedes`
- Fillable: `nombre`
- Soft deletes: si
- Relaciones:
  - `users() -> hasMany(User::class)`
  - `planificaciones() -> hasMany(PlanificacionCurso::class)`
- Uso comun: filtro de reportes, usuarios y planificacion.

## `Evaluacion`

- Tabla: `evaluaciones`
- Fillable: `modulo_id`
- Relaciones:
  - `modulo() -> belongsTo(Modulo::class)`
  - `preguntas() -> hasMany(Pregunta::class)->orderBy('orden')`
  - `intentos() -> hasMany(IntentoEvaluacion::class)`
- Accessors:
  - `puntosAprobacion` lee `GlobalSetting::get('evaluacion_puntos_aprobacion', 70)`
  - `maxIntentosSemanales` lee `GlobalSetting::get('evaluacion_max_intentos_semanales', 3)`
- Uso comun: evaluaciones y control de intentos.

## `Pregunta`

- Tabla: `preguntas`
- Fillable: `evaluacion_id`, `enunciado`, `orden`
- Relaciones:
  - `evaluacion() -> belongsTo(Evaluacion::class)`
  - `opciones() -> hasMany(Opcion::class)->orderBy('orden')`
- Uso comun: banco de preguntas de evaluacion.

## `Opcion`

- Tabla: `opciones`
- Fillable: `pregunta_id`, `texto`, `es_correcta`, `orden`
- Casts: `es_correcta: boolean`
- Relaciones:
  - `pregunta() -> belongsTo(Pregunta::class)`
- Uso comun: alternativas de preguntas.

## `IntentoEvaluacion`

- Tabla: `intentos_evaluacion`
- Fillable: `user_id`, `evaluacion_id`, `puntaje`, `total_preguntas`, `aprobado`
- Casts: `aprobado: boolean`
- Relaciones:
  - `user() -> belongsTo(User::class)`
  - `evaluacion() -> belongsTo(Evaluacion::class)`
  - `respuestas() -> hasMany(RespuestaEvaluacion::class, intento_id)`
- Uso comun: registrar intentos y aprobacion.

## `RespuestaEvaluacion`

- Tabla: `respuestas_evaluacion`
- Timestamps: no
- Fillable: `intento_id`, `pregunta_id`, `opcion_id`
- Relaciones:
  - `intento() -> belongsTo(IntentoEvaluacion::class, intento_id)`
  - `pregunta() -> belongsTo(Pregunta::class)`
  - `opcion() -> belongsTo(Opcion::class)`
- Uso comun: almacenar respuestas marcadas.

## `Certificado`

- Tabla: `certificados`
- Fillable: `user_id`, `curso_id`, `codigo_verificacion`, `ruta_pdf`, `fecha_emision`
- Casts: `fecha_emision: datetime`
- Relaciones:
  - `user() -> belongsTo(User::class)`
  - `curso() -> belongsTo(Curso::class)`
- Uso comun: emision y descarga de certificados.

## `NotificationDelivery`

- Tabla: `notification_deliveries`
- Fillable: `user_id`, `curso_id`, `planificacion_curso_id`, `certificado_id`, `type`, `dedupe_key`, `sent_at`
- Casts: `sent_at: datetime`
- Relaciones:
  - `user()`, `curso()`, `planificacionCurso()`, `certificado()` via belongsTo
- Helpers:
  - `recordOnce()`
  - `certificateCompletedKey()`, `courseAvailableKey()`, `deadlineReminderKey()`
- Uso comun: deduplicacion de notificaciones por mail.

## `GlobalSetting`

- Tabla: `global_settings`
- Fillable: `key`, `value`, `description`
- Casts: no definidos
- Helpers:
  - `get(string $key, $default = null)`
  - `set(string $key, $value)`
- Uso comun: configuracion global cacheada.

## `ReportePreset`

- Tabla: `reporte_presets`
- Fillable: `nombre`, `columnas`
- Casts: `columnas: array`
- Uso comun: formatos de exportacion guardados.

## `EventoCalendario`

- Tabla: `evento_calendarios`
- Fillable/casts/relaciones: no definidos
- Uso comun: calendario institucional. Actualmente sin logica adicional.

## `ProgresoModulo`

- Tabla: `progresos_modulo`
- Fillable: `user_id`, `modulo_id`, `completado`, `fecha_completado`
- Relaciones:
  - `user() -> belongsTo(User::class)`
  - `modulo() -> belongsTo(Modulo::class)`
- Uso comun: progreso por modulo.

