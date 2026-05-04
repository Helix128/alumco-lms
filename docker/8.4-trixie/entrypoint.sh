#!/bin/sh
set -e

RUN_MIGRATIONS="${RUN_MIGRATIONS:-false}"
RUN_SEEDERS="${RUN_SEEDERS:-false}"
RUN_STORAGE_LINK="${RUN_STORAGE_LINK:-false}"
RUN_LARAVEL_OPTIMIZE="${RUN_LARAVEL_OPTIMIZE:-true}"

has_app_key() {
    if [ -n "${APP_KEY:-}" ]; then
        return 0
    fi

    if [ -f .env ] && grep -Eq '^APP_KEY=base64:.+' .env; then
        return 0
    fi

    return 1
}

echo "verificando conexión con la base de datos..."
until php artisan db:monitor > /dev/null 2>&1; do
  echo "esperando a mysql..."
  sleep 2
done

if ! has_app_key; then
    echo "APP_KEY no está configurada. Define una clave estable antes de iniciar producción."
    exit 1
fi

echo "limpiando caches previos..."
php artisan optimize:clear


if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "ejecutando migraciones..."
    php artisan migrate --force
fi

if [ "$RUN_SEEDERS" = "true" ]; then
    echo "ejecutando seeders..."
    php artisan db:seed --force
fi

if [ "$RUN_STORAGE_LINK" = "true" ]; then
    echo "verificando enlace de storage..."
    php artisan storage:link --force
fi

if [ "$RUN_LARAVEL_OPTIMIZE" = "true" ]; then
    echo "optimizando Laravel..."
    php artisan optimize
fi

echo "entorno listo. lanzando proceso principal..."
exec "$@"
