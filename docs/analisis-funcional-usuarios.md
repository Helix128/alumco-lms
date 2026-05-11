# Alumco LMS: análisis funcional orientado al usuario

Este documento describe lo que está implementado en la plataforma desde el punto de vista de uso. Cuando una función no aparece disponible, se indica explícitamente.

## 1. Perfiles de usuario

Existen 5 perfiles:

| Perfil | Acceso principal | Qué puede hacer |
| --- | --- | --- |
| Desarrollador | Panel de salud del LMS | Accede a herramientas técnicas, soporte técnico, configuración global, panel administrativo, reportes, usuarios, estamentos, firma institucional, gestión de capacitaciones y vista previa como colaborador/a. |
| Administrador | Dashboard analítico | Accede al panel administrativo, reportes, usuarios, estamentos, firma institucional, gestión de capacitaciones, planificación institucional, certificados y vista previa como colaborador/a. No accede al panel de salud ni soporte técnico de desarrollador. |
| Capacitador Interno | Dashboard capacitador | Gestiona sus capacitaciones, módulos, evaluaciones, participantes, certificados, calendario institucional en modo lectura, estadísticas y firma personal. Además puede asociar estamentos a sus capacitaciones y exportar participantes. |
| Capacitador Externo | Dashboard capacitador | Gestiona sus capacitaciones, módulos, evaluaciones, participantes, certificados, calendario institucional en modo lectura, estadísticas y firma personal. No se encontró acceso implementado para asociar estamentos ni exportar participantes. |
| Trabajador | Mis capacitaciones | Realiza capacitaciones asignadas, revisa calendario, descarga certificados propios, gestiona soporte, ajusta accesibilidad, envía feedback y cierra sesión. |

No se encontraron permisos granulares configurados por acción; el acceso visible se organiza por perfil y área.

## 2. Flujo completo por perfil

### Trabajador

1. Ingresa con correo y contraseña.
2. Llega a **Mis capacitaciones**.
3. Revisa el resumen de capacitaciones vigentes, en proceso, completas y pasadas.
4. Entra a una capacitación vigente.
5. Revisa portada, descripción, nota del capacitador, progreso y programa.
6. Abre módulos disponibles en orden.
7. Completa módulos de video, documento, presentación, imagen o texto con las acciones **Listo, siguiente**, **Finalizar capacitación** o **Volver a la capacitación**.
8. Si encuentra una evaluación, responde una alternativa por pregunta.
9. Al aprobar, la evaluación se marca como completada; si con eso llega al 100%, se intenta generar el certificado automáticamente.
10. Al completar la capacitación, puede dejar feedback de la capacitación.
11. Puede navegar a **Calendario**, **Certificados**, **Soporte** o **Perfil**.
12. Puede enviar feedback general de plataforma desde el botón flotante **Feedback**.
13. Cierra sesión desde el botón **Salir** o desde su perfil.

### Capacitador Interno

1. Ingresa y llega al **Dashboard capacitador**.
2. Revisa indicadores de sus capacitaciones.
3. Navega a **Capacitaciones y material**.
4. Crea, edita, duplica o elimina capacitaciones propias.
5. Agrega, edita, elimina y ordena módulos.
6. Crea y organiza secciones.
7. Gestiona preguntas, alternativas y respuesta correcta de evaluaciones.
8. Revisa participantes asociados por estamento.
9. Asocia o actualiza estamentos para definir audiencia del curso.
10. Exporta participantes de una capacitación.
11. Genera y descarga certificados cuando corresponde.
12. Revisa calendario institucional en modo lectura.
13. Revisa estadísticas.
14. Actualiza firma personal para certificados.
15. Puede entrar a soporte como usuario autenticado.
16. Cierra sesión.

### Capacitador Externo

El flujo es igual al de Capacitador Interno, salvo que no se encontró acceso implementado para:

- Asociar estamentos a una capacitación.
- Exportar la lista de participantes.

### Administrador

1. Ingresa y llega al **Dashboard analítico**.
2. Filtra datos por año, sede, estamento y capacitación.
3. Revisa vistas de resumen, progreso, calidad y segmentos.
4. Entra a reportes, filtra resultados y exporta Excel.
5. Administra usuarios: crea, edita, activa/desactiva, envía recuperación de acceso y elimina.
6. Administra estamentos.
7. Gestiona firma institucional.
8. Gestiona capacitaciones y material de todos los capacitadores.
9. Planifica capacitaciones en calendario anual o mensual.
10. Puede activar **Ver como Usuario** para revisar la experiencia de colaborador/a.
11. Actualiza su perfil y firma personal.
12. Cierra sesión.

