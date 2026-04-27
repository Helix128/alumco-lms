# Alumco LMS

Sistema de gestión de aprendizaje centralizado para la ONG [Alumco](https://ongalumco.cl/). 
Diseñado para organizar estamentos, sedes y distribuir contenidos de formación o capacitaciones mediante cápsulas secuenciales y evaluaciones integradas.

## 📚 Documentación Técnica

Para un entendimiento profundo del sistema, por favor consulta la documentación oficial ubicada en el directorio `docs/`:

- [Arquitectura del Sistema y Base de Datos](docs/architecture.md)
- [Roles y Permisos (ACL)](docs/roles_and_permissions.md)
- [Flujo de Aprendizaje (Cursos, Tests y Certificados)](docs/learning_flow.md)

---

## 🚀 Tecnologías utilizadas

### Backend
- **PHP** `8.3`
- **Laravel Framework** `13.0`
- **Base de Datos:** MySQL Community Server `8.4`
- **Maatwebsite Excel** `3.1` (Generación de reportes)
- **Barryvdh DomPDF** `3.1` (Emisión de certificados físicos en PDF)
- **Spatie Laravel Permission** `7.3` (Motor de Roles y Permisos ACL)

### Frontend
- **Blade** (Motor de plantillas renderizado en servidor)
- **Livewire** `4.2` (Componentes interactivos, reactividad sin JS)
- **Tailwind CSS** `4.0.0` (Sistema de diseño)
- **Vite** `8.0.0` (Empaquetador de dependencias y estilos)

### Herramientas y Entorno
- **Docker & Laravel Sail** `1.54` (Entorno de desarrollo contenerizado oficial)
- **Redis** & **Mailpit** (Servicios satélites para caché y SMTP local)
- **PHPUnit** (Framework de pruebas)

---

## ⚙️ Configuración del proyecto y Desarrollo Local

Todo el desarrollo, migraciones y dependencias deben manejarse obligatoriamente a través del contenedor de **Laravel Sail** para garantizar que se usa la versión correcta de PHP (8.3) y los servicios de caché y base de datos nativos de la plataforma. **No asumas el uso de PHP nativo en la máquina host**.

### Prerrequisitos
- Docker Engine instalado y funcionando (Docker Compose).

### 1. Clonar e Iniciar Contenedores
Si acabas de clonar el repositorio, debes levantar el entorno. Abre tu terminal en la carpeta del proyecto y ejecuta:
```bash
./vendor/bin/sail up -d
```
*(Si es la primera vez que clonas y ni siquiera tienes la carpeta `vendor`, deberás usar la imagen temporal de Docker provista por Laravel para ejecutar el primer `composer install`)*

### 2. Preparar Dependencias y Base de Datos
Todos los comandos se corren anteponiendo `sail`:

```bash
# Instalar dependencias de PHP
./vendor/bin/sail composer install

# Instalar dependencias de Frontend (Tailwind/Vite)
./vendor/bin/sail npm install

# Correr migraciones y popular la DB de desarrollo con Seeders
./vendor/bin/sail artisan migrate:fresh --seed --force

# IMPORTANTÍSIMO: Crear el Symlink de Storage para que las portadas y archivos de los módulos sean visibles!
./vendor/bin/sail artisan storage:link
```

### 3. Compilación de Activos (Frontend)
Debido al uso de Tailwind V4 y Vite, todos los cambios a archivos `.blade.php` o CSS requieren que Vite ensamble los estilos. Tienes dos opciones principales de comandos:

```bash
# Opción 1 (Desarrollo Activo): Se queda escuchando cambios en vivo (Hot Reload)
./vendor/bin/sail npm run dev

# Opción 2 (Despliegue/Prueba Unitaria): Empaqueta los archivos a la versión minimizada y cierra el proceso
./vendor/bin/sail npm run build
```

## 🛠️ Reglas Básicas de Contribución
1. **Respeta Laravel Sail**: Si hay que ejecutar un comando, utilízalo mediante `./vendor/bin/sail <comando>`.
2. **Prioriza Livewire**: Para comportamiento dinámico en el Frontend, utiliza componentes Livewire antes que acumular scripts de JS tradicional.
3. **No subas el `public/storage`**: Los archivos multimedia locales deben estar en `storage/app/public` de forma segura.
