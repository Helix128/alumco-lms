# Roles y Permisos (ACL)

Alumco LMS gestiona el acceso jerárquico mediante `spatie/laravel-permission`. En la interfaz se debe hablar de colaboradores/as y capacitaciones; los nombres técnicos de roles y entidades se mantienen para compatibilidad.

## 1. Desarrollador (`Desarrollador`)

- Super administrador del sistema.
- Tiene acceso absoluto a secciones, controladores y funciones.

## 2. Administrador (`Administrador`)

- Administrador general de la ONG Alumco.
- Gestiona usuarios, capacitadores, sedes, estamentos y reportes.
- `hasAdminAccess()` retorna `true` para este rol.

## 3. Capacitador Interno (`Capacitador Interno`)

- Personal de Alumco dedicado a generar contenido de formación.
- Puede crear capacitaciones, planificaciones, módulos y evaluaciones.
- Puede asignar sus capacitaciones a estamentos.
- Solo puede editar o eliminar capacitaciones que creó, salvo que tenga acceso administrativo.

## 4. Capacitador Externo (`Capacitador Externo`)

- Persona externa o consultora que imparte una capacitación específica.
- Tiene acceso a estadísticas enfocadas en sus propias capacitaciones.

## 5. Colaborador/a (`Trabajador`)

- Usuario final del sistema.
- El rol técnico sigue siendo `Trabajador`, pero la interfaz debe mostrar “colaborador/a”, “colaboradores” o “colaboradoras y colaboradores” según contexto.
- Accede a **Mis capacitaciones** (`/cursos`) y solo visualiza capacitaciones asignadas a su estamento.
- Realiza evaluaciones, descarga certificados y configura preferencias de accesibilidad.

## Gestión en el código

Las validaciones deben hacerse mediante middleware, policies o helpers existentes en `User.php`. No renombrar rutas, modelos ni roles técnicos sin una migración específica.

```php
if (auth()->user()->hasAdminAccess()) {
    return;
}

abort_unless($curso->capacitador_id === auth()->id(), 403, 'No puedes editar capacitaciones de terceros.');
```

```blade
@if(auth()->user()->isTrabajador())
    <a href="{{ route('cursos.index') }}">Ir a mis capacitaciones</a>
@elseif(auth()->user()->isCapacitador())
    <a href="{{ route('capacitador.dashboard') }}">Panel de capacitación</a>
@endif
```
