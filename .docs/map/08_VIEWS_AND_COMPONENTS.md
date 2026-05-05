---
updated: 2026-05-04
covers: "vistas layout componentes livewire"
when_to_use: "cuando necesites editar la interfaz"
---

# Views and Components

## Layouts

### `resources/views/layouts/user.blade.php`

- Uso: area de trabajador y paginas de curso/certificados
- Estructura:
  - header sticky con logo, nav principal, perfil y logout
  - bottom nav mobile desde `partials.bottom-nav`
  - banner contextual via `@yield('course-banner')`
  - contenido principal con `data-nav-content` y skeleton de navegacion
  - modal global de alertas
- Incluye:
  - `partials.accessibility-scripts`
  - `partials.bottom-nav` si la vista no define `bottom-nav`

### `resources/views/layouts/panel.blade.php`

- Uso: admin, capacitador y dev
- Estructura:
  - topbar persistente
  - sidebar colapsable
  - main content con skeleton
  - modal global de accesibilidad
- Incluye:
  - `partials.accessibility-scripts`
  - `partials.accessibility-modal`
  - `x-logo-alumco`
  - `x-nav-link-admin`

### `resources/views/layouts/auth.blade.php`

- Uso: login y reset password
- Estructura:
  - shell ilustrado con nubes
  - logo grande
  - content slot

### `resources/views/layouts/error.blade.php`

- Uso: paginas 403/404/419/500/503
- Estructura:
  - logo
  - card central con header/body/illustration
  - footer

### `resources/views/layouts/app.blade.php`

- Uso: layout basico para Livewire "page components"
- Estado: implementado, pero en este proyecto el uso principal se concentra en `layouts.user` y `layouts.panel`

## Blade components

### `x-accessibility-preferences`

- Ruta: `resources/views/components/accessibility-preferences.blade.php`
- Props: `title`, `description`
- Proposito: encapsula `livewire:accessibility-preferences`

### `x-file-viewer`

- Ruta: `resources/views/components/file-viewer.blade.php`
- Props: `rutaArchivo`, `archivoUrl`, `descargarUrl`, `nombreOriginal`
- Proposito: muestra imagen, video, PDF o archivo generico con preview/descarga

### `x-logo-alumco`

- Ruta: `resources/views/components/logo-alumco.blade.php`
- Props: atributos del SVG
- Proposito: logo vectorial de marca

### `x-nav-link-admin`

- Ruta: `resources/views/components/nav-link-admin.blade.php`
- Props: `active`, `href`, `title`
- Slots: `icon`, default slot
- Proposito: item del sidebar admin/capacitador

### `x-picker-multi`

- Ruta: `resources/views/components/picker-multi.blade.php`
- Props: `name`, `options`, `selected`, `placeholder`
- Proposito: selector multiple con Alpine

### `x-auth.primary-button`

- Ruta: `resources/views/components/auth/primary-button.blade.php`
- Proposito: boton primario para auth

## Livewire components

### `App\Livewire\AccessibilityPreferences`

- Vista: `resources/views/livewire/accessibility-preferences.blade.php`
- Props del mount: `title`, `description`
- Proposito: cambiar tamano de fuente, contraste y movimiento.

### `App\Livewire\CalendarioUsuario`

- Vista: `resources/views/livewire/calendario-usuario.blade.php`
- Props: ninguna
- Proposito: calendario mensual del colaborador.

### `App\Livewire\DevConfig`

- Vista: `resources/views/livewire/dev-config.blade.php`
- Props: ninguna
- Proposito: editar settings globales y firma legal.

### `App\Livewire\VerEvaluacion`

- Vista: `resources/views/livewire/ver-evaluacion.blade.php`
- Props del mount: `Modulo $modulo`, `Curso $curso`
- Proposito: resolver evaluacion de un modulo.

### `App\Livewire\Admin\UserManagement`

- Vista: `resources/views/livewire/admin/user-management.blade.php`
- Props: ninguna
- Proposito: alta, edicion, busqueda y estado de usuarios.

### `App\Livewire\Admin\ReportePresets`

- Vista: `resources/views/livewire/admin/reporte-presets.blade.php`
- Props: ninguna
- Proposito: guardar y borrar formatos de reporte.

### `App\Livewire\Capacitador\CalendarioCapacitaciones`

- Vista: `resources/views/livewire/capacitador/calendario-capacitaciones.blade.php`
- Props: ninguna
- Proposito: planificacion anual y mensual de capacitaciones.

### `App\Livewire\Capacitador\EditarEvaluacion`

- Vista: `resources/views/livewire/capacitador/editar-evaluacion.blade.php`
- Props del mount: `Evaluacion $evaluacion`, `Curso $curso`
- Proposito: CRUD de preguntas y opciones.

### `App\Livewire\Capacitador\EstadisticasDashboard`

- Vista: `resources/views/livewire/capacitador/estadisticas-dashboard.blade.php`
- Props del mount: `int $capacitadorId`
- Proposito: grafico de avance por curso.

### `App\Livewire\Capacitador\GestionEstamentos`

- Vista: `resources/views/livewire/capacitador/gestion-estamentos.blade.php`
- Props del mount: `Curso $curso`
- Proposito: asignar estamentos a un curso.

## Vista a layout y componentes

| Vista | Layout | Componentes relevantes |
|---|---|---|
| `auth/login` | `layouts.auth` | `x-auth.primary-button` |
| `auth/forgot-password` | `layouts.auth` | `x-auth.primary-button` |
| `auth/reset-password` | `layouts.auth` | `x-auth.primary-button` |
| `cursos/index` | `layouts.user` | `x-file-viewer` en cards de curso |
| `cursos/show` | `layouts.user` | `cursos/partials/modulo-timeline-card`, `x-file-viewer` |
| `modulos/capsula` | `layouts.user` | `x-file-viewer` |
| `modulos/evaluacion` | `layouts.user` | `livewire:ver-evaluacion` |
| `perfil/index` | `layouts.user` | `x-accessibility-preferences` |
| `mis-certificados/index` | `layouts.user` | - |
| `admin/*` | `layouts.panel` | `x-nav-link-admin`, `x-logo-alumco`, `partials.accessibility-modal` |
| `capacitador/*` | `layouts.panel` | `x-nav-link-admin`, `x-logo-alumco`, `partials.accessibility-modal` |
| `certificados/verificar` | layout propio | - |
| `errors/*` | `layouts.error` | - |

## No implementado

- `app/View/Components/`: no implementado como clases PHP.
- `resources/views/components/`: solo Blade components, sin clases asociadas.

