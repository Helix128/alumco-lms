# Glosario y Referencias - Auditoria UI/UX

## Glosario de Terminos

### A
- **Accesibilidad A11y:** Practica de diseñar productos usables por todas las personas, incluyendo personas con discapacidades.
- **ARIA:** Atributos para comunicar nombre, rol y estado de componentes a tecnologias asistivas.
- **Alt text:** Texto alternativo que describe una imagen cuando la imagen aporta informacion.

### C
- **CLS:** Metrica que mide cambios inesperados de layout.
- **Core Web Vitals:** Metricas clave de experiencia web: LCP, INP y CLS.
- **Contraste:** Diferencia visual entre texto y fondo.

### F
- **Focus trap:** Patron que mantiene el foco dentro de un modal mientras esta abierto.

### I
- **INP:** Metrica de capacidad de respuesta ante interacciones.

### L
- **LCP:** Metrica que mide cuando aparece el contenido principal visible.
- **Livewire Navigate:** Navegacion SPA-like de Livewire para mejorar experiencia percibida.

### P
- **Prioridad P0-P3:** Nivel de urgencia del hallazgo.
  - **P0 Critico:** Bloquea funcionalidad relevante o accesibilidad esencial.
  - **P1 Alto:** Degrada de forma importante la experiencia.
  - **P2 Medio:** Mejora importante de calidad.
  - **P3 Bajo:** Refinamiento o deuda menor.

### Q
- **Quick Win:** Mejora de alto impacto con bajo esfuerzo.

### T
- **TALL:** Tailwind, Alpine, Laravel y Livewire.
- **Touch target:** Area tactil disponible para activar un control en mobile.

### U
- **UI:** Interfaz de usuario: elementos visuales e interactivos.
- **UX:** Experiencia de usuario completa al cumplir tareas.
- **Usabilidad:** Facilidad para completar tareas con eficacia y baja friccion.

### W
- **WCAG 2.1:** Guia internacional de accesibilidad web.
  - **Nivel A:** Requisitos minimos.
  - **Nivel AA:** Nivel recomendado.
  - **Nivel AAA:** Nivel avanzado.

---

## Estandares y Frameworks de Referencia

### Accesibilidad
- WCAG 2.1 Quick Reference: https://www.w3.org/WAI/WCAG21/quickref/
- A11y Project Checklist: https://www.a11yproject.com/checklist/
- ARIA Authoring Practices: https://www.w3.org/WAI/ARIA/apg/

### Usabilidad
- Nielsen's 10 Usability Heuristics.
- The Design of Everyday Things, Don Norman.
- Web Accessibility Initiative: https://www.w3.org/WAI/

### Rendimiento
- Web.dev: https://web.dev/
- Core Web Vitals: https://web.dev/vitals/
- Lighthouse: https://developer.chrome.com/docs/lighthouse/

### Diseno
- Material Design: https://m3.material.io/
- Apple Human Interface Guidelines: https://developer.apple.com/design/human-interface-guidelines/
- Atomic Design: https://bradfrost.com/blog/post/atomic-web-design/

---

## Herramientas de Testing y Validacion

### Testing Automatizado de Accesibilidad
- Axe DevTools: https://www.deque.com/axe/devtools/
- WAVE: https://wave.webaim.org/
- Pa11y: https://pa11y.org/
- Lighthouse en Chrome DevTools.

### Testing Automatizado de Rendimiento
- PageSpeed Insights: https://pagespeed.web.dev/
- GTmetrix: https://gtmetrix.com/
- WebPageTest: https://www.webpagetest.org/
- Lighthouse CI.

### Validacion HTML/CSS
- W3C HTML Validator: https://validator.w3.org/
- W3C CSS Validator: https://jigsaw.w3.org/css-validator/

### Testing Manual
- NVDA para Windows.
- JAWS para Windows.
- VoiceOver en macOS/iOS.
- TalkBack en Android.

---

## Convenciones Usadas en Este Reporte

### Prioridades
- **P0:** Accion inmediata.
- **P1:** Resolver en el siguiente ciclo.
- **P2:** Planificar en roadmap cercano.
- **P3:** Mejora continua.

### Abreviaturas
- **A11y:** Accessibility.
- **API:** Application Programming Interface.
- **CI:** Continuous Integration.
- **KPI:** Key Performance Indicator.
- **LMS:** Learning Management System.
- **NPS:** Net Promoter Score.
- **TALL:** Tailwind, Alpine, Laravel, Livewire.
- **WCAG:** Web Content Accessibility Guidelines.

---

## Contacto y Seguimiento

**Auditor:** Pendiente | **Email:** Pendiente | **Disponibilidad:** Pendiente

**PM de Proyecto:** Pendiente | **Email:** Pendiente

**Tech Lead:** Pendiente | **Email:** Pendiente

**Para preguntas sobre:**
- Accesibilidad: Pendiente.
- Rendimiento: Pendiente.
- Diseno: Pendiente.
- Roadmap: Pendiente.

---

## Historial de Cambios

| Version | Fecha | Cambios |
|---------|-------|---------|
| 1.0 | 2026-05-06 | Auditoria inicial documentada |

---

## Apendices Adicionales

### A. Screenshots y Evidencia
Referencia sugerida: `documents/Mockups/` y futuras capturas de producto real por breakpoint.

### B. Datos de Testing
Referencia sugerida: artefactos Lighthouse/Axe cuando se configure baseline.

### C. Configuraciones Tecnicas
Referencias principales:
- `resources/css/app.css`
- `resources/views/layouts/user.blade.php`
- `resources/views/partials/accessibility-modal.blade.php`
- `resources/views/partials/bottom-nav.blade.php`
- `tests/Feature/AccessibilityPreferencesTest.php`
- `tests/Feature/NavigationPerceivedPerformanceTest.php`

