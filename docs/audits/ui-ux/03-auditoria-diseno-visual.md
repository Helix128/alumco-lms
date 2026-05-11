# Auditoria de Diseno Visual - Alumco LMS

## 1. Sistema de Diseno

### Estado Actual
- Existe design system documentado: No formalmente.
- Esta centralizado: Parcialmente en `resources/css/app.css` y componentes Blade.
- Se mantiene actualizado: Parcial. Las pruebas cubren algunas decisiones, pero falta guia visual.

### Hallazgos

#### VIS-001: Tokens reales sin documentacion operativa
El archivo `resources/css/app.css` define colores Alumco, tipografias Ubuntu/Sora y clases de superficie para worker/admin. Esto funciona como fuente tecnica, pero no como contrato para diseno y desarrollo.

**Recomendacion:** Crear una guia corta de tokens y componentes con ejemplos: botones, badges, cards, panels, modales, estados de alerta y navegacion.

**Criterios de aceptacion:**
- [ ] Tokens de color y fuente listados.
- [ ] Estados hover, focus, active y disabled documentados.
- [ ] Componentes base enlazados a archivos Blade/CSS.

---

## 2. Paleta de Colores

### Analisis de Contraste

**Colores primarios usados:**

| Color | Hex | Uso | Riesgo | Cumple WCAG AA? |
|-------|-----|-----|--------|-----------------|
| Alumco blue | `#205099` | Primario, nav, botones | Bajo con texto blanco | Probable si texto es blanco |
| Alumco green | `#AFDD83` | Acento claro | Alto con texto blanco | Requiere validacion |
| Alumco coral | `#FF6364` | Acento/error visual | Medio | Requiere variante accesible |
| Alumco yellow | `#F8B606` | Focus/acento | Medio | Requiere texto oscuro |
| Alumco gray | `#4A4A4A` | Texto | Bajo | Probable |
| Alumco cream | `#FDF9F3` | Fondo | Bajo | Depende de texto |

El proyecto ya incluye variantes accesibles: `Alumco-green-accessible`, `Alumco-gold-accessible` y `Alumco-coral-accessible`. La oportunidad esta en usarlas de forma obligatoria cuando haya texto encima.

### Hallazgos sobre color

#### VIS-002: Colores dinamicos de curso requieren verificacion
La vista de curso usa `color-mix()` con `$curso->color_promedio`. La personalizacion visual es positiva, pero debe existir fallback cuando el color promedio no da contraste suficiente.

**Recomendacion:** Crear helper de contraste y aplicar tokens accesibles para texto, iconos y bordes.

---

## 3. Tipografia

### Configuracion Actual
- Familia principal: Ubuntu.
- Familia display: Sora.
- Familia logo: Nexa Black como fallback declarado.
- Escala de accesibilidad: 18px, 20px, 22px mediante `--font-base`.
- Weights usados: 400, 500, 700, 800 y 900 por utilidades.

### Hallazgos

#### VIS-003: Uso frecuente de texto muy pequeno
Hay utilidades `text-[9px]`, `text-[10px]` y `text-[11px]` compensadas por reglas de accesibilidad. Aunque existe mitigacion, estos tamaños pueden ser debiles en mobile o para usuarios con baja vision.

**Recomendacion:** Reservar texto menor a 12px solo para metadatos no esenciales. Usar 14px como minimo recomendado en informacion operativa.

---

## 4. Espaciado y Escala

### Sistema de espaciado
- Unidad base: Tailwind spacing, principalmente multiplos de 4px.
- Escala implementada: `gap`, `px`, `py`, `rounded-*`, sombras custom.

### Hallazgos

#### VIS-004: Densidad visual distinta entre admin y trabajador
Worker usa una UI mas amable y espaciosa. Admin necesita mayor densidad para reportes, usuarios y graficos. La diferencia es valida, pero requiere reglas claras para evitar pantallas operativas demasiado amplias.

**Recomendacion:** Definir escala por contexto:
- Worker: lectura, progreso y cursos.
- Admin: tablas, filtros y dashboards densos.
- Capacitador: creacion y seguimiento.

---

## 5. Componentes Reutilizables

### Inventario de Componentes
- `accessibility-modal`: funcional, requiere foco robusto.
- `bottom-nav`: funcional, requiere auditoria tactil.
- `nav-link-admin`: reusable para navegacion admin.
- `chart-panel`: reusable para metricas.
- `worker-card`, `admin-surface`: clases CSS compartidas.

### Hallazgos

#### VIS-005: Variantes no documentadas
Las variantes existen en CSS y Blade, pero no estan descritas como API de componentes.

**Recomendacion:** Documentar props, clases esperadas, estados y ejemplos de uso por componente.

---

## 6. Layouts y Responsividad

### Breakpoints configurados
- Mobile: base Tailwind.
- Tablet: `sm`, `md`.
- Desktop: `lg`, con max width frecuente `max-w-[90rem]`.

### Hallazgos

#### VIS-006: Necesaria matriz de screenshots responsive
Hay mocks en `documents/Mockups/`, pero no evidencia actualizada de capturas del producto real por breakpoint.

**Recomendacion:** Mantener capturas de login, cursos, curso, capsula, evaluacion, admin dashboard y calendario en 375px, 768px y 1024px.

