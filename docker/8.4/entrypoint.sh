#!/bin/sh
set -e

RUN_LARAVEL_OPTIMIZE="${RUN_LARAVEL_OPTIMIZE:-true}"
RUN_MIGRATIONS="${RUN_MIGRATIONS:-false}"

# Esperar a que la base de datos este lista
echo "verificando conexion con la base de datos..."
until php artisan db:monitor > /dev/null 2>&1; do
  echo "esperando a mysql..."
  sleep 2
done

if [ -z "$APP_KEY" ] && ! grep -q "APP_KEY=base64:" .env; then
    echo "generando app_key..."
    php artisan key:generate --force
fi

if [ "$RUN_LARAVEL_OPTIMIZE" = "true" ]; then
    echo "limpiando cache..."
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear

    echo "cacheando configuracion, rutas y vistas..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "ejecutando migraciones..."
    php artisan migrate --force
fi

echo "entorno listo. lanzando proceso principal..."
exec "$@"