### Desarrollador

1. Ingresa y llega a **Salud LMS**.
2. Revisa estado de servicios, jobs, logs, datos, rendimiento y caché.
3. Ejecuta acciones rápidas con confirmación: limpiar caché optimizada y vaciar jobs fallidos.
4. Reintenta u olvida jobs fallidos.
5. Revisa y elimina archivos de logs desde la vista correspondiente.
6. Gestiona soporte técnico.
7. Configura variables globales de evaluación: porcentaje de aprobación e intentos semanales.
8. También puede usar las funciones administrativas: dashboard, reportes, usuarios, estamentos, firma institucional, material y calendario.
9. Puede activar vista previa como colaborador/a.
10. Cierra sesión.

## 3. Navegación

### Trabajador

Menú superior en escritorio y menú inferior en móvil:

- **Mis capacitaciones**
- **Calendario**
- **Certificados**
- **Soporte**
- Avatar o perfil
- **Salir**

### Administrador

Barra lateral con grupos:

- **Estadísticas**: Dashboard analítico, Reportes de capacitación.
- **Material**: Capacitaciones y material, Calendario institucional.
- **Gestión**: Directorio de usuarios, Estamentos, Perfil y firma, Firma institucional.
- Botón superior **Ver como Usuario**.
- Cierre de sesión en la barra lateral.

### Capacitador Interno y Externo

Barra lateral con:

- **Estadísticas**: Dashboard capacitador, Estadísticas.
- **Material**: Capacitaciones y material, Calendario institucional.
- **Gestión**: Perfil y firma.
- Cierre de sesión.

### Desarrollador

Incluye la navegación administrativa y agrega:

- **Desarrollador**: Lógica de negocio, Salud LMS, Soporte técnico.

## 4. Formularios y acciones

### Acceso

- **Inicio de sesión**: correo electrónico, contraseña, recordarme. Valida correo requerido y contraseña requerida. Si las credenciales no coinciden, muestra error.
- **Recuperar contraseña**: correo electrónico requerido y válido. Envía enlace si el correo existe y el sistema de recuperación lo acepta.
- **Nueva contraseña**: correo, nueva contraseña, confirmación y token. La contraseña requiere mínimo 8 caracteres y confirmación.
- **Cerrar sesión**: finaliza la sesión y vuelve al login.

No se encontró bloqueo visible de inicio de sesión para usuarios marcados como inactivos, aunque el estado activo/inactivo sí existe en el directorio de usuarios.

### Capacitaciones del trabajador

- **Completar módulo**: permite avanzar al siguiente módulo o volver a la capacitación. Al completar el último módulo no evaluativo muestra “¡Curso completado!”.
- **Evaluación**: selección de una alternativa por pregunta, botones anterior/continuar. No permite avanzar sin responder la pregunta actual.
- **Feedback de capacitación**: valoración 1 a 5, tema principal y comentario opcional. Solo aparece con 100% de progreso.
- **Feedback de plataforma**: categoría y mensaje. El mensaje es obligatorio, mínimo 6 y máximo 1200 caracteres.

### Certificados

- **Buscar certificados propios**: búsqueda local por nombre de capacitación.
- **Descargar certificado**: descarga PDF propio.
- **Verificación pública**: código de certificado. Si existe, muestra validez; si no, indica que no se encontró.

### Soporte

- **Crear ticket**: nombre, correo, categoría, asunto, descripción y capturas opcionales.
- Categorías: Acceso, Error de plataforma, Curso o contenido, Certificados, Cuenta, Otro.
- Validaciones: nombre mínimo 3 y máximo 120, correo válido, asunto mínimo 6 y máximo 160, descripción mínimo 12 y máximo 5000, hasta 3 capturas, JPG/PNG/WEBP, máximo 4 MB cada una.
- Límite visible por validación: hasta 3 tickets por hora por usuario/correo aproximado; si se supera, pide esperar.

### Capacitaciones y material

