# Auditoria de Rendimiento - Alumco LMS

## Core Web Vitals

### Resumen

| Metrica | Valor Actual | Target | Estado |
|---------|--------------|--------|--------|
| LCP | Pendiente de baseline | <2.5s | Sin medir |
| INP | Pendiente de baseline | <200ms | Sin medir |
| CLS | Pendiente de baseline | <0.1 | Sin medir |

### Observacion
El codigo muestra buenas practicas de rendimiento percibido: `wire:navigate.hover`, navegacion persistente, skeletons y tests. Falta convertir esto en metricas reales por ruta.

---

## Hallazgo: Baseline de performance ausente

**Prioridad:** P1 | **Impacto:** Alto | **Esfuerzo:** Medio

### Causa raiz
- No hay reporte Lighthouse versionado.
- No hay presupuesto de JS/CSS.
- No hay medicion por ruta critica.

### Solucion propuesta
1. Ejecutar Lighthouse en login, cursos, curso, modulo, evaluacion, admin reportes y calendario.
2. Registrar LCP, INP, CLS, TBT, peso JS/CSS e imagenes.
3. Definir umbrales minimos para CI.
4. Guardar resultados como artefactos.

---

## Optimizacion de Recursos

### Imagenes
- Total de imagenes: Pendiente de inventario.
- Optimizadas: Pendiente.
- Tamaño total: Pendiente.
- Potencial de reduccion: Pendiente.

**Hallazgo:** Portadas de curso desde storage pueden afectar LCP si no se optimizan.

**Recomendacion:** Usar dimensiones explicitas, formatos modernos cuando sea posible y lazy loading en imagenes fuera del primer viewport.

### CSS
- Tailwind v4 usado correctamente con `@import 'tailwindcss'`.
- CSS custom extenso en `resources/css/app.css`.
- Riesgo: reglas globales y animaciones sin presupuesto.

### JavaScript
- Livewire/Alpine cubren interaccion.
- Riesgo: no hay reporte de bundle ni cobertura unused JS.

---

## Recomendaciones Prioritarias

1. Crear baseline Lighthouse por rutas criticas: impacto alto, esfuerzo 1 dia.
2. Revisar LCP de portada de curso: impacto medio-alto, esfuerzo 1 dia.
3. Aplicar reduccion de movimiento global: impacto alto para accesibilidad y performance percibido, esfuerzo 0.5 dia.
4. Definir presupuesto de bundle Vite: impacto medio, esfuerzo 0.5 dia.
5. Agregar medicion mensual de Core Web Vitals: impacto alto, esfuerzo medio.

