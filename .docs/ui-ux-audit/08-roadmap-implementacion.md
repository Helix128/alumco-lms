# Roadmap de Implementacion - Alumco LMS

## Matriz Consolidada de Hallazgos

### Hallazgos por Prioridad y Esfuerzo

```text
IMPACTO ALTO
|
|  H001 foco modales          H002 reduced motion
|  H003 progreso ARIA         H012 touch targets
|  H013 baseline performance  H006 empty states
|
|  H009 design tokens         H005 contraste dinamico
|  H011 iconografia
+------------------------------------------------> ESFUERZO
   BAJO                       MEDIO/ALTO
```

### Tabla de Todas las Issues

| ID | Hallazgo | Prioridad | Impacto | Esfuerzo | Categoria | Dependencias | Owner | Plazo |
|----|----------|-----------|---------|----------|-----------|--------------|-------|-------|
| H001 | Modales sin gestion completa de foco | P0 | Alto | Medio | Accesibilidad | - | Frontend | Sem 1 |
| H002 | Reduccion de movimiento incompleta | P0 | Alto | Bajo | Accesibilidad | - | Frontend | Sem 1 |
| H003 | Progreso sin semantica suficiente | P1 | Alto | Bajo | Accesibilidad | - | Frontend | Sem 2 |
| H004 | Validacion A11y automatizada ausente | P2 | Medio | Medio | Accesibilidad | H001-H003 | QA/Dev | Sem 4 |
| H005 | Contraste dinamico no verificado | P2 | Medio | Medio | Accesibilidad | - | Frontend | Sem 3 |
| H006 | Empty states necesitan acciones | P1 | Alto | Bajo | UX | - | Product/Frontend | Sem 2 |
| H007 | Alertas globales sin variantes | P1 | Alto | Medio | UX | H001 | Frontend | Sem 2 |
| H008 | Evaluacion mobile requiere casos largos | P2 | Medio | Bajo | UX | - | QA/Dev | Sem 4 |
| H009 | Design system no documentado | P2 | Medio | Bajo | Visual | - | Design/Frontend | Sem 3 |
| H010 | Radios y sombras varian entre areas | P2 | Medio | Medio | Visual | H009 | Design | Mes 2 |
| H011 | Iconografia inline repetida | P3 | Bajo | Medio | Visual | H009 | Frontend | Trimestral |
| H012 | Touch targets sin auditoria sistematica | P1 | Alto | Medio | Mobile | - | QA/Frontend | Sem 3 |
| H013 | Performance sin baseline real | P1 | Alto | Medio | Rendimiento | - | DevOps/Frontend | Sem 3 |

---

## Plan de Ejecucion por Fases

### Fase 0: Pre-Implementacion (Dias 1-2)

**Preparacion:**
- [ ] Crear issues en tracker.
- [ ] Confirmar owners.
- [ ] Definir rutas criticas para medicion.
- [ ] Preparar checklist A11y/manual.

**Duracion:** 2 dias.

**Recursos:** 1 PM, 1 frontend, 1 QA, apoyo design.

---

### Fase 1: CRITICO (Semanas 1-2)

**Objetivos:**
- Resolver hallazgos P0.
- Mejorar navegacion por teclado.
- Respetar reduccion de movimiento.

**Hallazgos a resolver:**
1. **H001: Modales sin gestion completa de foco**
   - Cambios: foco inicial, trap, Escape, restauracion.
   - Testing: teclado, lector de pantalla, Axe.
   - Tiempo estimado: 1-2 dias.
   - Owner: Frontend.
   - Criterios:
     - [ ] Foco entra al modal.
     - [ ] Foco no escapa.
     - [ ] Foco vuelve al disparador.

2. **H002: Reduccion de movimiento incompleta**
   - Cambios: CSS global para `data-motion` y media query.
   - Testing: preferencia del sistema y preferencia guardada.
   - Tiempo estimado: 0.5-1 dia.
   - Owner: Frontend.
   - Criterios:
     - [ ] Shimmer detenido.
     - [ ] Transiciones no esenciales detenidas.
     - [ ] Feedback de estado sigue visible.

**Dependencias:** Ninguna.

**Riesgos:**
- Trap de foco puede interferir con Livewire si se implementa de forma demasiado global. Mitigacion: componente modal acotado.

**Entregables:**
- [ ] PR con cambios.
- [ ] Pruebas manuales documentadas.
- [ ] Tests feature si aplica.

---

### Fase 2: URGENTE (Semanas 3-4)

**Objetivos:**
- Resolver P1 de mayor impacto.
- Crear baseline tecnico de medicion.

**Hallazgos a resolver:**
- H003 progreso ARIA.
- H006 empty states.
- H007 variantes de alertas.
- H012 touch targets.
- H013 baseline performance.

