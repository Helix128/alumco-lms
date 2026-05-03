#!/bin/bash
# Salir inmediatamente si un comando falla
set -e

# ==========================================
# 1. Análisis de Argumentos
# ==========================================
BUILD_MODE="parallel"

# Recorrer todos los argumentos pasados al script
for arg in "$@"; do
    if [ "$arg" == "--safe" ]; then
        BUILD_MODE="sequential"
    fi
done

echo "🚀 Iniciando despliegue de actualizaciones..."

# 2. Obtener el código nuevo
git pull origin main

echo "📦 Evaluando estrategia de construcción..."

# ==========================================
# 3. Lógica de Construcción (Zero-Downtime)
# ==========================================
if [ "$BUILD_MODE" == "sequential" ]; then
    echo "🐢 [Modo Secuencial]: Activado. Construyendo por capas para proteger la red..."
    
    echo " -> Construyendo capa base..."
    sudo docker build --target base -t app-base-cache:latest .
    
    echo " -> Construyendo dependencias frontend..."
    sudo docker build --target frontend-builder -t app-frontend-cache:latest .
    
    echo " -> Ensamblando imagen final de producción..."
    sudo docker compose -f compose.prod.yaml build app
else
    echo "⚡ [Modo Paralelo]: Activado. Usando máxima concurrencia de BuildKit..."
    sudo docker compose -f compose.prod.yaml build app
fi

# ==========================================
# 4. Orquestación y Despliegue
# ==========================================
echo "🔄 Poniendo la aplicación en modo mantenimiento..."
sudo docker compose -f compose.prod.yaml exec app php artisan down || true

echo "🚢 Reiniciando los contenedores con la nueva imagen..."
sudo docker compose -f compose.prod.yaml up -d

echo "🗄️ Ejecutando migraciones..."
sudo docker compose -f compose.prod.yaml exec app php artisan migrate --force

echo "⚡ Optimizando cachés de Laravel..."
sudo docker compose -f compose.prod.yaml exec app php artisan optimize
sudo docker compose -f compose.prod.yaml exec app php artisan view:cache
sudo docker compose -f compose.prod.yaml exec app php artisan event:cache

echo "✅ Levantando la aplicación..."
sudo docker compose -f compose.prod.yaml exec app php artisan up || true

echo "🎉 ¡Actualización completada con éxito!"