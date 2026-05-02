# Arquitectura del Sistema: Alumco LMS

El sistema está diseñado en base a **Laravel 13** y sigue el patrón Modelo-Vista-Controlador (MVC), integrando **Livewire 4** para componentes interactivos dinámicos sin necesidad de recargar la página o requerir de Javascript pesado.

## Entidades Principales

El dominio de negocio se centra en la gestión y distribución de contenidos de capacitación para los trabajadores de diversas Sedes y Estamentos de la ONG.

### Estructura Organizacional
- **Sede (`Sede`)**: Representa la ubicación física o sucursal.
- **Estamento (`Estamento`)**: Representa la división, departamento o rol jerárquico. Ej: Docentes, Administrativos, Equipo Directivo.
- **Usuario (`User`)**: Cada cuenta pertenece a un Estamento y una Sede. Los usuarios tienen roles de acceso mediante Spatie Permission. Almacenan información de su firma digital y datos demográficos.

### Contenido de Capacitación
- **Curso (`Curso`)**: La unidad de aprendizaje más grande. Está asignado a múltiples Estamentos; solo quienes pertenezcan a los mismos pueden visualizarlo.
- **Sección de Curso (`SeccionCurso`)**: Agrupa módulos dentro de un curso para mejor organización jerárquica. Cada sección tiene un título y orden.
- **Módulo (`Modulo`)**: Cápsula de contenido dentro de una sección (o curso si no hay secciones). Los módulos se dividen en los siguientes tipos:
  - `video`: MP4 directo o YouTube.
  - `pdf`: Visor en pantalla o descarga.
  - `imagen`: Visor gráfico.
  - `texto`: Contenido enriquecido WYSIWYG.
  - `ppt`: Presentaciones PowerPoint.
  - `evaluacion`: Tests o cuestionarios dinámicos.

### Cuestionarios y Evaluaciones
La estructura relacional que maneja la evaluación se compone de:
- **Evaluación (`Evaluacion`)**: Asociada a un Módulo de tipo "evaluacion". Contiene preguntas y está sujeta a parámetros globales.
- **Configuración Global (`GlobalSetting`)**: Define los parámetros transversales para todas las evaluaciones del sistema, como el puntaje mínimo de aprobación y el límite de intentos semanales. Valores almacenados como key-value con descripción.
- **Pregunta (`Pregunta`)** y **Opción (`Opcion`)**: Desglose interno del cuestionario, donde una Opción se marca como "correcta" (campo `es_correcta`).
- **Intentos (`IntentoEvaluacion`)** y **Respuestas (`RespuestaEvaluacion`)**: Registro transaccional del progreso del usuario, controlando el contador de intentos y los bloqueos temporales. Almacena puntaje, total de preguntas y si fue aprobado.

### Planificación y Progreso
- **Planificación de Cursos (`PlanificacionCurso`)**: Fechas de vigencia de un curso de acuerdo a una Sede específica. Define fechas inicio/fin para controlar disponibilidad.
- **Progreso (`ProgresoModulo`)**: Tabla pivot que registra qué usuario completó qué módulo, fecha de completado y estado. Controla el desbloqueo secuencial.
- **Certificados (`Certificado`)**: Emitido automáticamente tras completar el 100% de los módulos en un Curso. Es un PDF estático generado vía `dompdf` y almacenado en `storage/app/public`. Incluye código de verificación UNIQUE (UUIDv4 truncado).

### Características Adicionales
- **Eventos de Calendario (`EventoCalendario`)**: Tabla para almacenar eventos (calendario de capacitaciones). Actualmente es una tabla stub.
- **Presets de Reportes (`ReportePreset`)**: Guarda configuraciones de columnas (JSON) para exportación de reportes a Excel. Permite reutilizar filtros y vistas.

## Stack y Decisiones Tecnológicas

1. **Tailwind CSS 4**: Utilizado para toda la interfaz gráfica. Los tokens y utilidades se procesan mediante Vite.
2. **Livewire 4**: Núcleo del motor de evaluación (`livewire/ver-evaluacion`), controlando la persistencia de selecciones y comprobación final en el servidor.
3. **Storage Simbólico**: Manipulación de portadas, archivos descargables y certificados mediante `Storage::disk('public')`.
4. **Redis y Colas**: Integrado para manejo de cache y procesamiento en segundo plano.
5. **Mailpit**: Entorno para depurar notificaciones por correo de manera local.
