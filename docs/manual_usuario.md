# Manual de Usuario Alumco LMS

Este manual describe los flujos visibles de la plataforma para los perfiles **Colaboradora** y **Administrador**.

## Perfil: Colaboradora

### 1. Acceso

#### Iniciar sesión

1. Abre la pantalla de ingreso.
2. En **Correo electrónico**, escribe tu correo.
3. En **Contraseña**, escribe tu clave.
4. Si quieres mantener la sesión en el dispositivo, marca **Recordarme en este dispositivo**.
5. Si necesitas revisar lo que escribiste, haz clic en el icono de ojo del campo de contraseña.
6. Haz clic en **Acceder al Portal**.
7. El sistema mostrará tu área de trabajo con **Mis capacitaciones**.

Si hay un error, el sistema mostrará el mensaje bajo el campo correspondiente. Si no puedes ingresar, haz clic en **Contacta a soporte**.

#### Recuperar contraseña

1. En la pantalla de ingreso, haz clic en **¿Olvidaste tu contraseña?**.
2. El sistema mostrará **Recuperar contraseña**.
3. Escribe tu correo en **Correo electrónico**.
4. Haz clic en **Enviar enlace de recuperación**.
5. Revisa tu correo y abre el enlace recibido.
6. En la pantalla **Configura tu contraseña**, confirma tu correo.
7. Escribe una **Nueva contraseña** de al menos 8 caracteres.
8. Repite la clave en **Confirmar contraseña**.
9. Haz clic en **Guardar nueva contraseña**.
10. Vuelve al inicio de sesión e ingresa con tu nueva clave.

### 2. Perfil y accesibilidad

#### Entrar al perfil

1. Desde la barra superior, haz clic en tu avatar o nombre.
2. El sistema mostrará **Mi perfil** con tu nombre, correo, sede, estamento y resumen de avance.
3. En la sección **Preferencias de accesibilidad**, ajusta la interfaz.

#### Cambiar tamaño de texto

1. En **Tamaño de texto**, selecciona una opción:
   - **Normal**
   - **Grande**
   - **Extragrande**
2. El sistema aplicará el cambio de inmediato y guardará la preferencia en tu cuenta.

El sistema usa el atributo `data-font` para reflejar el tamaño activo:

| Opción | Valor detectado |
| --- | --- |
| Normal | `data-font="0"` |
| Grande | `data-font="1"` |
| Extragrande | `data-font="2"` |

#### Activar alto contraste

1. En **Alto contraste**, activa el interruptor.
2. El sistema reforzará bordes, texto y fondos.
3. Para volver a la apariencia normal, desactiva el mismo interruptor.

El sistema usa el atributo `data-contrast` para reflejar el contraste activo:

| Opción | Valor detectado |
| --- | --- |
| Contraste normal | `data-contrast="default"` |
| Alto contraste | `data-contrast="high"` |

También puedes activar **Reducir movimiento** para disminuir transiciones y animaciones.

### 3. Experiencia de aprendizaje

#### Entrar a una capacitación

1. En el menú principal, haz clic en **Mis capacitaciones**.
2. Revisa el resumen de capacitaciones **Vigentes**, **En proceso**, **Completos** y **Pasados**.
3. En **Capacitaciones vigentes**, elige una tarjeta.
4. Haz clic en **Comenzar capacitación** o **Continuar capacitación**.
5. El sistema mostrará la portada, descripción, nota del capacitador si existe, progreso actual y el **Programa de la capacitación**.

#### Avanzar por módulos

1. En el programa, revisa las etapas y actividades.
2. Haz clic en un módulo con estado **Disponible**.
3. Revisa el contenido: video, documento, presentación, imagen o texto.
4. Cuando termines, haz clic en **Listo, siguiente**.
5. Si es el último módulo, haz clic en **Finalizar capacitación**.
6. Si quieres regresar al programa, haz clic en **Volver a la capacitación**.

#### Lógica de bloqueo secuencial

La capacitación se completa en orden. El primer módulo está disponible desde el inicio. Los siguientes módulos se desbloquean solo cuando completas el módulo anterior.

Si haces clic en un módulo bloqueado, el sistema mostrará:

