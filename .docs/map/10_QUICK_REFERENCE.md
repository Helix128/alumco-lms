---
updated: 2026-05-04
covers: "atajos rutas tests patrones"
when_to_use: "cuando necesites saltar directo al archivo"
---

# Quick Reference

## Si necesitas X, ve a Y

1. Login o logout: `app/Http/Controllers/AuthController.php`
2. Reset de contrasena: `app/Http/Controllers/Auth/PasswordResetController.php`
3. Redireccion por rol: `app/Support/UserAreaRedirector.php`
4. Cursos del colaborador: `app/Http/Controllers/CursoController.php`
5. Detalle del curso del colaborador: `resources/views/cursos/show.blade.php`
6. Reproduccion/descarga de archivo: `app/Http/Controllers/ModuloController.php`
7. Certificados propios: `app/Http/Controllers/MisCertificadosController.php`
8. Verificacion publica de certificados: `app/Http/Controllers/VerificarCertificadoController.php`
9. Dashboard capacitador: `app/Http/Controllers/Capacitador/DashboardController.php`
10. CRUD de cursos capacitador: `app/Http/Controllers/Capacitador/CursoController.php`
11. CRUD de modulos: `app/Http/Controllers/Capacitador/ModuloController.php`
12. Secciones del curso: `app/Http/Controllers/Capacitador/SeccionCursoController.php`
13. Participantes y exportacion: `app/Http/Controllers/Capacitador/ParticipanteController.php`
14. Generacion de certificado: `app/Services/CertificadoService.php`
15. Duplicar curso: `app/Actions/Cursos/DuplicateCourseAction.php`
16. Reportes admin: `app/Http/Controllers/ReporteController.php`
17. Gestion de usuarios: `app/Livewire/Admin/UserManagement.php`
18. Formatos de reporte: `app/Livewire/Admin/ReportePresets.php`
19. Calendario colaborador: `app/Livewire/CalendarioUsuario.php`
20. Calendario capacitador: `app/Livewire/Capacitador/CalendarioCapacitaciones.php`
21. Accesibilidad: `app/Livewire/AccessibilityPreferences.php` y `app/Support/AccessibilityPreferences.php`
22. Configuracion global dev: `app/Livewire/DevConfig.php`
23. Evaluacion de modulo: `app/Livewire/VerEvaluacion.php`
24. Edicion de preguntas: `app/Livewire/Capacitador/EditarEvaluacion.php`
25. Layout de trabajador: `resources/views/layouts/user.blade.php`
26. Layout de admin/capacitador: `resources/views/layouts/panel.blade.php`
27. PDF de modulo: `resources/views/components/file-viewer.blade.php`
28. PDF de certificado: `resources/views/capacitador/certificados/plantilla.blade.php`
29. Navegacion inferior mobile: `resources/views/partials/bottom-nav.blade.php`
30. JS de visor PDF: `resources/js/app.js`

## Comandos Artisan personalizados

- `lms:send-course-available-notifications`
  - Programado en `routes/console.php` a las `08:00` hora `America/Santiago`.
  - Envía mails cuando una planificacion entra en ventana activa.
- `lms:send-course-deadline-reminders`
  - Programado en `routes/console.php` a las `09:00` hora `America/Santiago`.
  - Envía recordatorios a 2 dias del vencimiento.
- `inspire`
  - Comando demo de Laravel, sin uso funcional de negocio.

## Patrones frecuentes del proyecto

- `route()->name` es la forma canonica de enlazar vistas.
- `wire:navigate.hover` aparece en navegacion principal y sidebar.
- El acceso por rol no se apoya en policies; se resuelve con middleware + helpers de `User`.
- Los archivos de modulo y certificados se sirven desde `storage/public`, no por URLs abiertas directas en la UI de trabajador.
- Las evaluaciones se guardan con Livewire y se validan por preguntas/opciones en memoria y BD.
- La accesibilidad persiste en BD y en `localStorage`.
- Los reportes y dashboards pesados usan cache flexible.
- Las notificaciones importantes tienen dedupe en `notification_deliveries`.

