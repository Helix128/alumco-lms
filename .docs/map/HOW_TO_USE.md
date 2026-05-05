---
updated: 2026-05-04
covers: "como leer y mantener docs"
when_to_use: "cuando llegues nuevo al mapa"
---

# How To Use

## Que es esta carpeta

`.docs/map/` es un mapa de navegacion para IA y desarrolladores. Resume estructura, rutas, modelos, vistas, config y flujos de negocio sin tener que recorrer todo el repo.

## Orden recomendado de lectura

1. `00_INDEX.md` para saber que documento abre cada tema.
2. `01_PROJECT_OVERVIEW.md` para contexto global.
3. `02_DIRECTORY_TREE.md` para ubicar archivos.
4. `03_ROUTES_MAP.md` si la tarea involucra navegacion o endpoints.
5. `04_MODELS_MAP.md` y `07_DATABASE_SCHEMA.md` si cambia persistencia.
6. `05_CONTROLLERS_MAP.md` y `06_SERVICES_AND_ACTIONS.md` si cambia logica.
7. `08_VIEWS_AND_COMPONENTS.md` si cambia UI.
8. `09_CONFIG_AND_ENV.md` si cambia runtime, colas, mail, cache o auth.
9. `10_QUICK_REFERENCE.md` para atajos.

## Como mantenerlo actualizado

- Si agregas una ruta, actualiza `03_ROUTES_MAP.md`.
- Si agregas o cambias un modelo, actualiza `04_MODELS_MAP.md` y, si toca BD, `07_DATABASE_SCHEMA.md`.
- Si agregas un controlador o metodo publico, actualiza `05_CONTROLLERS_MAP.md`.
- Si agregas Service, Action, Job, Command o Notificacion nueva, actualiza `06_SERVICES_AND_ACTIONS.md`.
- Si agregas o cambias una vista o componente, actualiza `08_VIEWS_AND_COMPONENTS.md`.
- Si cambias una variable de entorno o config, actualiza `09_CONFIG_AND_ENV.md`.
- Si borras algo, no lo elimines del mapa sin dejar nota de reemplazo o estado `No implementado`.
- Mantén cada entrada breve y estable. Esta carpeta debe reducir tokens, no convertirse en duplicado completo del codigo.

