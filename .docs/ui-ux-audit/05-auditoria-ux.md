# Auditoria de Experiencia de Usuario UX - Alumco LMS

## 1. Arquitectura de Informacion

### Estructura Actual

```text
Trabajador
  Cursos
    Curso
      Modulos
      Consultas
      Feedback
  Calendario
  Certificados
  Perfil

Capacitador
  Dashboard
  Cursos
    Crear/Editar
    Modulos
    Participantes
  Calendario
  Estadisticas

Administrador
  Reportes
  Usuarios
  Perfil
  Vista previa trabajador
```

### Hallazgos

#### UX-001: Navegacion principal clara, pero rutas admin/capacitador requieren inventario visible
La navegacion worker es simple y consistente. En roles internos, algunas areas dependen de permisos y rutas especificas, por lo que conviene documentar el mapa de navegacion visible por rol.

**Recomendacion:** Crear matriz de menu por rol y mantenerla cerca de `docs/roles_and_permissions.md`.

---

## 2. Flujos de Tareas

### Tarea Principal: Completar un curso

**Flujo ideal:**
1. Entrar a Mis cursos.
2. Abrir curso disponible.
3. Ver progreso y etapas.
4. Completar modulos.
5. Rendir evaluacion si aplica.
6. Obtener certificado.

**Flujo actual:**
1. Mis cursos muestra tiles y acceso.
2. Curso muestra progreso, etapas y modulos.
3. Evaluacion tiene test responsive dedicado.
4. Certificados existe como seccion separada.

### Hallazgo: Progreso visible no siempre es accesible
El flujo es comprensible visualmente. Debe reforzarse con semantica para usuarios de lector de pantalla.

---

## 3. Formularios

### Analisis de Formularios Principales

#### Formulario: Preferencias de accesibilidad
- Campos: contraste, reduccion de movimiento, tamano de texto.
- Persistencia: Livewire guarda en cuenta.
- Riesgo: cooldown evita spam, pero necesita feedback claro si una accion no aplica inmediatamente.

### Hallazgo: Feedback de acciones Livewire debe ser uniforme
Cuando una preferencia se guarda, el usuario debe recibir confirmacion discreta. Si hay cooldown, el control no debe parecer roto.

**Recomendacion:** Usar estados `wire:loading`, texto de confirmacion breve y disabled temporal cuando aplique.

---

## 4. Navegacion

### Navegacion Principal
- Ubicacion worker: header desktop y bottom nav mobile.
- Items worker: Mis cursos, Calendario, Certificados.
- Profundidad worker: 2-3 niveles.

### Hallazgo: Bottom nav puede tapar acciones inferiores
El layout usa `pb-28`, lo que mitiga la barra inferior. Debe validarse en pantallas con formularios y botones sticky.

**Recomendacion:** Incluir pruebas visuales en 375px para evaluacion, feedback y certificados.

---

## 5. Feedback y Estados

### Estados Implementados

| Estado | Comunicado | Visual claro |
|--------|------------|--------------|
| Loading navegacion | Si, skeleton/progress | Si |
| Error global | Parcial | Parcial |
| Exito | Parcial | Pendiente de variantes |
| Empty state | Parcial | Parcial |

### Hallazgo: Alertas globales necesitan variantes
El modal global recibe `type`, pero debe reflejarlo en icono, color, titulo y rol ARIA.

---

## 6. Microcopia

### Analisis de Etiquetas y Mensajes
La microcopia esta en español y usa un tono claro. Las mejoras principales estan en convertir estados informativos en acciones concretas.

#### Hallazgo: Empty states poco accionables

**Ejemplos:**
- Actual: "Este curso aun no tiene modulos."
- Mejor: "Este curso aun no tiene modulos disponibles. Vuelve mas tarde o contacta a tu capacitador si necesitas ayuda."

---

## 7. Empty States y Error States

### Hallazgo: Falta catalogo de estados vacios por rol

**Recomendacion:** Definir empty states para:
- Trabajador sin cursos.
- Trabajador sin certificados.
- Curso sin modulos.
- Calendario sin capacitaciones.
- Admin sin resultados en filtros.
- Capacitador sin participantes.

**Criterios:**
- [ ] Cada estado explica causa probable.
- [ ] Cada estado ofrece accion siguiente.
- [ ] No culpa al usuario.

