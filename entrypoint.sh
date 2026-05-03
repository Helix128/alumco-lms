#!/bin/sh
set -e

RUN_LARAVEL_OPTIMIZE="${RUN_LARAVEL_OPTIMIZE:-true}"
RUN_MIGRATIONS="${RUN_MIGRATIONS:-false}"

# Esperar a que la base de datos esté lista
echo "verificando conexión con la base de datos..."
# Se puede usar un loop simple o herramientas como 'wait-for-it'
until php artisan db:monitor > /dev/null 2>&1; do
  echo "esperando a mysql..."
  sleep 2
done

# Generar llave si no existe (mira si APP_KEY tiene valor)
if [ -z "$APP_KEY" ] && ! grep -q "APP_KEY=base64:" .env; then
    echo "generando app_key..."
    php artisan key:generate --force
fi

if [ "$RUN_LARAVEL_OPTIMIZE" = "true" ]; then
    # Optimizar para producción
    echo "limpiando cache..."
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear

    echo "cacheando configuración, rutas y vistas..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

if [ "$RUN_MIGRATIONS" = "true" ]; then
    # El flag --force es obligatorio en producción. Nunca usar migrate:fresh aquí.
    echo "ejecutando migraciones..."
    php artisan migrate --force
fi

# Lanzar el proceso principal
echo "entorno listo. lanzando proceso principal..."
exec "$@"
