# Checklist de Producción — E-liber

> Lista de verificación antes, durante y después del despliegue al servidor institucional.
> Para instrucciones paso a paso ver `DEPLOY.md`.

---

## Antes de desplegar

### Servidor institucional — requisitos mínimos

- [ ] PHP 8.2+ con extensiones: `pdo_mysql`, `mbstring`, `gd`, `zip`, `intl`, `openssl`, `curl`
- [ ] MySQL 8.0+ (o MariaDB 10.6+)
- [ ] Nginx (recomendado) o Apache con `mod_rewrite`
- [ ] `mysqldump` disponible (para los respaldos automáticos)
- [ ] Acceso SSH al servidor
- [ ] Certificado SSL activo para el dominio (`https://`)

---

### Archivo `.env` — configuración obligatoria

Copiar `.env.example` y completar **todos** estos valores antes de ejecutar cualquier comando:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://eliber.tuinstitucion.edu.ar

DB_HOST=127.0.0.1
DB_DATABASE=biblioteca
DB_USERNAME=<usuario_mysql_sin_root>
DB_PASSWORD=<contraseña_segura>

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true        # ← obligatorio con HTTPS
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.tuinstitucion.edu.ar
MAIL_PORT=587
MAIL_USERNAME=biblioteca@tuinstitucion.edu.ar
MAIL_PASSWORD=<contraseña_smtp>
MAIL_FROM_ADDRESS=biblioteca@tuinstitucion.edu.ar
MAIL_FROM_NAME="Biblioteca"

SANCTUM_STATEFUL_DOMAINS=eliber.tuinstitucion.edu.ar
CORS_ALLOWED_ORIGINS=https://eliber.tuinstitucion.edu.ar

DEFAULT_ADMIN_USUARIO=admin
DEFAULT_ADMIN_EMAIL=admin@tuinstitucion.edu.ar
DEFAULT_ADMIN_PASSWORD=<contraseña_segura_no_password>   # ← cambiar siempre
DEFAULT_INSTITUCION_NOMBRE=<nombre_real_de_la_institución>
DEFAULT_INSTITUCION_SLUG=<slug-sin-espacios>
```

> **⚠️ Nunca usar `APP_DEBUG=true` en producción.** Expone stack traces a los usuarios.

> **⚠️ `DEFAULT_ADMIN_PASSWORD=password` es el valor de ejemplo.** Cambiarlo siempre antes del primer seed.

---

## Durante el despliegue

### Orden de comandos (fresh install)

```bash
# 1. Dependencias
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# 2. Claves y caché
php artisan key:generate
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. Base de datos
php artisan migrate --force
php artisan db:seed --force          # crea admin + roles + áreas

# 4. Permisos de sistema de archivos
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### Cola de trabajos (queue worker)

El sistema usa notificaciones y tareas en segundo plano. El worker debe ejecutarse continuamente:

```bash
# Opción A: supervisord (recomendado)
# Agregar a /etc/supervisor/conf.d/eliber-worker.conf:
[program:eliber-worker]
command=php /var/www/eliber/artisan queue:work --tries=3 --daemon
autostart=true
autorestart=true
user=www-data

# Opción B: systemd
# Ver plantilla en servicios/eliber-queue.service
```

### Tarea programada (scheduler)

Agregar al crontab del usuario `www-data`:

```bash
* * * * * cd /var/www/eliber && php artisan schedule:run >> /dev/null 2>&1
```

El scheduler ejecuta automáticamente:
- **Cada hora** — expiración de reservas vencidas
- **Diario 01:00** — marcado de préstamos atrasados + notificaciones
- **Diario 03:00** — respaldo automático de la base de datos (retiene los últimos 7)

---

## Después de desplegar — verificación

### Funcionalidad básica

- [ ] `curl https://eliber.tuinstitucion.edu.ar/up` devuelve `{"status":"up"}` (HTTP 200)
- [ ] Login con el usuario admin creado por el seeder
- [ ] Crear un socio de prueba y eliminarlo
- [ ] Subir imagen de perfil (verifica que `storage:link` funciona)
- [ ] API pública accesible: `GET /api/v1/materiales`

### Notificaciones

- [ ] Enviar un correo de prueba desde Tinker:
  ```bash
  php artisan tinker
  > Mail::raw('Test SMTP', fn($m) => $m->to('admin@tuinstitucion.edu.ar'));
  ```

### Respaldos

- [ ] Ejecutar manualmente el primer respaldo:
  ```bash
  php artisan db:respaldo --keep=7
  ls storage/backups/
  ```

### Cola

- [ ] Verificar que el worker está activo:
  ```bash
  php artisan queue:list
  ```

---

## Puntos de seguridad para el administrador institucional

| Punto | Qué hacer |
|---|---|
| **Contraseña admin** | Cambiar desde el perfil de usuario en cuanto esté en producción |
| **HTTPS obligatorio** | El sistema redirige automáticamente si `APP_URL` usa `https://`. Sin SSL las contraseñas viajan en texto plano |
| **Usuario MySQL** | Crear un usuario específico para la app con permisos solo sobre la base `biblioteca`. **Nunca usar `root`** |
| **Archivo `.env`** | No debe ser legible por el servidor web (`chmod 640 .env`) |
| **`storage/backups/`** | Mover respaldos a ubicación externa periódicamente (NAS institucional, etc.) |
| **Logs** | `storage/logs/laravel.log` puede contener datos sensibles. Rotar con logrotate o configurar `LOG_LEVEL=error` en producción |
| **Acceso SSH** | Deshabilitar login por contraseña, usar solo claves SSH |

---

## Actualizar el sistema (deploys futuros)

```bash
git pull origin master
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart          # reinicia workers para tomar el nuevo código
```

> Los assets se compilan en el servidor antes de cada deploy. No se suben archivos compilados al repositorio.

---

## Contacto técnico

Ante errores revisar primero:
```bash
tail -f storage/logs/laravel.log
```

Para el procedimiento completo de instalación ver **`DEPLOY.md`**.
