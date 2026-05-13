#!/bin/bash
# Deploy / actualización de eliber-laravel en producción
set -e

REPO_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$REPO_DIR"

echo "=== e-LibeR Deploy ==="

# 1. Actualizar código
echo "Pulling latest code..."
git pull origin master

# 2. Rebuild y restart
echo "Rebuilding containers..."
sudo docker compose -f docker-compose.prod.yml build --no-cache

echo "Restarting containers..."
sudo docker compose -f docker-compose.prod.yml up -d --wait --wait-timeout 120

echo "=== Deploy completado ==="
sudo docker compose -f docker-compose.prod.yml ps
