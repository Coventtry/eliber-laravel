#!/bin/sh
set -e

# Solo el contenedor app corre migraciones (no queue ni scheduler)
if [ "${1}" = "php-fpm" ]; then
    echo "Esperando base de datos..."
    until php artisan db:show --json > /dev/null 2>&1; do
        sleep 2
    done

    echo "Ejecutando migraciones..."
    if ! php artisan migrate --force 2>&1; then
        echo "WARN: migrate falló — verificando si la app puede continuar..."
        php artisan migrate:status 2>&1 || true
    fi

    echo "Vinculando storage..."
    php artisan storage:link --force 2>/dev/null || true

    echo "Sembrando datos iniciales..."
    php artisan db:seed --class=RolesAndPermissionsSeeder --force 2>/dev/null || true

    echo "Cacheando configuración..."
    php artisan config:cache
    php artisan route:cache || echo "WARN: route:cache falló, continuando sin caché de rutas"
    php artisan view:cache || echo "WARN: view:cache falló"

    echo "App lista."
fi

exec "$@"
