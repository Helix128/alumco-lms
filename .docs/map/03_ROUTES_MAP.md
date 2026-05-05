---
updated: 2026-05-04
covers: "rutas middleware controladores"
when_to_use: "cuando necesites ubicar un endpoint"
---

# Routes Map

## Public / Auth

| Metodo | URI | Nombre | Controlador@Metodo | Middleware | Proposito |
|---|---|---|---|---|---|
| GET | `/login` | `login` | `AuthController@showLoginForm` | guest | Muestra el login. |
| POST | `/login` | - | `AuthController@login` | guest, throttle:login | Autentica al usuario. |
| POST | `/logout` | `logout` | `AuthController@logout` | auth | Cierra sesion. |
| GET | `/forgot-password` | `password.request` | `PasswordResetController@showForgotForm` | guest | Formulario de recuperacion. |
| POST | `/forgot-password` | `password.email` | `PasswordResetController@sendResetLink` | guest | Envia link de reset. |
| GET | `/reset-password/{token}` | `password.reset` | `PasswordResetController@showResetForm` | guest | Formulario de nueva clave. |
| POST | `/reset-password` | `password.update` | `PasswordResetController@resetPassword` | guest | Guarda la nueva clave. |
| GET | `/certificados/verificar` | `certificados.verificar.index` | `VerificarCertificadoController@index` | throttle:30,1 | Busqueda publica de certificado. |
| GET | `/certificados/verificar/{codigo}` | `certificados.verificar.show` | `VerificarCertificadoController@show` | throttle:30,1 | Resultado de verificacion publica. |

## Worker area

| Metodo | URI | Nombre | Controlador@Metodo | Middleware | Proposito |
|---|---|---|---|---|---|
| GET | `/` | - | Closure redirect | auth | Redirige a la zona canonica del usuario. |
| GET | `/cursos` | `cursos.index` | `CursoController@index` | auth, worker.area | Lista cursos del colaborador. ⚡ |
| GET | `/cursos/{curso}` | `cursos.show` | `CursoController@show` | auth, worker.area | Muestra detalle y progreso. ⚡ |
| GET | `/cursos/{curso}/modulos/{modulo}` | `modulos.show` | `ModuloController@show` | auth, worker.area | Abre modulo o evaluacion. ⚡ |
| GET | `/cursos/{curso}/modulos/{modulo}/archivo` | `modulos.archivo` | `ModuloController@verArchivo` | auth, worker.area | Vista inline del archivo. |
| GET | `/cursos/{curso}/modulos/{modulo}/descargar` | `modulos.descargar` | `ModuloController@descargarArchivo` | auth, worker.area | Descarga archivo del modulo. |
| POST | `/cursos/{curso}/modulos/{modulo}/completar` | `modulos.completar` | `ModuloController@completar` | auth, worker.area | Marca modulo completado. ⚡ |
| GET | `/calendario-cursos` | `calendario-cursos.index` | `App\Livewire\CalendarioUsuario` | auth, worker.area | Calendario personal. |
| GET | `/perfil` | `perfil.index` | `PerfilController@show` | auth, worker.area | Perfil del colaborador. |
| GET | `/mis-certificados` | `mis-certificados.index` | `MisCertificadosController@index` | auth, worker.area | Lista certificados propios. ⚡ |
| GET | `/mis-certificados/{certificado}/descargar` | `mis-certificados.descargar` | `MisCertificadosController@descargar` | auth, worker.area | Descarga PDF propio. ⚡ |
| GET | `/ajustes` | `ajustes.index` | Closure redirect | auth, worker.area | Legacy a certificados. |

## Capacitador

