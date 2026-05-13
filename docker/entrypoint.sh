#!/bin/sh
set -e

# Solo el contenedor app corre migraciones (no queue ni scheduler)
if [ "${1}" = "php-fpm" ]; then
    echo "Esperando base de datos..."
    until php artisan db:show --json > /dev/null 2>&1; do
        sleep 2
    done

    echo "Ejecutando migraciones..."
    php artisan migrate --force

    echo "Vinculando storage..."
    php artisan storage:link --force 2>/dev/null || true

    echo "Cacheando configuración..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    echo "App lista."
fi

exec "$@"
