# Cumplimiento del análisis heurístico de Alumco LMS

## Resumen no técnico

Alumco LMS fue revisado con las dos rúbricas entregadas: **“Base análisis heurístico - UDD”** y **“Base análisis heurístico - Diplomado UX UI Agile USACH”**. Ambas contienen los mismos 52 criterios de las diez heurísticas de Nielsen. La evaluación tradujo las referencias comerciales de la planilla —compras, productos, cotizaciones, equipos y planes— a las tareas reales del LMS: crear y planificar capacitaciones, consumir módulos, rendir evaluaciones, comparar avance, exportar reportes y obtener certificados.

El resultado combina componentes comunes, ayuda segmentada por rol, eliminación recuperable, historial transaccional, validación accesible, estados explícitos, navegación coherente y controles de auditoría. La aplicación no contiene publicidad; ese criterio se cumple por ausencia de anuncios. La comparación se resuelve con dashboards y reportes en una misma pantalla. La personalización se cubre con preferencias de accesibilidad, filtros persistentes, formatos de reporte guardados y atajos de teclado.

La matriz usa la misma referencia para **UDD** y **USACH**, porque el enunciado y el orden son idénticos. `.nopush` se conserva sin modificaciones y fuera de Git.

## Cambios y razones por heurística

1. **Visibilidad del estado.** Alertas con icono, texto, región ARIA y acción siguiente; indicadores de guardado, navegación, carga, generación y procesamiento con controles bloqueados mientras trabajan.
2. **Coincidencia con el mundo real.** Terminología centrada en capacitaciones, módulos, avance, evaluación, reportes y certificados; ayuda escrita por tareas.
3. **Control y libertad.** Deshacer/Rehacer visible en estructura, evaluaciones, calendario y formatos de reporte; 20 pasos por 30 minutos, atajos y detección de conflictos.
4. **Coherencia y estándares.** Logo con destino de inicio según rol, navegación común, botones, alertas, diálogos y estados compartidos.
5. **Prevención de errores.** Eliminación lógica, confirmación accesible, validación de servidor, resumen enfocado y auditoría de rutas internas.
6. **Reconocer en vez de recordar.** Ubicación, regreso, selecciones visibles, filtros en URL y resúmenes en contexto.
7. **Flexibilidad y eficiencia.** Atajos Ctrl/Cmd+Z, Ctrl/Cmd+Shift+Z, preferencias, formatos guardados y paneles comparativos.
8. **Estética minimalista.** Jerarquía, espacio, contraste AA, recursos responsivos, reducción de movimiento y ausencia de anuncios.
9. **Recuperación de errores.** Mensajes con problema y solución; datos conservados; eliminación y edición recuperables; conflictos sin sobrescritura.
10. **Ayuda y documentación.** `/ayuda` y `/ayuda/{tema}`, búsqueda, permisos por rol, pasos concretos, enlaces contextuales y soporte en línea.

## Evidencia reproducible

- `php artisan lms:audit-heuristics`: rutas, enlaces Blade, eliminación lógica, estados de carga, confirmaciones, movimiento reducido y cierre de 52 filas.
- `tests/Feature/HelpCenterTest.php`: contenido público y autorizado por rol, búsqueda y estado vacío.
- `tests/Feature/EditHistoryTest.php`: ciclos completos, límite y vencimiento, invalidación de Rehacer, permisos implícitos y concurrencia sin sobrescritura.
- `tests/Feature/AccessibilityAuditTest.php` y `tests/Feature/AccessibilityPreferencesTest.php`: estructura, nombres accesibles y preferencias.
- `npm run build && npm run audit:assets`: compilación Vite; CSS interno 23,9 KB gzip, CSS público 7,6 KB gzip y JavaScript inicial 20,2 KB gzip.
- `npm run test:e2e`: Playwright + Axe para público, colaborador, capacitador, administrador y desarrollador en Chromium, Firefox y WebKit, usando 375×667, 768×1024, 1024×768 y 1440×900; incluye foco, validación, objetivos táctiles y rastreo de enlaces.
- `npm run audit:lighthouse`: medianas de tres ejecuciones. Login: móvil 99/LCP 1,95 s y escritorio 100/LCP 0,44 s; ayuda: móvil 100/LCP 1,65 s y escritorio 100/LCP 0,36 s; certificados: móvil 99/LCP 1,80 s y escritorio 100/LCP 0,36 s. CLS y TBT medianos fueron 0 en todas las rutas.
- `docs/audits/screenshots/`: capturas versionadas de login, ayuda, cursos, estructura, evaluación, certificados, calendarios, reportes, usuarios y salud LMS.