- **Crear/editar capacitación**: título obligatorio, descripción, nota para participantes, imagen de portada, color de portada o color automático.
- Validaciones: título máximo 255, nota máximo 1200, portada JPG/PNG/WEBP máximo 4 MB, color en formato hexadecimal.
- **Duplicar capacitación**: nuevo título obligatorio.
- **Eliminar capacitación**: elimina la capacitación y sus archivos asociados.
- **Crear módulo**: título, tipo de contenido, duración, archivo o texto según tipo.
- Tipos: video, documento, presentación, texto, imagen, evaluación.
- Validaciones: título obligatorio, tipo válido, duración mínima 1 minuto si se informa, archivo máximo 500 MB, formatos según tipo: MP4, PDF, PPT/PPTX, JPEG/PNG/JPG/GIF/WEBP.
- La pantalla informa 100 MB como orientación de carga, pero la validación implementada acepta hasta 500 MB.
- **Editar módulo**: título, duración y archivo/contenido. El tipo de contenido no es editable.
- **Eliminar módulo**: elimina el módulo y reordena los siguientes.
- **Secciones**: crear, editar, eliminar y reordenar. Título obligatorio, máximo 255.
- **Reordenar estructura**: permite mover secciones y módulos.
- **Gestionar evaluación**: agregar preguntas, editar enunciados, agregar alternativas, marcar una correcta, eliminar preguntas/opciones y reordenar preguntas.

### Participantes y certificados para capacitadores

- **Asociar estamentos**: selección de estamentos. Solo visible para Capacitador Interno y perfiles administrativos.
- **Exportar participantes**: genera Excel con RUT, nombre, email, estamento, sede, progreso y fecha de certificado. Solo visible para Capacitador Interno y perfiles administrativos.
- **Generar certificado**: intenta emitir certificado para un participante.
- Si la capacitación tiene evaluación, exige que exista una evaluación aprobada.
- Si la capacitación no tiene evaluación, no se encontró una restricción visible adicional de avance antes de generar el certificado.

### Calendario institucional

- **Vista anual/mensual**.
- Filtros y navegación: año anterior/siguiente, mes anterior/siguiente, hoy, ir a mes.
- **Planificación**: capacitación, sede, fecha inicio, fecha fin, notas.
- Validaciones: capacitación existente, fecha inicio requerida, fecha fin requerida y posterior o igual a inicio, sede válida si se selecciona.
- **Copiar año**: año origen y destino entre 2020 y 2099. Si el destino ya tiene planificaciones, permite añadir o reemplazar.
- **Eliminar planificación**: confirma eliminación del bloque.
- Para capacitadores, el calendario está disponible en modo lectura; no se encontraron acciones de edición habilitadas para ellos.

### Directorio de usuarios

- **Buscar usuario**: por nombre, correo o RUT.
- **Crear/editar usuario**: nombre, correo, RUT, perfil, estamento, sede, firma digital, sexo y fecha de nacimiento.
- Validaciones: nombre requerido, correo requerido/válido/único, RUT opcional con formato chileno y único, sede requerida, perfil requerido, estamento válido si se selecciona, sexo F/M/Otro, fecha válida, firma JPG/PNG/WEBP máximo 1 MB.
- Al crear usuario, se envía correo para configurar contraseña.
- Acciones: editar, cambiar estado, resetear acceso, eliminar.
- Restricción: un administrador no puede gestionarse a sí mismo ni gestionar perfiles de igual o mayor jerarquía; el desarrollador puede gestionar todos.

### Estamentos

- **Crear/editar estamento**: nombre obligatorio, mínimo 3 y máximo 120 caracteres.
- Si el nombre ya existe, muestra error.
- Si existía eliminado, se restaura.
- **Eliminar estamento**: no permite eliminar si tiene colaboradores o cursos asociados.

### Reportes

- Filtros: sedes, estamentos, capacitaciones aprobadas, rango etario, estado de capacitación.
- Estados: Todos, No iniciado, En progreso, Certificado.
- El estado de capacitación se aplica cuando se selecciona una sola capacitación.
- Exportación Excel: permite elegir columnas, ordenarlas, restaurar columnas predeterminadas y usar plantillas.
- Plantillas: crear nombre de plantilla, aplicar plantilla, eliminar plantilla.
- Validaciones de filtros: edades entre 0 y 150, fechas válidas, fecha fin posterior o igual a inicio, sedes/estamentos/cursos existentes.

### Firmas y accesibilidad

- **Firma personal**: carga archivo PNG/JPG/JPEG/WEBP máximo 1 MB. Visible para administradores y capacitadores.
- **Firma institucional**: carga firma del representante legal, PNG/JPG/JPEG/WEBP máximo 1 MB. Visible para administración/desarrollo.
- **Accesibilidad**: tamaño de texto Normal/Grande/Extragrande, alto contraste y reducir movimiento. Se guarda para el usuario autenticado.

### Desarrollo y soporte técnico interno

