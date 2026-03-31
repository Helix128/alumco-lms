#!/bin/sh
set -e

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

# Optimizar para producción
echo "optimizando cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Migraciones y Seeding (Solo si es necesario)
# El flag --force es obligatorio en producción
echo "ejecutando migraciones..."
php artisan migrate --force

# Lanzar el proceso principal (PHP-FPM)
echo "entorno listo. lanzando php-fpm..."
exec "$@"