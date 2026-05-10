# Flujo de capacitación

Este documento explica el recorrido funcional desde que un capacitador crea una capacitación hasta que un colaborador o colaboradora obtiene su certificado final. En el código, la entidad principal sigue llamándose `Curso`; en la interfaz se presenta como **capacitación**.

## 1. Creación de la capacitación

El capacitador ingresa a su panel y crea una nueva **capacitación**. A la capacitación se le asignan un título, una descripción y una imagen de portada.

### Estructuración de módulos y secciones

Una vez creada, el capacitador puede organizar la capacitación en **secciones** y **módulos**. Los módulos siguen un orden secuencial dentro de su sección y pueden ser de distintas naturalezas:

- **Teórico/Práctico:** archivo MP4, PDF, JPG/PNG, presentación, enlace embebido o texto enriquecido.
- **Evaluación:** módulo especial que representa un cuestionario de validación de conocimientos.

### Creación de evaluaciones

Cuando un capacitador añade un módulo de tipo `evaluacion`, el sistema instancia automáticamente un registro de **Evaluación**. Luego el capacitador configura preguntas (`Pregunta`) y opciones (`Opcion`), indicando cuál es la correcta.

El puntaje mínimo de aprobación y el límite de intentos semanales se definen de manera centralizada en `GlobalSetting`.

## 2. Asignación y planificación

Para que los colaboradores y colaboradoras vean una capacitación, el capacitador o administrador debe asociarla al **Estamento** correspondiente. Además, se configuran fechas de disponibilidad por sede mediante `PlanificacionCurso`.

## 3. Experiencia del colaborador/a

El colaborador o colaboradora inicia sesión y ve en **Mis capacitaciones** (`/cursos`) únicamente las capacitaciones ligadas a su Estamento.

- La navegación es secuencial: no puede saltarse módulos al azar.
- Al completar un módulo, el sistema registra el avance en `ProgresoModulo`.
- Al acceder al siguiente módulo, la barra general de progreso se actualiza.

## 4. Evaluaciones con Livewire

Cuando el colaborador o colaboradora llega a una evaluación, se carga el componente Livewire `VerEvaluacion.php`.

- Livewire verifica si quedan intentos disponibles.
- Las respuestas se mantienen y evalúan en el servidor.
- Si aprueba, se marca el progreso como completado; si no aprueba, se registra el intento y puede reintentar si aún tiene oportunidades.

## 5. Emisión del certificado

Cuando el colaborador o colaboradora completa el 100% de la capacitación, el sistema genera automáticamente un certificado PDF con `dompdf`.

El documento incluye nombre, RUT si existe, nombre de la capacitación, fecha, código de verificación y firma digital institucional. El certificado queda disponible en **Mis certificados**.

## 6. Preferencias de accesibilidad

Los usuarios pueden configurar preferencias de accesibilidad almacenadas en `users.accessibility_preferences`: tamaño de fuente, contraste, velocidad de animaciones y otras configuraciones de interfaz.
