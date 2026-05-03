#!/bin/bash
# Salir inmediatamente si un comando falla
set -e

# ==========================================
# 1. Configuracion
# ==========================================
BUILD_MODE="parallel"

# IMPORTANTE: Cambia esta ruta si tu archivo tiene otro nombre
# Ejemplo: "Dockerfile.prod" o "docker/8.4-trixie/Dockerfile"
DOCKERFILE_PATH="docker/8.4-trixie/Dockerfile"

# Recorrer todos los argumentos pasados al script
for arg in "$@"; do
    if [ "$arg" == "--safe" ]; then
        BUILD_MODE="sequential"
    fi
done

echo "Iniciando despliegue de actualizaciones..."

# 2. Obtener el codigo nuevo
git pull origin main

echo "Evaluando estrategia de construccion..."

export MAX_CONCURRENT_STAGES=1
# ==========================================
# 3. Logica de Construccion (Zero-Downtime)
# ==========================================
if [ "$BUILD_MODE" == "sequential" ]; then
    echo "[Modo Secuencial]: Activado. Construyendo por capas para proteger la red..."
    
    echo " -> Construyendo capa base..."
    sudo docker build -f "$DOCKERFILE_PATH" --target base -t app-base-cache:latest .
    
    echo " -> Construyendo dependencias frontend..."
    sudo docker build -f "$DOCKERFILE_PATH" --target frontend-builder -t app-frontend-cache:latest .
    
    echo " -> Ensamblando imagen final de produccion..."
    sudo docker compose -f compose.prod.yaml build app
else
    echo "[Modo Paralelo]: Activado. Usando maxima concurrencia de BuildKit..."
    sudo docker compose -f compose.prod.yaml build app
fi

# ==========================================
# 4. Orquestacion y Despliegue
# ==========================================
echo "Poniendo la aplicacion en modo mantenimiento..."
sudo docker compose -f compose.prod.yaml exec app php artisan down || true

echo "Reiniciando los contenedores con la nueva imagen..."
sudo docker compose -f compose.prod.yaml up -d

echo "Ejecutando migraciones..."
sudo docker compose -f compose.prod.yaml exec app php artisan migrate --force

echo "Optimizando caches de Laravel..."
sudo docker compose -f compose.prod.yaml exec app php artisan optimize
sudo docker compose -f compose.prod.yaml exec app php artisan view:cache
sudo docker compose -f compose.prod.yaml exec app php artisan event:cache

echo "Levantando la aplicacion..."
sudo docker compose -f compose.prod.yaml exec app php artisan up || true

echo "Actualizacion completada con exito!"
