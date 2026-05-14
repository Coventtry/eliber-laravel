#!/bin/bash
# Deploy / actualización de eliber-laravel en producción
set -e

REPO_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$REPO_DIR"

echo "=== e-LibeR Deploy ==="

# 1. Actualizar código
echo "Pulling latest code..."
git pull origin deploy

# 2. Rebuild y restart
echo "Rebuilding containers..."
sudo docker compose -f docker-compose.prod.yml build

echo "Restarting containers..."
sudo docker compose -f docker-compose.prod.yml up -d --force-recreate --wait --wait-timeout 120

echo "=== Deploy completado ==="
sudo docker compose -f docker-compose.prod.yml ps
