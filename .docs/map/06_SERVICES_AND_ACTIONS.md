---
updated: 2026-05-04
covers: "services actions jobs commands"
when_to_use: "cuando busques logica de negocio"
---

# Services and Actions

## Service Layer

### `app/Services/CertificadoService.php`

- Proposito: centraliza emision, render PDF, nombre de archivo y notificacion de certificado.
- Inputs: `User`, `Curso`, `Certificado`
- Outputs: `Certificado`, `string` PDF, `string` filename
- Usa:
  - `generarParaUsuario()` para crear o reutilizar certificado
  - `output()` para stream PDF
  - `downloadFileName()` para nombre legible
- Invocado por:
  - `Capacitador\CertificadoController`
  - `MisCertificadosController`

## Action Objects

### `app/Actions/Cursos/DuplicateCourseAction.php`

- Proposito: clonacion profunda de un curso con modulos, evaluaciones, preguntas y opciones.
- Inputs: `Curso $cursoOriginal`, `string $nuevoTitulo`
- Output: `Curso` nuevo
- Invocado por: `Capacitador\CursoController@duplicar`

## Console commands

### `app/Console/Commands/SendCourseAvailableNotifications.php`

- Signature: `lms:send-course-available-notifications`
- Proposito: notifica a trabajadores cuando un curso entra en ventana activa.
- Input: planificaciones vigentes del dia
- Output: conteo en consola
- Invocado por: scheduler en `routes/console.php`

### `app/Console/Commands/SendCourseDeadlineReminderNotifications.php`

- Signature: `lms:send-course-deadline-reminders`
- Proposito: envía recordatorios 2 dias antes del vencimiento para cursos con avance bajo.
- Input: planificaciones con `fecha_fin = hoy + 2 dias`
- Output: conteo en consola
- Invocado por: scheduler en `routes/console.php`

## Support / helpers

### `app/Support/AccessibilityPreferences.php`

- Proposito: normaliza preferencias de accesibilidad y calcula tamano de fuente.
- Inputs: arrays o null
- Output: arreglo canonico con `fontLevel`, `highContrast`, `reducedMotion`
- Usado por layouts, Livewire y scripts frontend

### `app/Support/UserAreaRedirector.php`

- Proposito: decide la zona canonica de un usuario y valida intended URL.
- Inputs: `User`, `Request`, session preview mode
- Output: route name o URL
- Usado por login, root redirect y middleware de area

## Jobs / Events / Listeners

- `app/Jobs/`: No implementado
- `app/Events/`: No implementado
- `app/Listeners/`: No implementado