- **Variables globales**: porcentaje de aprobación entre 0 y 100; intentos máximos semanales entre 1 y 50.
- **Salud LMS**: pestañas Resumen, Jobs, Logs, Datos y performance.
- **Jobs**: búsqueda, rango de horas, reintentar y olvidar job.
- **Logs**: filtro por nivel, búsqueda, eliminación de archivos de log con confirmación.
- **Acciones rápidas**: limpiar caché optimizada y vaciar jobs fallidos, ambas con confirmación.
- **Soporte técnico interno**: búsqueda por asunto, ID o usuario; filtros por estado y asignación; cambio de estado/prioridad; asignarse ticket; responder; responder y resolver; cerrar ticket; adjuntar capturas; nota interna.

## 5. Estados y mensajes del sistema

Mensajes implementados:

- “Las credenciales no coinciden con los registros.”
- Mensajes estándar de recuperación o cambio de contraseña.
- “Modo vista previa activado.”
- “Has vuelto al Panel de Administración.”
- “Has vuelto al Panel del Capacitador.”
- “Curso creado correctamente.”
- “Curso actualizado correctamente.”
- “Curso eliminado correctamente.”
- “Nueva versión del curso creada exitosamente. Ahora puedes editarla.”
- “Módulo creado correctamente.”
- “Módulo actualizado correctamente.”
- “Módulo eliminado correctamente.”
- “Módulo completado.”
- “¡Curso completado!”
- “Esta evaluación no está disponible todavía.”
- “Límite de intentos alcanzado.”
- “Aprobado.”
- “No aprobado.”
- “Certificado generado.”
- “Has agotado tus intentos por esta semana.”
- “Certificado generado para [nombre].”
- “El certificado no se puede generar porque el colaborador o colaboradora aun no aprueba la evaluacion requerida de la capacitacion.”
- “No se pudo generar el certificado. Revisa el avance del trabajador y vuelve a intentarlo.”
- “Estamentos actualizados correctamente.”
- “Sección creada correctamente.”
- “Sección actualizada correctamente.”
- “Sección eliminada. Los módulos asociados ahora están sin sección.”
- “Usuario actualizado exitosamente.”
- “Usuario creado. Se envió un correo para que configure su contraseña.”
- “Estado del usuario actualizado.”
- “Correo de recuperación enviado a [correo].”
- “No se pudo enviar el correo de recuperación.”
- “Usuario eliminado.”
- “Estamento creado/actualizado/restaurado/eliminado correctamente.”
- “No se puede eliminar un estamento asociado a colaboradores o cursos.”
- “Firma personal actualizada correctamente.”
- “Firma institucional actualizada correctamente.”
- “Configuración guardada exitosamente.”
- “Gracias. Tu feedback quedó registrado.”
- “Feedback enviado al equipo de la plataforma.”
- “Ticket #[número] enviado. El equipo técnico lo revisará.”
- “Caché de framework limpiada y auditada.”
- “Jobs fallidos eliminados y acción auditada.”
- En verificación pública: “Certificado válido”, “Este certificado es auténtico”, “No validado”, “No encontramos un certificado con ese código”.

## 6. Restricciones y reglas de negocio visibles

- Los módulos se desbloquean en orden. Si un usuario intenta abrir uno bloqueado, se informa: “Para acceder a este módulo, primero debes completar todas las actividades anteriores de la capacitación.”
- Si entra directamente a un módulo bloqueado, se informa que debe completar los módulos anteriores primero.
- Una capacitación solo se puede ver si está disponible por fecha y sede, salvo vista previa o perfiles autorizados.
- Si el curso aún no inicia, se informa la fecha de disponibilidad cuando existe.
- Si no hay periodo activo, se informa que no hay disponibilidad activa.
- Si el usuario no pertenece al estamento del curso, se informa que no tiene acceso.
- Las evaluaciones tienen límite semanal de intentos.
- Al alcanzar el límite semanal, la evaluación queda bloqueada temporalmente.
- Para aprobar, se usa el porcentaje global de aprobación. Por defecto está en 70%, pero el perfil Desarrollador puede cambiarlo.
- Los intentos semanales por evaluación son configurables. Por defecto están en 3.
- El feedback de capacitación solo aparece cuando el progreso es 100%.
- Un administrador no puede gestionar usuarios de igual o mayor jerarquía.
- Un estamento asociado a colaboradores o cursos no se puede eliminar.
- Los archivos de firma se limitan a imágenes de máximo 1 MB.
- Los tickets de soporte limitan la cantidad de envíos por hora.
- No se encontró inscripción libre de trabajadores a cursos.
- No se encontró edición directa de datos personales del trabajador desde su perfil.
- No se encontró un flujo público de registro de cuenta.

## 7. Flujo de evaluación completo

