---
updated: 2026-05-04
covers: "arbol directorios anotado"
when_to_use: "cuando busques un archivo o carpeta"
---

# Directory Tree

```text
.
  app/
    Actions/                 # Acciones de negocio reutilizables
      Cursos/                # Clonacion de cursos
    Console/
      Commands/              # Comandos Artisan programados
    Exports/                 # Exportaciones Excel
    Http/
      Controllers/           # Controladores HTTP por area
        Admin/               # Administracion
        Auth/                # Recuperacion de contrasena
        Capacitador/         # Gestion de contenido y alumnos
      Middleware/            # Filtros de acceso por area
    Livewire/                # Componentes Livewire por area
      Admin/
      Capacitador/
    Models/                  # Modelos Eloquent y helpers de dominio
    Notifications/           # Notificaciones por mail
    Providers/               # Service providers
    Services/                # Servicios de dominio
    Support/                 # Helpers de aplicacion
  bootstrap/                 # Arranque y alias de middleware
  config/                    # Configuracion de framework y paquetes
  database/
    factories/               # Factories para tests y seeders
    migrations/              # Esquema y migraciones historicas
    seeders/                 # Datos base y demo
  docs/                      # Documentacion existente del proyecto
  documents/                 # Mockups y formatos externos
  public/                    # Assets publicos
    images/
  resources/
    css/                     # Tailwind y estilos globales
    js/                      # Bootstrap JS y PDF viewer
    views/
      admin/
      auth/
      capacitador/
      certificados/
      components/           # Blade components reutilizables
      cursos/
      errors/
      layouts/              # Layouts de pagina
      livewire/             # Vistas de componentes Livewire
      modulos/
      partials/             # Fragmentos de layout
      perfil/
  routes/
    web.php                  # Todas las rutas de la app
    console.php              # Comandos y schedules
  tests/
    Feature/
      Admin/
    Traits/
    Unit/
```

## Carpetas no obvias

- `app/Support/`: utilidades de flujo de usuario y accesibilidad.
- `resources/views/partials/`: piezas de layout compartidas, no componentes Blade formales.
- `database/seeders/Testing/`: datos demo usados para entorno local y pruebas.
- `database/seeders/assets/`: covers SVG para los seeders demo.
- `public/images/undraw/`: ilustraciones usadas en auth y error pages.