> Para acceder a este módulo, primero debes completar todas las actividades anteriores de la capacitación.

En la pantalla verás los estados:

| Estado | Qué significa |
| --- | --- |
| **Disponible** | Puedes entrar al módulo. |
| **Completado** | Ya terminaste esa actividad. |
| **Bloqueado** | Debes completar la actividad anterior. |

#### Rendir evaluaciones

1. Entra al módulo marcado como **evaluación**.
2. El sistema mostrará **Una alternativa por pregunta**.
3. Lee la pregunta actual.
4. Selecciona una alternativa.
5. Haz clic en **Continuar**.
6. Si necesitas corregir antes de terminar, haz clic en **Anterior**.
7. En la última pregunta, haz clic en **Continuar** para finalizar.
8. El sistema mostrará tu resultado como **Aprobado** o **No aprobado**.

Para aprobar, necesitas alcanzar el umbral de **70% de respuestas correctas**. El sistema convierte ese porcentaje en la cantidad mínima de respuestas correctas necesarias para la evaluación.

Si no apruebas, el sistema mostrará cuántos intentos te quedan disponibles esta semana. Si alcanzas el límite semanal, verás **Límite de intentos alcanzado** y podrás intentarlo nuevamente en los próximos días.

### 4. Certificación

#### Requisitos para obtener certificado

Para obtener el certificado debes:

1. Completar el **100%** de la capacitación.
2. Aprobar la evaluación correspondiente con al menos **70%** de respuestas correctas cuando la capacitación incluya evaluación.

Al aprobar una evaluación, el módulo queda completado. Si con eso alcanzas el 100% del curso, el sistema intentará generar el certificado automáticamente y mostrará **Certificado generado**.

#### Descargar el PDF

1. Haz clic en **Certificados** en el menú principal.
2. El sistema mostrará **Mis certificados**.
3. Si tienes certificados, usa el buscador **Buscar por capacitación** si necesitas filtrar.
4. En la tarjeta del certificado, haz clic en **Descargar PDF**.
5. El sistema descargará un archivo PDF con el nombre del certificado.

También puedes descargarlo desde:

- La tarjeta de una capacitación pasada si aparece **Descargar Certificado**.
- Tu perfil, en **Certificados recientes**, con el botón **Descargar**.

#### Verificar un certificado con QR

1. Abre el PDF descargado.
2. Escanea el código QR que aparece en el certificado.
3. El sistema abrirá la pantalla pública **Verificar certificado**.
4. Si el certificado es válido, se mostrará **Certificado válido** y **Este certificado es auténtico**.
5. La pantalla mostrará colaboradora, capacitación, fecha de emisión y código de verificación.

Si no puedes escanear el QR:

1. Abre la pantalla **Verificar certificado**.
2. Escribe el código de verificación impreso en el PDF.
3. Haz clic en **Buscar**.
4. El sistema mostrará si el documento es auténtico o si no encontró un certificado con ese código.

## Perfil: Administrador

### 1. Acceso

1. Abre la pantalla de ingreso.
2. Escribe tu correo en **Correo electrónico**.
3. Escribe tu clave en **Contraseña**.
4. Haz clic en **Acceder al Portal**.
5. El sistema te llevará al panel administrativo.

Si olvidaste la clave, usa el mismo flujo de recuperación descrito para Colaboradora: **¿Olvidaste tu contraseña?**, correo, enlace de recuperación y nueva clave.

### 2. Perfil y accesibilidad

#### Abrir opciones de accesibilidad

1. En la barra superior del panel, haz clic en **Opciones**.
2. El sistema abrirá el panel **Preferencias de accesibilidad**.
3. Ajusta **Tamaño de texto**, **Alto contraste** o **Reducir movimiento**.
4. Cierra el panel con el botón de cerrar.

En pantallas pequeñas, el acceso aparece como un botón con icono en la barra superior.

#### Cambiar tamaño y contraste

1. En **Tamaño de texto**, elige **Normal**, **Grande** o **Extragrande**.
2. Activa **Alto contraste** si necesitas mayor legibilidad.
3. El sistema guarda la preferencia en tu cuenta y la aplica en el panel.

Valores detectados:

