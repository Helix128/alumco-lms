# Auditoria de Mobile - Alumco LMS

## Resumen de Compatibilidad

| Aspecto | iOS | Android | Estado |
|---------|-----|---------|--------|
| Responsividad | Parcialmente validada | Parcialmente validada | Parcial |
| Touch targets | Pendiente auditoria | Pendiente auditoria | Parcial |
| Viewport | Correcto | Correcto | Cumple |
| Performance | Pendiente baseline | Pendiente baseline | Sin medir |

---

## 1. Touch Targets

### Hallazgo: Algunos controles pueden quedar bajo 44px

**Elementos afectados:**
- Avatar mobile en `resources/views/layouts/user.blade.php`: 40x40px.
- Icon buttons compactos en admin: revisar 40x40px.
- Chips y acciones secundarias: revisar por pantalla.

**Recomendacion:** Definir minimo tactil de 44x44px para acciones principales y 40x40px solo para acciones secundarias con separacion suficiente.

**Criterios:**
- [ ] Acciones primarias cumplen 44x44px.
- [ ] Separacion minima evita toques accidentales.
- [ ] Controles inferiores no quedan cubiertos por bottom nav.

---

## 2. Viewport

### Configuracion actual

```html
<meta name="viewport" content="width=device-width, initial-scale=1.0">
```

### Hallazgo
La configuracion permite zoom y es correcta. No se observa `user-scalable=no`.

---

## 3. Rendimiento Mobile

### Comparativa Desktop vs Mobile

| Metrica | Desktop | Mobile | Diferencia |
|---------|---------|--------|------------|
| LCP | Pendiente | Pendiente | Pendiente |
| INP | Pendiente | Pendiente | Pendiente |
| CLS | Pendiente | Pendiente | Pendiente |

### Hallazgo: Performance movil sin baseline
El proyecto debe medir mobile de forma separada porque CPU, red y viewport cambian la experiencia real.

---

## 4. Layouts Moviles

### Breakpoints probados

**Mobile 375px:** Pendiente captura actual.

**Tablet 768px:** Pendiente captura actual.

**Desktop 1024px:** Pendiente captura actual.

### Hallazgo: Falta evidencia visual versionada
Existen mockups en `documents/Mockups/`, pero falta evidencia del producto renderizado actualmente.

**Recomendacion:** Capturar rutas criticas con Playwright o herramienta equivalente.

---

## 5. Entrada de Datos

### Tipos de Input
- Login/email: pendiente revision puntual.
- Perfil: pendiente revision puntual.
- Formularios capacitador: pendiente revision puntual.
- Evaluaciones: requiere validar opciones largas y errores.

### Hallazgo: Inputs mobile requieren checklist
Los formularios deben usar `type`, `autocomplete`, `inputmode` y mensajes de error adecuados.

**Criterios:**
- [ ] Email usa `type="email"` y `autocomplete="email"`.
- [ ] Password usa autocomplete correcto.
- [ ] RUT/telefono si aplica usan `inputmode`.
- [ ] Errores aparecen cerca del campo.

