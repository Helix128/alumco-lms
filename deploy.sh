#!/bin/bash
set -e

echo "Desplegando actualizaciones..."

git pull origin main

sudo docker compose -f compose.prod.yaml up -d --build

sudo docker compose -f compose.prod.yaml exec app php artisan migrate --force
sudo docker compose -f compose.prod.yaml exec app php artisan optimize

echo "¡Actualización completada con éxito!"