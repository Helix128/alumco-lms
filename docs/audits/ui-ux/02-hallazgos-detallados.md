# Hallazgos Detallados - Auditoria UI/UX Alumco LMS

## Tabla de Contenidos
- [Resumen por Categoria](#resumen-por-categoria)
- [Categoria: Accesibilidad](#categoria-accesibilidad)
- [Categoria: Experiencia de Usuario](#categoria-experiencia-de-usuario)
- [Categoria: Diseno Visual](#categoria-diseno-visual)
- [Categoria: Mobile](#categoria-mobile)
- [Categoria: Rendimiento](#categoria-rendimiento)

## Resumen por Categoria

| Categoria | P0 | P1 | P2 | P3 | Total |
|-----------|----|----|----|----|-------|
| Accesibilidad | 2 | 1 | 2 | 0 | 5 |
| UX | 0 | 2 | 1 | 0 | 3 |
| Diseno Visual | 0 | 0 | 2 | 1 | 3 |
| Mobile | 0 | 1 | 0 | 0 | 1 |
| Rendimiento | 0 | 1 | 0 | 0 | 1 |

---

## CATEGORIA: Accesibilidad

### Hallazgo H001: Modales sin gestion completa de foco

**Prioridad:** P0 | **Impacto:** Alto | **Esfuerzo:** Medio | **Area:** `partials/accessibility-modal.blade.php`, `layouts/user.blade.php`

#### Descripcion del Problema
El modal de opciones de accesibilidad declara `role="dialog"` y `aria-modal="true"`, pero no se observa una gestion explicita de foco inicial, trampa de foco ni restauracion al boton que abrio el dialog.

En modales, `aria-modal` comunica intencion semantica, pero no implementa por si solo el comportamiento de teclado. El usuario puede quedar desorientado si el foco permanece en contenido de fondo.

**Ejemplo concreto:**
- Ubicacion: `resources/views/partials/accessibility-modal.blade.php`
- Contexto: usuario abre "Opciones" y navega con Tab.
- Resultado no deseado: el foco puede no entrar de forma predecible al dialog o no volver al disparador.

#### Impacto en Usuarios
**Usuarios afectados:**
- Personas que usan teclado: pierden el orden de navegacion.
- Personas que usan lector de pantalla: pueden escuchar contenido fuera del modal.

**Severidad del impacto:**
- Bloqueo potencial en una funcionalidad de accesibilidad.
- Frecuencia alta porque el modal esta disponible globalmente para usuarios autenticados.

#### Recomendacion Especifica
**Accion principal:** Implementar gestion de foco en modales Alpine o extraer un componente modal reutilizable.

**Pasos concretos:**
1. Guardar referencia del elemento disparador antes de abrir.
2. Enfocar el titulo o primer control interactivo al abrir.
3. Encerrar Tab/Shift+Tab dentro del modal.
4. Restaurar foco al disparador al cerrar.

**Codigo referencial:**
```html
<div role="dialog" aria-modal="true" aria-labelledby="accessibility-title">
  <h2 id="accessibility-title" tabindex="-1">Opciones de accesibilidad</h2>
</div>
```

#### Consideraciones de Implementacion
- **Stack afectado:** Blade, Alpine, Livewire.
- **Compatibilidad:** Desktop y mobile; teclado fisico en tablets.
- **Dependencias:** Ninguna.
- **Testing necesario:** Navegacion Tab, Shift+Tab, Escape y lector de pantalla.

#### Criterio de Aceptacion
- [ ] Al abrir, el foco entra al modal.
- [ ] Tab no sale del modal mientras esta abierto.
- [ ] Escape cierra y devuelve foco al boton original.
- [ ] Axe no reporta errores de dialog.

#### Estandares Relacionados
- WCAG 2.1 SC 2.1.1 Teclado.
- WCAG 2.1 SC 2.4.3 Orden del foco.
- ARIA Authoring Practices: Dialog Modal Pattern.

---

### Hallazgo H002: Reduccion de movimiento incompleta

**Prioridad:** P0 | **Impacto:** Alto | **Esfuerzo:** Bajo | **Area:** `resources/css/app.css`

#### Descripcion del Problema
La aplicacion guarda `data-motion="reduced"` en el documento, pero hay animaciones y transiciones globales como shimmer, cloud, entrada de pagina, transformaciones hover y transiciones de dialog.

La preferencia existe, pero debe tener efecto consistente sobre animaciones no esenciales.

**Ejemplo concreto:**
- Ubicacion: `resources/css/app.css`
- Contexto: usuario activa "Reducir movimiento".
- Resultado no deseado: skeleton shimmer, page entry o transiciones siguen visibles.

#### Impacto en Usuarios
**Usuarios afectados:**
- Personas con sensibilidad al movimiento.
- Usuarios mobile en dispositivos de baja potencia.

**Severidad del impacto:**
- Puede causar malestar fisico.
- Frecuencia media-alta por uso transversal del layout.

#### Recomendacion Especifica
**Accion principal:** Agregar reglas globales para `data-motion="reduced"` y `prefers-reduced-motion: reduce`.

**Pasos concretos:**
1. Desactivar animaciones no esenciales.
2. Reducir transiciones a 0.01ms o eliminarlas en componentes globales.
3. Mantener indicadores de estado sin movimiento.

**Codigo referencial:**
```css
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    scroll-behavior: auto !important;
    transition-duration: 0.01ms !important;
  }
}

[data-motion="reduced"] .nav-skeleton__row {
  animation: none;
}
```

#### Criterio de Aceptacion
- [ ] Preferencia guardada detiene animaciones no esenciales.
- [ ] `prefers-reduced-motion` se respeta aunque el usuario no haya guardado preferencia.
- [ ] No se pierde feedback de carga.

---

### Hallazgo H003: Barras de progreso sin semantica suficiente

**Prioridad:** P1 | **Impacto:** Alto | **Esfuerzo:** Bajo | **Area:** `resources/views/cursos/show.blade.php`

#### Descripcion del Problema
El progreso del curso y de secciones se representa visualmente con barras y circulos SVG. En el codigo revisado no se observa `role="progressbar"` con valores ARIA.

**Ejemplo concreto:**
- Ubicacion: vista de curso.
- Contexto: trabajador revisa avance.
- Resultado no deseado: lector de pantalla puede no anunciar el porcentaje como progreso.

#### Recomendacion Especifica
**Accion principal:** Agregar semantica de progreso a elementos visuales.

**Codigo referencial:**
```html
<div role="progressbar"
     aria-label="Progreso actual del curso"
     aria-valuemin="0"
     aria-valuemax="100"
     aria-valuenow="{{ $progreso }}">
  <div style="width: {{ $progreso }}%"></div>
</div>
```

#### Criterio de Aceptacion
- [ ] Lector de pantalla anuncia nombre y porcentaje.
- [ ] Estado visual no depende solo de color.
- [ ] Prueba feature valida presencia de `role="progressbar"`.

---

### Hallazgo H004: Validacion automatizada A11y ausente

**Prioridad:** P2 | **Impacto:** Medio | **Esfuerzo:** Medio | **Area:** Testing

#### Descripcion del Problema
Hay pruebas de preferencias de accesibilidad, pero no hay evidencia de Axe, Pa11y o Lighthouse CI para detectar regresiones de contraste, labels, landmarks o dialogs.

#### Recomendacion Especifica
Agregar smoke tests automatizados para rutas criticas: login, cursos, curso, evaluacion, perfil, admin reportes y calendario.

#### Criterio de Aceptacion
- [ ] CI ejecuta al menos una herramienta A11y automatizada.
- [ ] Fallas P0/P1 bloquean merge.
- [ ] Reportes quedan guardados como artefactos.

---

### Hallazgo H005: Contraste dinamico no verificado

**Prioridad:** P2 | **Impacto:** Medio | **Esfuerzo:** Medio | **Area:** colores de curso y badges

#### Descripcion del Problema
La pantalla de curso usa `color-mix()` con un color promedio de portada. Esto mejora personalizacion, pero requiere validacion de contraste para texto, bordes e indicadores generados dinamicamente.

#### Recomendacion Especifica
Definir una funcion o helper que calcule si el acento es apto para texto y seleccione tokens accesibles cuando no cumpla.

#### Criterio de Aceptacion
- [ ] Combinaciones dinamicas cumplen WCAG AA.
- [ ] Se prueba un acento claro, uno oscuro y uno saturado.

---

## CATEGORIA: Experiencia de Usuario

### Hallazgo H006: Empty states necesitan acciones siguientes

**Prioridad:** P1 | **Impacto:** Alto | **Esfuerzo:** Bajo | **Area:** cursos, certificados, calendario

#### Descripcion del Problema
El curso sin modulos muestra "Este curso aun no tiene modulos.", pero algunos estados vacios del LMS deberian orientar la siguiente accion segun rol.

#### Recomendacion Especifica
Agregar microcopia con accion primaria: volver a cursos, contactar capacitador o crear modulo segun contexto.

#### Criterio de Aceptacion
- [ ] Cada empty state explica que pasa.
- [ ] Cada empty state ofrece una accion clara.
- [ ] Texto mantiene tono español y util.

---

### Hallazgo H007: Feedback global de alertas no distingue tipos

**Prioridad:** P1 | **Impacto:** Alto | **Esfuerzo:** Medio | **Area:** `resources/views/layouts/user.blade.php`

#### Descripcion del Problema
El modal global recibe `type`, pero el icono y color observados son de bloqueo/seguridad por defecto. Un exito, error o aviso deberia comunicar severidad de forma visual y textual.

#### Recomendacion Especifica
Mapear `type` a icono, titulo, color y `aria-live` adecuado.

#### Criterio de Aceptacion
- [ ] Exito, error, warning e info tienen estilo y texto claros.
- [ ] Mensajes criticos usan `role="alert"`.
- [ ] Mensajes informativos no interrumpen de forma agresiva.

---

### Hallazgo H008: Flujo de evaluacion requiere seguimiento mobile continuo

**Prioridad:** P2 | **Impacto:** Medio | **Esfuerzo:** Bajo | **Area:** evaluaciones

#### Descripcion del Problema
Existe test `EvaluacionLayoutTest` para evitar espacio oculto del boton "Anterior" y validar layout responsive. Es una buena defensa, pero conviene ampliar el set para preguntas largas, muchas opciones y feedback de error.

#### Recomendacion Especifica
Agregar fixtures de evaluacion con textos largos, alternativas extensas y resolucion 375px.

#### Criterio de Aceptacion
- [ ] Textos largos no rompen layout.
- [ ] Botones de accion permanecen visibles.
- [ ] Errores de validacion aparecen cerca del campo afectado.

---

## CATEGORIA: Diseno Visual

### Hallazgo H009: Design system no documentado

**Prioridad:** P2 | **Impacto:** Medio | **Esfuerzo:** Bajo | **Area:** `resources/css/app.css`, componentes Blade

#### Descripcion del Problema
El proyecto tiene tokens claros (`Alumco-blue`, `Alumco-green`, Ubuntu, Sora), pero no hay documento de sistema visual en `docs/`.

#### Recomendacion Especifica
Documentar tokens, componentes base, estados, radios, sombras y ejemplos de uso.

#### Criterio de Aceptacion
- [ ] Tokens principales estan documentados.
- [ ] Hay ejemplos para botones, cards, badges, tablas y modales.
- [ ] Nuevos cambios UI referencian esta guia.

---

### Hallazgo H010: Radios y sombras varian entre areas

**Prioridad:** P2 | **Impacto:** Medio | **Esfuerzo:** Medio | **Area:** worker/admin

#### Descripcion del Problema
Se observan radios altos (`rounded-3xl`, `border-radius: 26px`, `30px`) y sombras grandes en distintos componentes. Esto crea una identidad amable, pero puede reducir densidad en pantallas operativas.

#### Recomendacion Especifica
Definir escala de contenedores: cards de contenido, paneles de dashboard, modales y botones.

#### Criterio de Aceptacion
- [ ] Cada tipo de componente tiene radio recomendado.
- [ ] Admin usa una densidad mas operativa que worker.
- [ ] No hay cards anidadas innecesarias.

---

### Hallazgo H011: Iconografia mezclada entre SVG inline y posible libreria

**Prioridad:** P3 | **Impacto:** Bajo | **Esfuerzo:** Medio | **Area:** vistas Blade

#### Descripcion del Problema
La UI usa SVG inline repetidos para navegacion y acciones. Esto funciona, pero aumenta duplicacion y dificulta cambios de estilo.

#### Recomendacion Especifica
Extraer iconos repetidos a componentes Blade o adoptar una convencion unica.

#### Criterio de Aceptacion
- [ ] Iconos repetidos tienen una fuente unica.
- [ ] Los decorativos usan `aria-hidden="true"`.
- [ ] Los accionables tienen nombre accesible.

---

## CATEGORIA: Mobile

### Hallazgo H012: Touch targets requieren auditoria sistematica

**Prioridad:** P1 | **Impacto:** Alto | **Esfuerzo:** Medio | **Area:** navegacion mobile, botones, chips

#### Descripcion del Problema
La bottom nav tiene alto de 64px y el avatar mobile usa 40x40px. Algunos controles pueden quedar bajo el minimo recomendado de 44x44px, especialmente icon buttons compactos.

#### Recomendacion Especifica
Auditar todos los controles interactivos en 375px y ajustar minimo tactil a 44x44px.

#### Criterio de Aceptacion
- [ ] Todos los controles principales miden al menos 44x44px.
- [ ] Separacion entre targets evita toques accidentales.
- [ ] El contenido no queda tapado por bottom nav.

---

## CATEGORIA: Rendimiento

### Hallazgo H013: Rendimiento percibido existe, pero falta baseline real

**Prioridad:** P1 | **Impacto:** Alto | **Esfuerzo:** Medio | **Area:** Vite, Livewire Navigate, assets

#### Descripcion del Problema
El proyecto ya usa `wire:navigate.hover`, skeletons y tests de rendimiento percibido. Sin embargo, no hay baseline automatizado de LCP, INP, CLS ni peso de bundle.

#### Recomendacion Especifica
Agregar Lighthouse CI o PageSpeed programado para rutas criticas y registrar tendencia.

#### Criterio de Aceptacion
- [ ] Existe baseline por ruta critica.
- [ ] Se define presupuesto de JS/CSS.
- [ ] Regresiones relevantes bloquean release o generan alerta.

