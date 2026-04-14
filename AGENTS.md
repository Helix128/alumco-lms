# AGENTS.md

## Proyecto
Alumco LMS es un sistema de gestión de aprendizaje para la ONG Alumco.

### Stack principal
- Laravel 13
- PHP 8.3
- Blade
- Livewire 4
- Tailwind CSS 4
- Vite
- MySQL, Redis y Mailpit

## Regla clave de entorno
Este proyecto **usa Docker con Laravel Sail**.

### Importante
- **No asumir PHP "pelado" en la máquina host**.
- **No usar comandos como `php`, `composer`, o `npm` directamente** salvo que el usuario lo pida de forma explícita.
- Para tareas del proyecto, preferir siempre Laravel Sail. sail *comandos* → `./vendor/bin/sail *comando*`

## Comandos preferidos
Usar estos patrones:

- `./vendor/bin/sail up -d`
- `./vendor/bin/sail artisan migrate`
- `./vendor/bin/sail artisan test`
- `./vendor/bin/sail composer install`
- `./vendor/bin/sail npm install`
- `./vendor/bin/sail npm run dev`
- `./vendor/bin/sail npm run build`

## Forma de trabajar esperada
- Hacer cambios claros y enfocados. Pueden ser grandes pero deben tener un propósito definido.
- Respetar la estructura existente del proyecto.
- Investigar la causa raíz antes de corregir bugs.
- Verificar con pruebas o comandos reales antes de dar algo por terminado.
- Mantener el texto de interfaz y los mensajes en español cuando corresponda.
- Preferir Livewire para elementos interactivos de la interfaz.
- Usar JavaScript directo solo cuando la necesidad no pueda resolverse bien con Livewire.
- Evitar dependencias nuevas o refactors grandes si no son necesarios.

## Criterios de estilo
### Blade, Livewire y Tailwind
- Tailwind está bien para layout, espaciado, tipografía y estilos estáticos.
- Para comportamientos interactivos, preferir componentes o acciones con Livewire antes que JavaScript personalizado.
- Para estados interactivos como hover, focus, active, transiciones o animaciones, preferir clases CSS comunes en la hoja de estilos del proyecto en vez de acumular variantes en el HTML.
- En Tailwind v4, respetar los tokens de color ya usados por el proyecto.

## Al editar o depurar
- Revisar primero rutas, controladores, vistas, modelos y migraciones relacionadas.
- Mantener compatibilidad con Laravel y Livewire existentes.
- Si hay que ejecutar algo en consola, hacerlo con Sail primero.

## Objetivo para agentes de IA
Actuar como asistentes técnicos del proyecto, proponiendo soluciones compatibles con Laravel Sail, el stack actual y la forma de trabajo definida arriba.