| Ajuste | Valores del sistema |
| --- | --- |
| Tamaño de texto | `data-font="0"`, `data-font="1"`, `data-font="2"` |
| Contraste | `data-contrast="default"` o `data-contrast="high"` |

### 3. Gestión administrativa: Dashboard de BI

#### Entrar al Dashboard analítico

1. En el panel lateral, abre **Estadísticas**.
2. Haz clic en **Dashboard analítico**.
3. El sistema mostrará **Monitoreo y Métricas** y el título **Dashboard analítico**.

#### Aplicar filtros

1. En la sección de filtros, selecciona **Año de planificación**.
2. En **Sede Institucional**, selecciona una sede o deja **Todas las sedes**.
3. En **Estamento / Rol**, selecciona un estamento o deja **Todos los estamentos**.
4. En **Capacitación específica**, selecciona una capacitación o deja **Todas las capacitaciones**.
5. El sistema actualizará automáticamente los indicadores y gráficos.
6. Revisa los chips bajo **Filtros activos** para confirmar el alcance del análisis.
7. Para volver a la vista inicial, haz clic en el botón de restablecer filtros.

#### Cambiar vista del Dashboard

Usa las pestañas del dashboard:

| Vista | Qué muestra |
| --- | --- |
| **Resumen** | Cobertura anual, base activa, planificaciones, oferta vigente, tendencia de certificación y ranking de sedes. |
| **Progreso** | Usuarios que iniciaron, módulos completados, usuarios sin inicio, casos que requieren seguimiento, cursos críticos y embudo de formación. |
| **Calidad** | Tasa de aprobación, satisfacción, intentos aprobados/reprobados, feedback y relación entre avance y valoración. |
| **Segmentos** | Cobertura por sede, composición por estamento, rango etario y analítica individual de colaboradoras. |

#### Ir desde el Dashboard a reportes

1. En la cabecera de filtros del Dashboard, haz clic en **Ver reportes**.
2. El sistema abrirá **Reportes de capacitación**.

### 4. Gestión administrativa: exportar reportes a Excel

#### Filtrar el reporte

1. En el panel lateral, entra a **Estadísticas**.
2. Haz clic en **Reportes de capacitación**.
3. En **Sedes**, abre el selector y marca una o más sedes. Usa **Buscar...** si la lista es larga.
4. En **Estamentos**, marca uno o más estamentos.
5. En **Capacitaciones aprobadas**, marca una o más capacitaciones.
6. En **Rango Etario**, mueve los controles para definir edad mínima y máxima.
7. En **Estado capacitación**, selecciona:
   - **Todos**
   - **No iniciado**
   - **En progreso**
   - **Certificado**
8. Haz clic en **Aplicar Filtros**.
9. El sistema mostrará la cantidad de **registros encontrados** y actualizará la tabla.

Nota: el filtro **Estado capacitación** se aplica cuando seleccionas una sola capacitación.

#### Exportar a Excel

1. Con los filtros ya definidos, haz clic en **Exportar Excel**.
2. El sistema abrirá **Configurar Exportación**.
3. En el paso **Seleccionar columnas a incluir**, activa o desactiva las columnas que quieres exportar.
4. Las columnas disponibles son:
   - **RUT**
   - **Nombre completo**
   - **Sexo**
   - **Edad**
   - **Correo**
   - **Sede**
   - **Estamento**
   - **Capacitaciones aprobadas**
   - **Estado capacitación**
   - **Progreso (%)**
   - **Feedback capacitación**
5. En **Vista previa y orden de columnas**, arrastra las columnas para cambiar el orden.
6. Si quieres volver al formato inicial, haz clic en **Restaurar**.
7. Si tienes una plantilla guardada, abre **Plantillas** y selecciona el formato.
8. Para guardar un nuevo formato, haz clic en **Nueva Plantilla**, escribe un nombre y confirma con **OK**.
9. Cuando el orden y las columnas estén correctos, haz clic en **Generar Reporte Excel**.
10. El sistema descargará el archivo **reporte_capacitaciones.xlsx**.

El Excel respeta los filtros aplicados y el orden de columnas configurado de izquierda a derecha. Si no seleccionas ninguna columna, el botón **Generar Reporte Excel** queda deshabilitado.