| Metodo | URI | Nombre | Controlador@Metodo | Middleware | Proposito |
|---|---|---|---|---|---|
| GET | `/capacitador` | `capacitador.dashboard` | `DashboardController@index` | auth, capacitador | Dashboard del capacitador. ⚡ |
| GET | `/capacitador/cursos` | `capacitador.cursos.index` | `CursoController@index` | auth, capacitador | Lista cursos propios. ⚡ |
| GET | `/capacitador/cursos/crear` | `capacitador.cursos.crear` | `CursoController@create` | auth, capacitador | Form de nuevo curso. |
| POST | `/capacitador/cursos` | `capacitador.cursos.store` | `CursoController@store` | auth, capacitador | Crea curso. |
| GET | `/capacitador/cursos/{curso}` | `capacitador.cursos.show` | `CursoController@show` | auth, capacitador | Detalle y estructura del curso. ⚡ |
| GET | `/capacitador/cursos/{curso}/editar` | `capacitador.cursos.editar` | `CursoController@edit` | auth, capacitador | Editar curso. |
| PUT | `/capacitador/cursos/{curso}` | `capacitador.cursos.update` | `CursoController@update` | auth, capacitador | Actualiza curso. |
| DELETE | `/capacitador/cursos/{curso}` | `capacitador.cursos.destroy` | `CursoController@destroy` | auth, capacitador | Elimina curso. |
| POST | `/capacitador/cursos/{curso}/duplicar` | `capacitador.cursos.duplicar` | `CursoController@duplicar` | auth, capacitador | Duplica curso. |
| GET | `/capacitador/cursos/{curso}/modulos/crear` | `capacitador.cursos.modulos.crear` | `ModuloController@create` | auth, capacitador | Form de modulo. |
| POST | `/capacitador/cursos/{curso}/modulos` | `capacitador.cursos.modulos.store` | `ModuloController@store` | auth, capacitador | Crea modulo. |
| GET | `/capacitador/cursos/{curso}/modulos/{modulo}/editar` | `capacitador.cursos.modulos.editar` | `ModuloController@edit` | auth, capacitador | Editar modulo. |
| PUT | `/capacitador/cursos/{curso}/modulos/{modulo}` | `capacitador.cursos.modulos.update` | `ModuloController@update` | auth, capacitador | Actualiza modulo. |
| DELETE | `/capacitador/cursos/{curso}/modulos/{modulo}` | `capacitador.cursos.modulos.destroy` | `ModuloController@destroy` | auth, capacitador | Elimina modulo. |
| GET | `/capacitador/cursos/{curso}/modulos/{modulo}/evaluacion` | `capacitador.cursos.modulos.evaluacion` | `ModuloController@evaluacion` | auth, capacitador | Editar evaluacion asociada. |
| POST | `/capacitador/cursos/{curso}/modulos/reordenar` | `capacitador.cursos.modulos.reordenar` | `ModuloController@reordenar` | auth, capacitador | Reordena modulos. |
| POST | `/capacitador/cursos/{curso}/secciones` | `capacitador.cursos.secciones.store` | `SeccionCursoController@store` | auth, capacitador | Crea seccion. |
| PUT | `/capacitador/cursos/{curso}/secciones/{seccion}` | `capacitador.cursos.secciones.update` | `SeccionCursoController@update` | auth, capacitador | Actualiza seccion. |
| DELETE | `/capacitador/cursos/{curso}/secciones/{seccion}` | `capacitador.cursos.secciones.destroy` | `SeccionCursoController@destroy` | auth, capacitador | Elimina seccion. |
| POST | `/capacitador/cursos/{curso}/secciones/reordenar` | `capacitador.cursos.secciones.reordenar` | `SeccionCursoController@reordenar` | auth, capacitador | Reordena secciones y modulos. |
| GET | `/capacitador/cursos/{curso}/participantes` | `capacitador.cursos.participantes.index` | `ParticipanteController@index` | auth, capacitador | Lista participantes. |
| POST | `/capacitador/cursos/{curso}/estamentos` | `capacitador.cursos.estamentos.sync` | `ParticipanteController@syncEstamentos` | auth, capacitador, capacitador.interno | Asigna estamentos al curso. |
| GET | `/capacitador/cursos/{curso}/participantes/exportar` | `capacitador.cursos.participantes.exportar` | `ParticipanteController@exportar` | auth, capacitador, capacitador.interno | Exporta participantes. |
| POST | `/capacitador/cursos/{curso}/certificados/{user}` | `capacitador.certificados.generar` | `CertificadoController@generar` | auth, capacitador | Genera certificado. |
| GET | `/capacitador/certificados/{certificado}/descargar` | `capacitador.certificados.descargar` | `CertificadoController@descargar` | auth, capacitador | Descarga certificado. |
| GET | `/capacitador/calendario` | `capacitador.calendario.index` | `App\Livewire\Capacitador\CalendarioCapacitaciones` | auth, capacitador | Calendario institucional. |

## Admin / Dev

| Metodo | URI | Nombre | Controlador@Metodo | Middleware | Proposito |
|---|---|---|---|---|---|
| GET | `/admin/reportes` | `admin.reportes.index` | `ReporteController@index` | auth, admin | Panel de reportes. ⚡ |
| GET | `/admin/reportes/exportar` | `admin.reportes.exportar` | `ReporteController@exportar` | auth, admin | Exporta reporte XLSX. |
| GET | `/admin/usuarios` | `admin.usuarios.index` | Closure view | auth, admin | Directorio de usuarios. ⚡ |
| GET | `/admin/perfil` | `admin.perfil.index` | `PerfilController@showAdmin` | auth, admin | Perfil administrativo. |
| POST | `/admin/preview-mode/toggle` | `admin.preview.toggle` | Closure | auth | Activa/desactiva vista previa. ⚡ |
| GET | `/dev/configuracion` | `dev.configuracion` | Closure view | auth | Configuracion para desarrollador. |

