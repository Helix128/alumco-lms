# Auditoria de Accesibilidad A11Y - Alumco LMS

## Resumen de Cumplimiento WCAG 2.1

| Criterio | Nivel | Paginas Afectadas | Estado | Notas |
|----------|-------|-------------------|--------|-------|
| 1.4.3 Contraste minimo | AA | Curso, badges, estados | Parcial | Colores dinamicos requieren medicion |
| 2.1.1 Teclado | A | Modales | Parcial | Falta foco/trap explicito |
| 2.4.3 Orden del foco | A | Modales | Parcial | Restauracion de foco pendiente |
| 2.4.7 Focus visible | AA | Worker/Admin | Parcial | Existe `worker-focus`, falta consistencia total |
| 3.1.1 Idioma de la pagina | A | Layouts | Cumple | `lang="es"` o locale app |
| 4.1.2 Nombre, rol, valor | A | Progresos/dialogs | Parcial | Progresos requieren ARIA |

---

## 1. Perceptibilidad

### 1.1 Alternativas de Texto

#### Hallazgo: Imagen de portada usa titulo como alt
La portada de curso usa `alt="{{ $curso->titulo }}"`. Si la imagen es decorativa, esto puede duplicar el titulo. Si aporta informacion, el alt debe describir la imagen.

**Recomendacion:** Definir regla editorial:
- Decorativa: `alt=""`.
- Informativa: descripcion breve real.
- Portada generica: usar `alt=""` si el titulo ya esta visible.

**Criterios:**
- [ ] Portadas decorativas no duplican texto.
- [ ] Imagenes informativas tienen descripcion util.

### 1.4 Distinguibilidad

#### Hallazgo: Contraste dinamico no medido
Los acentos generados desde portada deben pasar contraste. Usar variantes accesibles cuando haya texto.

**Criterios:**
- [ ] Texto normal cumple 4.5:1.
- [ ] Texto grande cumple 3:1.
- [ ] Indicadores no dependen solo de color.

---

## 2. Operabilidad

### 2.1 Accesible por Teclado

#### Hallazgo: Modales sin foco robusto
El modal de accesibilidad y la alerta global deben controlar foco inicial, ciclo de Tab y restauracion.

**Criterios:**
- [ ] Abrir modal mueve foco al dialog.
- [ ] Tab y Shift+Tab permanecen dentro.
- [ ] Escape cierra.
- [ ] Foco vuelve al disparador.

### 2.4 Navegable

#### Hallazgo: Focus visible parcial
`worker-focus` esta bien definido, pero debe aplicarse de forma consistente en worker, admin, capacitador, botones de alerta y controles Livewire.

**Criterios:**
- [ ] Todos los controles interactivos tienen focus visible.
- [ ] El foco cumple contraste minimo.
- [ ] No se elimina outline sin reemplazo equivalente.

---

## 3. Comprensibilidad

### 3.1 Legible

#### Hallazgo: Microcopia de estados puede ser mas accionable
Mensajes como "Este curso aun no tiene modulos." informan, pero no orientan.

**Recomendacion:** Agregar siguiente accion segun rol.

### 3.3 Ayuda para errores

#### Hallazgo: Validacion de formularios requiere checklist comun
Los formularios Livewire deben mostrar errores cerca del campo, conservar datos y evitar mensajes genericos.

**Criterios:**
- [ ] Error cerca del control.
- [ ] Mensaje indica como corregir.
- [ ] Estado `aria-invalid` cuando aplica.

---

## 4. Robustez

### 4.1 Compatible

#### Hallazgo: Falta validacion automatizada HTML/ARIA
No se observo pipeline de Axe/Pa11y/Lighthouse para rutas criticas.

**Recomendacion:** Agregar pruebas automatizadas y guardar reportes.

---

## Resultados de Testing Automatizado

### Axe DevTools
- Errores criticos: Pendiente de ejecucion.
- Advertencias: Pendiente de ejecucion.
- Necesidades de revision: Pendiente de ejecucion.

### Lighthouse Accessibility
- Puntuacion: Pendiente de baseline.
- Elementos probados: Pendiente.
- Items con errores: Pendiente.

---

## Matriz de Remediacion

| Componente | Tipo de Defecto | Severidad | Esfuerzo | Plazo |
|------------|-----------------|-----------|----------|-------|
| Modal accesibilidad | Foco/dialog | Alta | Medio | Semana 1 |
| Alerta global | Foco/rol | Alta | Medio | Semana 1 |
| Progreso curso | Nombre, rol, valor | Alta | Bajo | Semana 2 |
| Portadas curso | Texto alternativo | Media | Bajo | Semana 3 |
| Colores dinamicos | Contraste | Media | Medio | Semana 3 |