## Matriz consolidada UDD + USACH

| N | Heurística | Enunciado original (UDD y USACH) | Equivalencia LMS y situación anterior | Solución y evidencia | Resultado final |
|---:|---|---|---|---|---|
| 1 | Visibilidad | Se comunica claramente al usuario todo lo que ocurre en las acciones a lo largo de los flujos | Guardados, cargas y envíos podían usar feedback desigual. | `x-alert`, `x-saving-indicator`, progreso de navegación y textos de proceso específicos. | 0 — Sin problemas |
| 2 | Visibilidad | El sistema genera confianza comunicando claramente lo que ocurre en cada acción | Algunas acciones sólo cambiaban la pantalla. | Regiones `status`/`alert`, mensajes comprensibles y acción siguiente. | 0 — Sin problemas |
| 3 | Visibilidad | Botones significativos al posar puntero del mouse sobre ellos | Varios iconos dependían sólo del dibujo. | Nombre visible o ARIA, `title`, hover, foco y objetivos de 44×44 px. | 0 — Sin problemas |
| 4 | Visibilidad | Se identifican todas las secciones en toda la navegación del sitio | Ayuda no figuraba en la arquitectura. | Navegación por rol, ubicación actual, ayuda y soporte. | 0 — Sin problemas |
| 5 | Visibilidad | El tiempo de respuesta es razonable a la acción | Las esperas breves podían parecer inactividad. | Barra de navegación, skeleton diferido, bloqueo de duplicados y estados estáticos con movimiento reducido. | 0 — Sin problemas |
| 6 | Visibilidad | Al contratar un producto o servicio se informa al usuario que ha completado la acción | Equivale a crear, planificar, evaluar, exportar o certificar. | Confirmación explícita en cada operación y acción siguiente cuando aplica. | 0 — Sin problemas |
| 7 | Visibilidad | Se identifica el correcto ingreso del usuario en el login | El redireccionamiento era la única señal. | Estado de autenticación y llegada al inicio canónico del rol; errores conservan correo. | 0 — Sin problemas |
| 8 | Mundo real | La forma de presentar el contenido es familiar y entendible para el usuario | Había términos técnicos dispersos. | Jerarquía por capacitación, módulo, evaluación, avance y certificado. | 0 — Sin problemas |
| 9 | Mundo real | Las funcionalidades tienen un nombre apropiado para que el usuario lo entienda según la idea previa que tiene de éste | Etiquetas administrativas no siempre explicaban la tarea. | Nombres orientados a acciones y guías contextuales. | 0 — Sin problemas |
| 10 | Mundo real | La terminología utlizada es acorde al lenguaje cultural del usuario | Mensajes y etiquetas no estaban centralizados. | Español de Chile consistente, lenguaje breve y sin códigos técnicos. | 0 — Sin problemas |
| 11 | Control | Existe facilidad al ejecutar las acciones en el sitio | Flujos editables no compartían recuperación. | Acciones visibles, estados bloqueados sólo al procesar y controles comunes. | 0 — Sin problemas |
| 12 | Control | Se visibilizan las acciones de Deshacer y Rehacer. | No existía historial transversal. | `EditHistoryService`, controles en cuatro flujos, 20 pasos/30 min y atajos. | 0 — Sin problemas |
| 13 | Control | Muestra una forma clara de salir de la acción actual, como un botón Cancelar | Confirmaciones usaban patrones distintos. | Diálogo común con Cancelar, Escape, foco atrapado y restaurado. | 0 — Sin problemas |
| 14 | Control | Asegura de que la salida esté claramente etiquetada y sea visible | Algunos cierres eran sólo iconos. | Etiquetas accesibles y botones de cancelar visibles. | 0 — Sin problemas |
| 15 | Control | El sitio no debe iniciar procesos sin que el usuario los seleccione explícitamente | Había precarga automática, pero no mutaciones automáticas. | Sólo se precargan lecturas; toda escritura exige acción expresa. | 0 — Sin problemas |
| 16 | Control | El sitio siempre cuenta con una opción de regreso a la página de inicio | El destino del logo variaba. | Logo enlazado al inicio canónico por rol y regreso en ayuda/errores. | 0 — Sin problemas |
| 17 | Control | El sitio no debe incluir opciones predeterminadas que pasen inadvertidas para el usuario | Filtros y valores iniciales podían pasar desapercibidos. | Valores seleccionados visibles, restauración explícita y filtros serializados. | 0 — Sin problemas |
| 18 | Coherencia | Logo visible e identificable | En login no era enlace. | Logo Alumco con alternativa y destino de inicio en todas las superficies. | 0 — Sin problemas |
| 19 | Coherencia | Identidad y consistencia en las páginas en relación a flujo y arquitectura | Componentes aislados generaban variaciones. | Layouts y componentes comunes, identidad Alumco conservada. | 0 — Sin problemas |
| 20 | Coherencia | Los símbolos utilizados son posibles de identificar por el usuario y facilitan la interacción con el sitio | Iconos aislados carecían de explicación uniforme. | Texto/ARIA, tooltip en hover y foco, iconos decorativos ocultos. | 0 — Sin problemas |
| 21 | Coherencia | Se dispone de un menú principal con todos los servicios | Ayuda no estaba presente. | Menús por rol con capacitaciones, calendario, certificados, soporte, ayuda y administración autorizada. | 0 — Sin problemas |
| 22 | Coherencia | Los elementos del sistema son ubicados de manera estándar para el fácil reconocimiento del usuario | Acciones equivalentes variaban de posición. | Cabeceras, acciones, alertas, diálogos e historial comparten patrón. | 0 — Sin problemas |
| 23 | Prevención | Existen enlaces rotos | No había rastreo reproducible. | `lms:audit-heuristics` valida rutas literales Blade registradas. | 0 — Sin problemas |
| 24 | Prevención | Existen enlaces que dirigen a otras páginas que no es el propuesto | Algunas rutas legacy podían confundir. | Redirecciones canónicas y texto/destino alineados por pruebas de ruta. | 0 — Sin problemas |
| 25 | Prevención | Existe un sistema de validación antes de que el usuario envie información para tratar de evitar errores | Validación existía, pero su presentación variaba. | Form Requests/Livewire, resumen enfocado, `aria-invalid` y datos conservados. | 0 — Sin problemas |
| 26 | Prevención | Se ofrece ayuda contextual en tareas complejas | Sólo existía soporte genérico. | Enlaces a tema exacto y pasos para acceso, contenido, evaluación, calendario y reportes. | 0 — Sin problemas |
| 27 | Prevención | El sitio destaca los campos obligatorios a completar y cómo se debe hacer cuando el usuario debe llenar campos | No todos comunicaban obligación o formato. | Asterisco con texto para lector, formato esperado, error cercano y resumen. | 0 — Sin problemas |
| 28 | Reconocimiento | Se visibiliza en qué etapa del sitio se encuentra el usuario | La ubicación dependía del título de cada vista. | Navegación activa, encabezados, regreso y migas en ayuda. | 0 — Sin problemas |
| 29 | Reconocimiento | Se minimiza el uso de memoria haciendo que los objetos, acciones y opciones resulten claramente visibles | Opciones frecuentes podían quedar dentro de modales. | Acciones principales, estado, filtros e historial permanecen visibles. | 0 — Sin problemas |
| 30 | Reconocimiento | Durante un proceso de compra o cotización las opciones seleccionadas previamente por el usuario, como servicio o producto, son destacadas claramente en la sección del paso en donde se encuentre. | Equivale a capacitación, sede, fechas, respuestas, filtros y columnas elegidas. | Selecciones se muestran en el paso actual y se conservan ante error. | 0 — Sin problemas |
| 31 | Flexibilidad | El sistema reconoce acciones habituales y entrega atajos para ejecutar dicha acción | No había atajos de recuperación. | Ctrl/Cmd+Z y Ctrl/Cmd+Shift+Z fuera de campos de texto. | 0 — Sin problemas |
| 32 | Flexibilidad | Permite al usuario personalizar funciones frecuentes del sistema | Personalización estaba dispersa. | Fuente, contraste, movimiento, formatos guardados y filtros persistentes. | 0 — Sin problemas |
| 33 | Flexibilidad | Cuando se hace comparación de equipos y planes la información es presentada en la misma pantalla, evitando que deba recordar información de uno u otro | Equivale a comparar sedes, cursos, avance y certificación. | Dashboards, tablas y reportes comparan métricas en una misma pantalla con resumen textual. | 0 — Sin problemas |
| 34 | Estética | Diseño de contenido corresponde a las acciones que ejecuta el usuario | Algunas tarjetas y decoraciones competían con la tarea. | Superficies y CTA jerarquizados por rol y tarea. | 0 — Sin problemas |
| 35 | Estética | Se reconoce qué tipo de información y/o contenido necesita el usuario y se jerarquiza | Densidad administrativa podía ocultar lo principal. | Encabezado, resumen, acción primaria y detalle secundario consistentes. | 0 — Sin problemas |
| 36 | Estética | Se evita la sobrecarga de contenido irrelevante o rara vez utilizada por el usuario | Ayuda técnica podía filtrarse a otros roles. | Menús y documentación autorizados por rol; herramientas técnicas sólo a desarrollador. | 0 — Sin problemas |
| 37 | Estética | Existen espacios en blanco para descansar la vista | Algunas pantallas densas anidaban tarjetas. | Espaciado y densidad compartidos, grillas responsive y paneles sin anidación innecesaria. | 0 — Sin problemas |
| 38 | Estética | Existe contraste entre el color de la fuente y el fondo | Colores de marca claros no siempre sirven como texto. | Variantes accesibles, alto contraste y foco amarillo; estado no depende sólo del color. | 0 — Sin problemas |
| 39 | Estética | Se presentan gráficas con correcta resolución (desktop o mobile) | Gráficos necesitaban adaptación de tamaño. | Canvas responsive, carga diferida, placeholders con texto y paneles ajustables. | 0 — Sin problemas |
| 40 | Estética | La publicidad no es invasiva para el usuario | El LMS no usa publicidad. | Cumplimiento por ausencia de anuncios y rastreadores publicitarios. | 0 — Sin problemas |
| 41 | Errores | Los mensajes de errores están expresados en lenguaje entendible | Algunos errores podían mostrar contexto insuficiente. | Mensajes en español describen problema y próximo paso. | 0 — Sin problemas |
| 42 | Errores | Se indica con precisión cuál es el problema | Resumen y campo no siempre estaban conectados. | Error cercano, `aria-describedby`, `aria-invalid` y resumen de errores. | 0 — Sin problemas |
| 43 | Errores | Se facilita la experiencia cuando suceden errores (mensaje amigable) | Las páginas de error ofrecían regreso, pero no ayuda. | Páginas amistosas con inicio, ayuda y soporte; formularios conservan datos. | 0 — Sin problemas |
| 44 | Errores | Se da la posibilidad de revertir el error en caso de equivocación (acción concreta, botón) | Eliminaciones y ediciones principales no eran recuperables. | Deshacer visible, eliminación lógica y purga sólo después de 30 días. | 0 — Sin problemas |
| 45 | Errores | Está clara esa posibilidad (de revertir; te sugiere constructivamente una solución) | No había explicación de vigencia o conflicto. | Etiqueta del cambio, disponibilidad, 30 minutos y mensaje de concurrencia con solución. | 0 — Sin problemas |
| 46 | Ayuda | Existe un enlace de sección de ayuda | No existía centro unificado. | Enlace “Ayuda” en login, menús, errores y soporte. | 0 — Sin problemas |
| 47 | Ayuda | Se ofrece ayuda contextual en tareas complejas | La documentación no apuntaba al paso exacto. | `/ayuda/{tema}#pasos` y enlaces desde tareas complejas. | 0 — Sin problemas |
| 48 | Ayuda | La documentación es fácil de encontrar | Sólo había documentación técnica del repositorio. | Centro buscable, navegación principal y búsqueda por tarea. | 0 — Sin problemas |
| 49 | Ayuda | La información de respaldo se focaliza en las tareas que ejecuta para el usuario | Manuales técnicos mezclaban audiencias. | Temas segmentados para público, colaborador, capacitador, administrador y desarrollador. | 0 — Sin problemas |
| 50 | Ayuda | Enlista pasos concretos de acción | No había formato común. | Cada tema presenta lista numerada y verificable. | 0 — Sin problemas |
| 51 | Ayuda | La información es concreta | La ayuda podía depender de textos largos. | Resumen, pasos breves, recomendación y enlace de retorno. | 0 — Sin problemas |
| 52 | Ayuda | El sistema ofrece ayuda en línea | Había tickets, pero no estaban integrados con documentación. | Soporte público/autenticado enlazado desde cada tema. | 0 — Sin problemas |

## Cierre

Los 52 criterios de las rúbricas UDD y USACH quedan trazados a una solución y evidencia concreta con severidad final **0 — Sin problemas**. Las acciones destructivas técnicas que no pueden revertirse mantienen confirmación explícita y Cancelar; las operaciones de contenido conservan recuperación. El comando de auditoría y las pruebas automatizadas deben ejecutarse en integración continua junto con la compilación Vite y los recorridos de navegador.