**Duracion:** 2 semanas.

**Recursos:** 1-2 frontend, 1 QA, apoyo product.

---

### Fase 3: IMPORTANTE (Semanas 5-8)

**Objetivos:**
- Resolver P2.
- Formalizar sistema visual y automatizacion.

**Hallazgos a resolver:**
- H004 validacion A11y.
- H005 contraste dinamico.
- H008 evaluacion mobile con casos largos.
- H009 design tokens.
- H010 escala visual por area.

**Duracion:** 4 semanas.

---

### Fase 4: MEJORA CONTINUA (Mensual)

**Objetivos:**
- Resolver P3.
- Mantener regresiones bajo control.

**Hallazgos a resolver:**
- H011 iconografia inline repetida.
- Nuevos hallazgos de testing mensual.

**Ciclo:** Mensual, maximo 3 refinamientos por sprint.

---

## Matriz de Dependencias

```text
H001 foco modales
  -> H007 alertas globales
  -> H004 validacion A11y

H002 reduced motion
  -> H013 baseline performance

H009 design tokens
  -> H010 escala visual
  -> H011 iconografia

H003 progreso ARIA [independiente]
H006 empty states [independiente]
H012 touch targets [independiente]
```

---

## Tracking y Monitoreo

### Dashboard de Progreso

| Fase | Total | Completados | En Proceso | Por Hacer | % Completado |
|------|-------|-------------|------------|-----------|--------------|
| Fase 1 | 2 | 0 | 0 | 2 | 0% |
| Fase 2 | 5 | 0 | 0 | 5 | 0% |
| Fase 3 | 5 | 0 | 0 | 5 | 0% |
| Fase 4 | 1 | 0 | 0 | 1 | 0% |
| **Total** | **13** | **0** | **0** | **13** | **0%** |

### Reuniones de Seguimiento

- **Semanal:** revision de avance, bloqueadores y proximos pasos.
- **Quincenal:** progreso vs plan, riesgos y ajustes.
- **Mensual:** comparativa de metricas y nuevas oportunidades.

---

## Metricas de Exito

### Antes de Implementacion

| Metrica | Valor |
|---------|-------|
| Accesibilidad WCAG | 78% estimado |
| Performance LCP | Pendiente |
| Performance CLS | Pendiente |
| Usabilidad | 80% estimado |
| Mobile performance | Pendiente |

### Target Final Fase 3

| Metrica | Target | Mejora |
|---------|--------|--------|
| Accesibilidad WCAG | 95% | +17 pts |
| Performance LCP | <2.5s | Crear baseline y cumplir |
| Performance CLS | <0.1 | Crear baseline y cumplir |
| Usabilidad | 90% | +10 pts |
| Mobile performance | >85 | Crear baseline |

---

## Budget y Recursos

### Estimacion de Esfuerzo

| Rol | Fase 1 | Fase 2 | Fase 3 | Fase 4 | Total |
|-----|--------|--------|--------|--------|-------|
| Frontend Dev | 24h | 48h | 40h | 8h/mes | ~120h |
| QA/Tester | 8h | 24h | 24h | 4h/mes | ~60h |
| Designer | 4h | 12h | 24h | 4h/mes | ~44h |
| PM/Product | 4h | 8h | 8h | 2h/mes | ~24h |
| **Total** | **40h** | **92h** | **96h** | **18h/mes** | **~248h** |

---

## Riesgos y Mitigacion

| Riesgo | Probabilidad | Impacto | Mitigacion |
|--------|--------------|---------|------------|
| Cambios de modal rompen interacciones Alpine/Livewire | Media | Alto | Pruebas por teclado y componentes acotados |
| Baseline performance varia por entorno local | Alta | Medio | Medir en entorno estable y repetir 3 veces |
| Ajustes visuales afectan identidad Alumco | Media | Medio | Validar con tokens y mockups existentes |

---

## Plan B: Priorizacion Alternativa

**Opcion A: Accesibilidad Primero**
- Semana 1-2: H001, H002, H003.
- Semana 3-4: H004, H005.
- Semana 5-8: resto.

**Opcion B: Impacto de Usuario Primero**
- Semana 1-2: H001, H002, H006, H007.
- Semana 3-4: H012, H013.
- Semana 5-8: sistema visual.

**Opcion C: Velocidad de Implementacion**
- Semana 1-2: H002, H003, H006, H009.
- Semana 3-4: H001, H007.
- Semana 5-8: automatizacion y refinamientos.

---

## Aprobacion y Commitment

**Preparado por:** Auditoria UI/UX asistida | **Fecha:** 2026-05-06

**Revisado por:** Pendiente | **Fecha:** Pendiente

**Aprobado por:** Pendiente | **Fecha:** Pendiente

**Team commitment:** Pendiente.

