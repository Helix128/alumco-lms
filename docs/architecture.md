# Arquitectura del Sistema: Alumco LMS

El sistema está diseñado sobre **Laravel 13** y sigue el patrón Modelo-Vista-Controlador (MVC), integrando **Livewire 4** para componentes interactivos sin recargar la página.

## Entidades principales

El dominio de negocio se centra en la gestión y distribución de contenidos de capacitación para colaboradores y colaboradoras de distintas sedes y estamentos de la ONG.

### Estructura organizacional

- **Sede (`Sede`)**: ubicación física o sucursal.
- **Estamento (`Estamento`)**: división, departamento o rol organizacional.
- **Usuario (`User`)**: cuenta de acceso asociada a un estamento y una sede, con roles mediante Spatie Permission.

### Contenido de capacitación

- **Capacitación (`Curso`)**: unidad principal de formación. El nombre técnico se mantiene como `Curso`, pero la interfaz debe usar “capacitación”.
- **Sección de capacitación (`SeccionCurso`)**: agrupa módulos dentro de una capacitación.
- **Módulo (`Modulo`)**: cápsula de contenido dentro de una sección o directamente dentro de la capacitación. Puede ser video, PDF, PPT, texto, imagen o evaluación.

### Evaluaciones

- **Evaluación (`Evaluacion`)**: asociada a un módulo de tipo `evaluacion`.
- **Configuración global (`GlobalSetting`)**: define parámetros transversales como puntaje mínimo de aprobación e intentos semanales.
- **Pregunta (`Pregunta`)** y **Opción (`Opcion`)**: estructura interna del cuestionario.
- **Intentos (`IntentoEvaluacion`)** y **Respuestas (`RespuestaEvaluacion`)**: registro transaccional del progreso y resultado de cada evaluación.

### Planificación y progreso

- **Planificación de capacitaciones (`PlanificacionCurso`)**: fechas de vigencia de una capacitación por sede.
- **Progreso (`ProgresoModulo`)**: registra qué usuario completó qué módulo.
- **Certificados (`Certificado`)**: emitidos automáticamente al completar la capacitación.

### Características adicionales

- **Eventos de calendario (`EventoCalendario`)**: eventos y capacitaciones programadas.
- **Presets de reportes (`ReportePreset`)**: configuraciones de columnas para exportación.

## Stack y decisiones tecnológicas

1. **Tailwind CSS 4**: interfaz gráfica y tokens de diseño.
2. **Livewire 4**: motor de evaluación y componentes interactivos.
3. **Storage simbólico**: portadas, archivos descargables y certificados.
4. **Redis y colas**: cache y procesamiento en segundo plano.
5. **Mailpit**: depuración local de correos.
