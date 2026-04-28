# Flujo de Aprendizaje y Cursos

Este documento explica el recorrido funcional completo desde que un Capacitador concibe un curso hasta que el Trabajador obtiene su certificado final.

## 1. Creación del Curso (Por el Capacitador)
El Capacitador ingresa a su panel y crea un nuevo **Curso**. 
Al curso se le asignan un título, descripción, y una imagen de portada.

### Estructuración de Módulos
Una vez el Curso existe, el capacitador comienza a inyectarle **Módulos**. 
Los módulos deben seguir un orden secuencial (Orden 1, 2, 3...) y pueden ser de distintas naturalezas:
- **Teórico/Práctico:** Se sube un archivo (MP4 para `video`, PDF para `pdf`, JPG/PNG para `imagen`) o se inserta un link embebido (Youtube). Si es `texto`, se digita mediante un editor WYSIWYG en HTML. 
- **Evaluación:** Módulo especial que representa un Quiz o Test de validación de conocimientos.

### Creación de Evaluaciones
Cuando un Capacitador añade un Módulo de tipo `evaluacion`, el sistema instancia automáticamente un registro de **Evaluación**. Posteriormente, el capacitador debe ir a dicha Evaluación para:
- Crear las preguntas (`Pregunta`) y definir múltiples opciones (`Opcion`), indicando explícitamente cuál es la correcta.
- **Nota sobre Parámetros**: El *puntaje mínimo de aprobación* y el *límite de intentos semanales* se definen ahora de manera centralizada en la configuración global del sistema (`GlobalSetting`) para mantener la consistencia institucional.

## 2. Asignación y Planificación (Habilitar el Curso)
Para que los trabajadores vean el curso, el Capacitador o Administrador debe asociar el Curso al **Estamento** (o Estamentos) correspondientes.
Adicionalmente, se configuran fechas de disponibilidad global para evitar accesos tempranos o a cursos ya vencidos (`PlanificacionCurso`).

## 3. La Experiencia del Trabajador (Consumo de Contenido)
El trabajador inicia sesión y ve en su "Salón Virtual" (`/cursos`) únicamente aquellos cursos ligados a su Estamento.

- **Navegación Secuencial**: El trabajador no puede saltarse los módulos al azar. Entra al Módulo 1 (Ej: Un Video), lo consume, y presiona un botón verde **"¡Listo! Siguiente"**.
- Esto dispara un `POST` al controlador que marca un registro en `ProgresoModulo`, asegurando que ese usuario completó ese módulo específico, y desbloquea el acceso a la URL del siguiente.
- Al acceder al siguiente módulo, la barra general de progreso sube.

### 4. Completando Evaluaciones (Livewire 4)
Cuando el trabajador llega a un módulo que contiene una Evaluación, la experiencia cambia. En lugar del controlador normal, se carga un componente **Livewire** en la vista (`VerEvaluacion.php`).

- **Bloqueo por Intentos**: Livewire inmediatamente comprueba si el usuario no ha agotado sus "intentos semanales" definidos globalmente. Si los agotó, se muestra una pantalla de bloqueo (`Límite alcanzado, regresa en X días`).
- **Seguridad Antifraude**: A diferencia de enviar todas las respuestas al frontend para que un script JS evalúe, Livewire mantiene todo en el servidor (PHP). El usuario pulsa una opción, el componente la almacena localmente y avanza.
- **Veredicto**: Al enviar la última pregunta, Livewire cuenta en el servidor los aciertos. 
  - *Aprobado*: Se marca el Progreso como completado.
  - *No Aprobado*: Gasta un intento, muestra un mensaje de fallo, y obliga a reintentar si es que le quedan chances.

## 5. Emisión del Certificado
En el instante preciso en que el trabajador oprime el botón para marcar como completado el *ÚLTIMO módulo* del curso (ya sea una evaluación final aprobada o un video normal), la función de progreso detecta que el porcentaje ha alcanzado el **100%**.

- Automáticamente, se toma la vista de PDF estipulada y se compila utilizando el motor **dompdf** (`barryvdh/laravel-dompdf`).
- Este documento lleva el nombre del usuario, RUT (si estuviese presente), el nombre del curso, la fecha y se adhiere la *firma digital* en PNG del Administrador/Director previamente subida en los Settings Globales.
- El PDF físico se inyecta en el Storage de Laravel (`public/certificados/XX.pdf`) y se crea una entrada en la base de datos `Certificado`.
- El trabajador es notificado y puede ver/descargar su certificado desde la sección **Mis Logros** para toda la posteridad.
