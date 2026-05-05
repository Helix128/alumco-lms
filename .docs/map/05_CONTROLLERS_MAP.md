---
updated: 2026-05-04
covers: "controladores metodos dependencias"
when_to_use: "cuando necesites ubicar request response"
---

# Controllers Map

## Auth

### `app/Http/Controllers/AuthController.php`

- Dependencias inyectadas: `Request`
- Metodos:
  - `showLoginForm()` - GET `/login` - Renderiza login.
  - `login(Request $request)` - POST `/login` - Valida credenciales, hace `Auth::attempt` y redirige por area.
  - `logout(Request $request)` - POST `/logout` - Cierra sesion y regresa a `/login`.

### `app/Http/Controllers/Auth/PasswordResetController.php`

- Dependencias inyectadas: `Request`
- Metodos:
  - `showForgotForm()` - GET `/forgot-password` - Muestra form de recuperacion.
  - `sendResetLink(Request $request)` - POST `/forgot-password` - Envia link de reset.
  - `showResetForm(Request $request, string $token)` - GET `/reset-password/{token}` - Renderiza form con token/email.
  - `resetPassword(Request $request)` - POST `/reset-password` - Cambia password y reloguea.

## General area

### `app/Http/Controllers/CursoController.php`

- Dependencias inyectadas: ninguna
- Metodos:
  - `index()` - GET `/cursos` - Clasifica cursos en vigentes, completados y anteriores.
  - `show(Curso $curso)` - GET `/cursos/{curso}` - Valida acceso y muestra detalles/progreso.

### `app/Http/Controllers/ModuloController.php`

- Dependencias inyectadas: ninguna
- Metodos:
  - `show(Curso $curso, Modulo $modulo)` - GET `/cursos/{curso}/modulos/{modulo}` - Abre capsula o evaluacion.
  - `verArchivo(Curso $curso, Modulo $modulo)` - GET `/archivo` - Sirve archivo inline.
  - `descargarArchivo(Curso $curso, Modulo $modulo)` - GET `/descargar` - Descarga archivo.
  - `completar(Curso $curso, Modulo $modulo)` - POST `/completar` - Registra progreso y redirige.

### `app/Http/Controllers/MisCertificadosController.php`

- Dependencias inyectadas: `CertificadoService`
- Metodos:
  - `index()` - GET `/mis-certificados` - Lista certificados del usuario.
  - `descargar(Certificado $certificado, CertificadoService $service)` - Descarga el PDF del usuario.

### `app/Http/Controllers/PerfilController.php`

- Dependencias inyectadas: ninguna
- Metodos:
  - `show()` - GET `/perfil` - Resume cursos, certificados e info personal.
  - `showAdmin()` - GET `/admin/perfil` - Muestra perfil admin.

### `app/Http/Controllers/ReporteController.php`

- Dependencias inyectadas: `Request`
- Metodos:
  - `index(Request $request)` - GET `/admin/reportes` - Construye filtros, paginacion y vista.
  - `exportar(Request $request)` - GET `/admin/reportes/exportar` - Descarga XLSX.

### `app/Http/Controllers/VerificarCertificadoController.php`

- Dependencias inyectadas: `Request`
- Metodos:
  - `index(Request $request)` - GET `/certificados/verificar` - Busca codigo en query string o muestra form.
  - `show(string $codigo)` - GET `/certificados/verificar/{codigo}` - Normaliza y consulta certificado.

## Capacitador

### `app/Http/Controllers/Capacitador/DashboardController.php`

- Dependencias inyectadas: ninguna
- Metodos:
  - `index()` - GET `/capacitador` - Calcula resumen cacheado.

### `app/Http/Controllers/Capacitador/CursoController.php`

- Dependencias inyectadas: `Request`, `DuplicateCourseAction`
- Metodos:
  - `index()` - GET `/capacitador/cursos` - Lista cursos del capacitador o admin.
  - `create()` - GET `/capacitador/cursos/crear` - Formulario de alta.
  - `store(Request $request)` - POST `/capacitador/cursos` - Crea curso y sube portada.
  - `show(Curso $curso)` - GET `/capacitador/cursos/{curso}` - Carga estructura del curso y sanea evaluaciones faltantes.
  - `edit(Curso $curso)` - GET `/editar` - Form de edicion.
  - `update(Request $request, Curso $curso)` - PUT - Actualiza curso.
  - `destroy(Curso $curso)` - DELETE - Borra curso y sus archivos.
  - `duplicar(Request $request, Curso $curso, DuplicateCourseAction $action)` - POST `/duplicar` - Clona curso.

### `app/Http/Controllers/Capacitador/ModuloController.php`

- Dependencias inyectadas: `Request`
- Metodos:
  - `create(Curso $curso)` - Form de modulo.
  - `store(Request $request, Curso $curso)` - Crea modulo y evaluacion si corresponde.
  - `edit(Curso $curso, Modulo $modulo)` - Form de edicion.
  - `update(Request $request, Curso $curso, Modulo $modulo)` - Actualiza modulo y archivo.
  - `destroy(Curso $curso, Modulo $modulo)` - Borra modulo y reordena.
  - `evaluacion(Curso $curso, Modulo $modulo)` - Abre la vista de evaluacion.
  - `reordenar(Request $request, Curso $curso)` - Reordena modulos.

### `app/Http/Controllers/Capacitador/ParticipanteController.php`

- Dependencias inyectadas: `Request`
- Metodos:
  - `index(Curso $curso)` - Lista participantes con progreso y certificado.
  - `syncEstamentos(Request $request, Curso $curso)` - Sincroniza estamentos del curso.
  - `exportar(Curso $curso)` - Exporta participantes a Excel.

### `app/Http/Controllers/Capacitador/SeccionCursoController.php`

- Dependencias inyectadas: `Request`
- Metodos:
  - `store(Request $request, Curso $curso)` - Crea seccion.
  - `update(Request $request, Curso $curso, SeccionCurso $seccion)` - Actualiza seccion.
  - `destroy(Curso $curso, SeccionCurso $seccion)` - Elimina seccion dejando modulos huérfanos.
  - `reordenar(Request $request, Curso $curso)` - Reordena secciones y modulos.

### `app/Http/Controllers/Capacitador/CertificadoController.php`

- Dependencias inyectadas: `CertificadoService`
- Metodos:
  - `generar(Curso $curso, User $user, CertificadoService $service)` - Genera certificado.
  - `descargar(Certificado $certificado, CertificadoService $service)` - Descarga PDF del curso.

## Admin / legacy

### `app/Http/Controllers/ReporteController.php`

- Ya cubierto arriba por su uso en admin.

### `app/Http/Controllers/Admin/EstamentoController.php`

- Estado: vacio, sin metodos publicos.

### `app/Http/Controllers/Admin/SedeController.php`

- Estado: vacio, sin metodos publicos.

