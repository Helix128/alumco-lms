# Operación de recursos multimedia

## Migración inicial sin pérdida

No recrees los contenedores antes de respaldar. El primer montaje de `prod-media` oculta cualquier archivo que solo exista dentro del contenedor PHP actual.

1. Respalda MySQL con `mysqldump` o la herramienta habitual del servidor.
2. Identifica el contenedor PHP actual con `docker compose -f compose.prod.yaml ps app`.
3. Extrae los archivos antes de desplegar: `docker cp <contenedor>:/var/www/html/storage/app/public ./backup-storage-public`.
4. Verifica el respaldo (cantidad, tamaño y una muestra de archivos) y guárdalo junto al dump.
5. Despliega, ejecuta migraciones y copia el respaldo a `storage/app/public` solo para la etapa de importación legado.
6. Ejecuta `php artisan media:migrate-legacy --dry-run`; revisa faltantes, MIME incorrectos y huérfanos.
7. Ejecuta `php artisan media:migrate-legacy` y deja que `media-worker` termine la cola.
8. Ejecuta `php artisan media:audit`. Los huérfanos se informan y nunca se borran automáticamente.

## Respaldo y restauración local

Respalda diariamente MySQL y el volumen `prod-media` en la misma ventana lógica. Para el volumen, monta un contenedor temporal de solo lectura y genera un archivo tar en un destino externo. Conserva checksums SHA-256 y prueba restauraciones periódicas.

Para restaurar: detén las escrituras, restaura la base de datos y luego el volumen en una ubicación vacía, levanta los servicios y ejecuta `media:audit`. Una base y un volumen de fechas distintas pueden dejar referencias faltantes.

## Capacidad y mantenimiento

Salud LMS muestra uso, faltantes, fallos, trabajos atascados y recursos sin referencia. El sistema avisa al 80% y bloquea cargas al 90% o con menos de 5 GB libres. `media:cleanup` elimina sesiones incompletas después de 24 horas y recursos sin referencias después de siete días.

## S3 / Cloudflare R2

Configura `MEDIA_DISK=s3`, credenciales `AWS_*`, endpoint y bucket privados. Configura CORS para permitir `PUT`, `GET` y `HEAD` desde los orígenes del LMS, aceptar `Range` y exponer `ETag`, `Accept-Ranges` y `Content-Range`; conserva el bucket sin acceso público. Las cargas usan multipart directo con URLs prefirmadas. Tras copiar objetos existentes, compara checksums y ejecuta `media:audit` antes del cambio. La entrega usa URLs firmadas de corta duración.
