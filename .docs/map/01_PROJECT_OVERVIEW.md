---
updated: 2026-05-04
covers: "stack proposito patrones convenciones"
when_to_use: "cuando necesites entender el sistema completo"
---

# Project Overview

## Stack tecnico

- Laravel 13.1.1
- PHP 8.4 en el entorno detectado
- Blade
- Livewire 4.2.4
- Tailwind CSS 4.2.4
- Vite 8
- MySQL
- Redis
- Mail via `log` por defecto en `.env.example`
- Paquetes principales:
  - `barryvdh/laravel-dompdf`
  - `endroid/qr-code`
  - `maatwebsite/excel`
  - `mews/purifier`
  - `spatie/laravel-permission`

## Proposito inferido

Plataforma LMS de Alumco para capacitacion interna y seguimiento de cursos. El codigo muestra tres areas claras:

- Trabajador: ve cursos, calendario, modulos, progreso y certificados.
- Capacitador: crea y administra cursos, modulos, secciones, evaluaciones, participantes y certificados.
- Admin/Dev: gestiona usuarios, reportes, vista previa y configuracion global.

## Patrones observados

- Service layer: `App\Services\CertificadoService`
- Action object: `App\Actions\Cursos\DuplicateCourseAction`
- Livewire-first para interaccion compleja: calendarios, evaluaciones, gestion de usuarios, accesibilidad
- Model-centric domain helpers: `Curso`, `Modulo`, `User`, `Evaluacion`, `GlobalSetting`
- Middleware de area por rol: `worker.area`, `capacitador`, `capacitador.interno`, `admin`
- Cache aplicada a vistas pesadas: dashboard, presets, configuracion global y calendarios
- Notificaciones por mail con deduplicacion en BD

## Convenciones observadas

- Nombres en espanol para dominio, rutas y vistas.
- Layouts separados por contexto: `layouts.user`, `layouts.panel`, `layouts.auth`, `layouts.error`.
- Rutas nombradas por area: `cursos.*`, `capacitador.*`, `admin.*`, `mis-certificados.*`.
- Relaciones Eloquent y helpers de negocio conviven en modelos.
- Uso consistente de `wire:navigate.hover` en navegacion principal.
- Los cambios de UI accesible se reflejan en `data-font`, `data-contrast` y `data-motion` sobre `<html>`.

