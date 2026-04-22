# Operacion del proyecto

## Estandar local

El proyecto se desarrolla con herramientas nativas instaladas en el sistema:

- PHP 8.2 en `PATH`
- Composer en `PATH`
- Node.js 18+ y npm en `PATH`
- MySQL 8+ o MariaDB 10.4+ corriendo como servicio

No se usa XAMPP como estandar del proyecto.
No se usa Docker.

## Flujo local

### Primera instalacion

```bash
composer run setup
php artisan storage:link
```

### Desarrollo diario

```bash
composer run dev
```

Este comando levanta:

- `php artisan serve`
- `npm run dev`

### Si necesitas probar colas en local

```bash
composer run dev:queue
```

Usalo solo cuando realmente haya trabajo en cola que probar. El estandar local base sigue siendo simple y sin worker dedicado.

## Checklist local

Antes de empezar a desarrollar en una maquina nueva:

1. Confirmar `php -v`
2. Confirmar `composer --version`
3. Confirmar `node -v` y `npm -v`
4. Confirmar que MySQL/MariaDB esta levantado
5. Crear la base `biblioteca`
6. Ejecutar `composer run setup`
7. Ejecutar `php artisan storage:link`
8. Iniciar con `composer run dev`
9. Probar login con el admin inicial

## Checklist GitHub

Antes de subir cambios:

1. Ejecutar `composer run test`
2. Ejecutar `php artisan migrate:fresh --seed --force` si tocaste migraciones/seeders
3. Revisar `git status`
4. Confirmar que `.env` no se sube
5. Confirmar que `biblioteca.sql` no se sube
6. Confirmar que no hay credenciales reales en README o `.env.example`
7. Revisar el diff final
8. Crear rama de trabajo si el cambio es relevante

Recomendacion de flujo:

```bash
git checkout -b feature/nombre-corto
git add .
git commit -m "feat: descripcion corta"
git push -u origin feature/nombre-corto
```

## Checklist Ubuntu

Paquetes base recomendados:

- nginx
- php8.2-fpm
- php8.2-cli
- php8.2-mysql
- php8.2-mbstring
- php8.2-xml
- php8.2-curl
- php8.2-zip
- php8.2-bcmath
- php8.2-gd
- unzip
- git
- composer
- nodejs
- npm
- mysql-client o mariadb-client

Validaciones previas:

1. Crear usuario y base de datos de produccion
2. Preparar `.env` productivo
3. Configurar dominio real en `nginx/eliber.conf`
4. Confirmar permisos sobre `storage` y `bootstrap/cache`
5. Confirmar que `SEED_SAMPLE_DATA=false`

## Deploy

Secuencia recomendada:

```bash
cd /var/www/eliber
git pull origin master
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo chown -R www-data:www-data storage bootstrap/cache
sudo systemctl reload php8.2-fpm
sudo systemctl reload nginx
```

## Post-deploy

1. Abrir `/up`
2. Abrir `/login`
3. Iniciar sesion con una cuenta valida
4. Verificar dashboard
5. Verificar carga de socios
6. Verificar carga de materiales
7. Verificar prestamos
8. Verificar noticias con imagen
9. Verificar que el symlink de `storage` responde

## Scheduler

El proyecto tiene tareas operativas como `reservas:expirar`.
En produccion debe existir un cron de Laravel:

```bash
* * * * * cd /var/www/eliber && php artisan schedule:run >> /dev/null 2>&1
```

Sin ese cron, las reservas no expiraran automaticamente.
