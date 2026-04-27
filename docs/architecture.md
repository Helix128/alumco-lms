# Arquitectura del Sistema: Alumco LMS

El sistema está diseñado en base a **Laravel 13** y sigue el patrón Modelo-Vista-Controlador (MVC), integrando **Livewire 4** para componentes interactivos dinámicos sin necesidad de recargar la página o requerir de Javascript pesado.

## Entidades Principales (Modelos de Base de Datos)

El dominio de negocio se centra en la gestión y distribución de contenidos de capacitación para los trabajadores de diversas Sedes y Estamentos de la ONG.

### Estructura Organizacional
- **Sede (`Sede`)**: Representa la ubicación física o sucursal.
- **Estamento (`Estamento`)**: Representa la división, departamento o rol jerárquico. Ej: Docentes, Administrativos, Equipo Directivo.
- **Usuario (`User`)**: Cada cuenta pertenece a un **Estamento** y una **Sede**. Los usuarios tienen roles de acceso gracias a Spatie Permission. Adicionalmente, almacenan información de su firma digital y datos demográficos.

### Contenido de Capacitación
- **Curso (`Curso`)**: La unidad de aprendizaje más grande, agrupando diversos Módulos. Un curso está asignado a múltiples Estamentos, es decir, solo quienes pertenezcan a los Estamentos asociados pueden ver el Curso.
- **Módulo (`Modulo`)**: Una cápsula de contenido dentro del curso. Los módulos se dividen en diferentes **tipos**:
  - `video` (MP4 directo o YouTube)
  - `pdf` (Visor en pantalla o descarga)
  - `imagen` (Visor gráfico)
  - `texto` (Contenido enriquecido WYSIWYG)
  - `descargable` (Archivos PPT, DOCX u otros tipos)
  - `evaluacion` (Tests o cuestionarios dinámicos)

### Cuestionarios y Evaluaciones
La estructura relacional que maneja la evaluación:
- **Evaluación (`Evaluacion`)**: Asociada a un Módulo de tipo "evaluacion". Determina el número máximo de intentos semanales y el puntaje necesario.
- **Pregunta (`Pregunta`)** y **Opción (`Opcion`)**: El desglose interno del cuestionario, donde una Opción se marca como "correcta".
- **Intentos (`IntentoEvaluacion`)** y **Respuestas (`RespuestaEvaluacion`)**: Guarda el registro transaccional cuando el usuario completa la prueba mediante Livewire, controlando el contador de intentos y los bloqueos temporales.

### Planificación y Progreso
- **Planificación de Cursos (`PlanificacionCurso`)**: Fechas de vigencia de un curso de acuerdo a una Sede/Estamento específico.
- **Progreso (`ProgresoModulo`)**: Tabla pivot que registra qué usuario completó qué módulo y si efectivamente aprobó la instancia de evaluación correspondiente.
- **Certificados (`Certificado`)**: Emitido automáticamente tras completar el 100% de los módulos en un Curso. Guarda un PDF estático auto-generado (`dompdf`) que sella la aprobación del usuario y lo almacena localmente en `storage/app/public`.

## Stack y Decisiones Tecnológicas

1. **Tailwind CSS 4**: Se usa para toda la interfaz gráfica. Los tokens y utilidades se procesan mediante Vite.
2. **Livewire 4**: Es el núcleo para el motor de la evaluación (`livewire/ver-evaluacion`), controlando paginación de preguntas, persistencia de selecciones, y comprobación final (aprobación) evitando exposición de respuestas correctas en HTML.
3. **Storage Simbólico**: Toda la manipulación de portadas, archivos de módulos descargables y certificados emitidos utiliza `Storage::disk('public')`. Es vital que la máquina cuente con el link `storage/app/public` apuntando a `public/storage`.
4. **Redis y Colas**: Integrado para el manejo de cache y procesamiento en segundo plano (Manejado en Sail).
5. **Mailpit**: Entorno en Sail para atrapar y depurar notificaciones por correo (Ej: Recuperar Contraseña) de manera local.
