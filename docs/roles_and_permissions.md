# Roles y Permisos (ACL)

Alumco LMS gestiona el acceso jerárquico mediante la librería oficial de `spatie/laravel-permission`. 
Existen 5 grandes roles principales en el sistema. Todo modelo `User` en la plataforma tiene asignado un Rol, que le garantiza (o deniega) el acceso a ciertas vistas y módulos.

## 1. Desarrollador (`Desarrollador`)
- Es el super administrador del sistema (Súper Usuario).
- Generalmente asociado a los creadores de la plataforma o personal TI de alto nivel.
- Tiene acceso absoluto y sin restricciones a cualquier sección, controlador y función de la web. Posee poder destructivo total (Eliminar cursos, modificar todos los usuarios, etc.).

## 2. Administrador (`Administrador`)
- Administrador general de la ONG Alumco.
- **Competencias**:
  - Gestión completa de Usuarios (Crear, Editar, Eliminar Trabajadores y Capacitadores).
  - Gestión transversal sobre Sedes y Estamentos de toda la organización.
  - Visualización del Dashboard general de reportes (Estadísticas macro del LMS).
- Al igual que el Desarrollador, la función `$user->hasAdminAccess()` retornará `true`.

## 3. Capacitador Interno (`Capacitador Interno`)
- Personal de Alumco dedicado a generar el contenido de formación.
- **Competencias**:
  - Crear Cursos, Planificaciones, Módulos y Evaluaciones.
  - Asignar sus propios cursos a diversos Estamentos.
  - **Restricción**: A diferencia de un Administrador, un Capacitador Interno *sólo puede editar o eliminar aquellos cursos que él mismo creó*. Si el curso fue creado por el usuario X, el usuario Y (ambos capacitadores) no puede modificarlo ni borrar su contenido.

## 4. Capacitador Externo (`Capacitador Externo`)
- Personal contratado externamente o consultor que imparte un curso en específico.
- **Competencias**: Similares al Capacitador Interno. Posee un dashboard de estadísticas enfocado únicamente en la tasa de aprobación y rendimiento de los **cursos que le pertenecen**.

## 5. Trabajador (`Trabajador`)
- Usuario final del sistema, empleado de la ONG Alumco.
- **Competencias**:
  - Entrar al panel de `Mis Cursos`. Sólo visualiza los Cursos que le han sido asignados explícitamente a su **Estamento** respectivo (mediante la tabla pivote de Planificaciones/Asignaciones).
  - Explorar el contenido multimedia del curso (Video, PDF, Imágenes).
  - Realizar Evaluaciones con intentos limitados.
  - Generar automáticamente y descargar **Certificados de Aprobación** cuando finaliza y aprueba un curso.
  - Ver el módulo de `Mis Logros` donde reposan todos sus certificados.

## Gestión en el Código

Cualquier validación de vistas (`Blade`) y Controladores debe hacerse mediante middleware o las funciones *helper* implementadas nativamente en el modelo `User.php`.

**Ejemplo en Controlador:**
```php
// app/Http/Controllers/Capacitador/CursoController.php
if (auth()->user()->hasAdminAccess()) {
    // Es desarrollador o admin, pasa directo
    return;
}
abort_unless($curso->capacitador_id === auth()->id(), 403, 'No puedes editar cursos de terceros.');
```

**Ejemplo en Blade:**
```blade
@if(auth()->user()->isTrabajador())
    <a href="/mis-cursos">Ir al Salón Virtual</a>
@elseif(auth()->user()->isCapacitador())
    <a href="/capacitador/panel">Panel de Capacitación</a>
@endif
```