1. El usuario abre un módulo de tipo evaluación.
2. La pantalla muestra una pregunta a la vez.
3. El usuario selecciona una alternativa.
4. El botón **Continuar** solo se habilita si hay respuesta seleccionada.
5. Puede volver a preguntas anteriores antes de finalizar.
6. Al terminar, se calcula el puntaje.
7. Aprueba si alcanza la cantidad mínima de respuestas correctas según el porcentaje global configurado.
8. Si aprueba, el módulo queda completado.
9. Si al aprobar el curso llega al 100%, se intenta generar el certificado automáticamente.
10. Si reprueba, ve “No aprobado”, su puntaje y los intentos restantes de la semana.
11. Si agota los intentos, ve “Has agotado tus intentos por esta semana.”
12. Si vuelve a entrar sin intentos disponibles, ve “Límite de intentos alcanzado” y debe esperar los próximos días.

No se encontró retroalimentación pregunta por pregunta ni explicación de respuestas correctas/incorrectas al finalizar.

## 8. Flujo del certificado

El certificado se puede obtener de estas formas:

- Automáticamente, cuando el usuario aprueba una evaluación y con eso deja el curso al 100%.
- Manualmente, cuando un capacitador o administrador genera el certificado desde la vista de participantes.

El trabajador accede a sus certificados desde **Certificados** y puede buscar por capacitación o descargar el PDF.

El PDF muestra:

- Nombre del participante.
- Nombre de la capacitación.
- Fecha de emisión.
- Firma del capacitador o capacitadora si está cargada.
- Firma del representante legal si está cargada.
- Código de verificación.
- Código QR para verificar autenticidad.

Existe una verificación pública del certificado por código o QR. Si el código es válido, muestra colaborador/a, capacitación, fecha de emisión y código. Si no existe, muestra que no fue validado.

No se encontró una pantalla para revocar certificados.

## 9. Panel administrativo

### Dashboard analítico

Permite filtrar por año, sede, estamento y capacitación. Tiene vistas:

- **Resumen**: tendencia de certificados, indicadores generales y sedes con mayor alcance.
- **Progreso**: embudo de formación, cursos críticos, usuarios en riesgo y cumplimiento.
- **Calidad**: aprobaciones, intentos, feedback y satisfacción.
- **Segmentos**: cobertura por sede, composición por estamento, edad y colaboradores con señales accionables.

### Reportes de capacitación

Permite filtrar por sedes, estamentos, capacitaciones, edad y estado. Muestra una tabla de colaboradores con datos personales institucionales, capacitaciones, progreso y estado cuando corresponde. Permite exportar a Excel con columnas configurables y plantillas.

### Directorio de usuarios

Permite buscar, crear, editar, activar/desactivar, enviar recuperación de contraseña y eliminar usuarios. Muestra nombre, correo, RUT, perfil, estamento, sede y estado.

### Estamentos

Permite crear, editar y eliminar estamentos. Muestra cantidad de usuarios y cursos asociados. Bloquea eliminación si hay asociaciones.

### Capacitaciones y material

Permite administrar capacitaciones, módulos, secciones, evaluaciones, participantes y certificados. El administrador ve todas las capacitaciones.

### Calendario institucional

Permite ver planificación anual o mensual. El administrador puede activar edición anual, arrastrar capacitaciones, crear rangos, mover bloques, ajustar duración, eliminar bloques y copiar planificaciones de un año a otro.

### Perfil y firma

Permite revisar datos del usuario del panel y cargar firma personal para certificados.

### Firma institucional

Permite cargar o actualizar la firma del representante legal usada en certificados.

### Secciones exclusivas del desarrollador

- **Lógica de negocio**: porcentaje de aprobación e intentos semanales.
- **Salud LMS**: monitoreo operacional, jobs, logs, datos, rendimiento, caché y acciones rápidas.
- **Soporte técnico**: administración de tickets.

## 10. Recomendaciones técnicas para el usuario

No se encontró una recomendación explícita de navegador.

No se encontró una restricción explícita de dispositivo. La interfaz implementada incluye navegación para escritorio y móvil, y menús inferiores en la experiencia del trabajador.

Comportamientos que el usuario debe conocer:

- La plataforma usa navegación con cambios visuales y animaciones; existe opción **Reducir movimiento**.
- Existe opción **Alto contraste** y tres tamaños de texto.
- En evaluaciones, los intentos son semanales y pueden agotarse.
- Algunas capacitaciones dependen de fecha, sede y estamento.
- Los módulos se desbloquean en orden.
- Los certificados pueden validarse públicamente mediante código o QR.
- Los archivos adjuntos de soporte deben ser imágenes y tienen límite de tamaño.
