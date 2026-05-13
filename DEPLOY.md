# Deploy al Servidor Institucional

> Instrucciones paso a paso para poner E-liber en producción.

---

## 1. Requisitos del Servidor

- PHP ^8.2 (con extensiones: pdo_mysql, mbstring, xml, gd, zip, json, curl, openssl)
- MySQL 8.0+ (o MariaDB 10.6+)
- Composer 2.x
- Node.js 20+ y npm
- `mysqldump` (para backups automáticos)
- Acceso SSH con permisos de escritura en el directorio del proyecto

---

## 2. Preparar el Servidor

```bash
# Clonar el proyecto (o copiar los archivos)
git clone <repo-url> /var/www/eliber
cd /var/www/eliber

# Instalar dependencias PHP
composer install --optimize-autoloader --no-dev

# Instalar dependencias JS y compilar assets
npm ci
npm run build

# Configurar .env
cp .env.example .env
nano .env   # <-- ver sección 3
```

---

## 3. Configurar `.env` (PRODUCCIÓN)

Editar estas variables en el servidor:

| Variable | Valor |
|----------|-------|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://eliber.tuinstitucion.edu.ar` |
| `APP_KEY` | *(generar con `php artisan key:generate`)* |
| `DB_DATABASE` | `biblioteca` |
| `DB_USERNAME` | *(usuario MySQL real, NO root)* |
| `DB_PASSWORD` | *(password real)* |
| `MAIL_MAILER` | `smtp` |
| `MAIL_HOST` | *(servidor SMTP real)* |
| `MAIL_PORT` | `587` |
| `MAIL_USERNAME` | *(usuario SMTP)* |
| `MAIL_PASSWORD` | *(password SMTP)* |
| `MAIL_FROM_ADDRESS` | `noreply@tudominio.edu.ar` |
| `CORS_ALLOWED_ORIGINS` | `https://eliber.tuinstitucion.edu.ar` |
| `SANCTUM_STATEFUL_DOMAINS` | `eliber.tuinstitucion.edu.ar` |
| `SESSION_SECURE_COOKIE` | `true` |
| `SESSION_DRIVER` | `database` |
| `QUEUE_CONNECTION` | `database` |
| `TRUSTED_PROXIES` | `10.0.0.0/8,172.16.0.0/12,192.168.0.0/16` *(o IP real del LB)* |
| `DEFAULT_ADMIN_PASSWORD` | *(contraseña segura para el admin)* |
| `DEFAULT_ADMIN_USUARIO` | `admin` |
| `DEFAULT_ADMIN_EMAIL` | `admin@tudominio.edu.ar` |

---

## 4. Comandos de Deploy (en orden)

```bash
php artisan key:generate
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan queue:table       # si no existe la migración
php artisan migrate --force   # para la tabla de jobs
```

### Seeders (solo en fresh install)

```bash
php artisan db:seed --force
```

**⚠️ Importante:** En producción, cambiar `DEFAULT_ADMIN_PASSWORD` en `.env` por una contraseña segura antes del seed.

---

## 5. Post-Deploy

### Queue Worker

```bash
# Iniciar worker (no se detiene hasta Ctrl+C)
php artisan queue:work --tries=3 --daemon

# Opcional: correr como servicio systemd
# Ver SERVICIOS/eliber-queue.service
```

### Scheduler (Cron)

Agregar al crontab del usuario `www-data` (o el que ejecuta PHP):

```bash
* * * * * cd /var/www/eliber && php artisan schedule:run >> /dev/null 2>&1
```

### Verificar Funcionamiento

```bash
# Verificar health check
curl https://eliber.tuinstitucion.edu.ar/up

# Verificar backup
php artisan db:respaldo --keep=7

# Verificar cola de trabajos
php artisan queue:list

# Verificar workers activos
php artisan queue:status

# Probar login vía web
# - Ir a https://eliber.tuinstitucion.edu.ar/login
# - Ingresar con admin:admin (o el usuario admin configurado)

# Probar API pública
curl https://eliber.tuinstitucion.edu.ar/api/v1/materiales
curl https://eliber.tuinstitucion.edu.ar/api/v1/noticias

# Verificar storage symlink
ls -la public/storage
# debe apuntar a: ../storage/app/public
```

---

## 6. Postergado (para después del deploy inicial)

| Tarea | Prioridad | Detalle |
|-------|-----------|---------|
| Configurar SMTP real | Alta | Cambiar `MAIL_MAILER=log` → `smtp` con credenciales reales |
| Cambiar contraseña admin | Alta | `DEFAULT_ADMIN_PASSWORD` del seed |
| Verificar backups automáticos | Media | `php artisan db:respaldo --keep=7` debe funcionar |
| Configurar SSL/HTTPS | Alta | Certbot, Let's Encrypt o certificado institucional |
| Migrar a Bootstrap 5 | Baja | Elimina dependencia de jQuery/popper.js |
| Agregar monitoreo | Baja | New Relic, Laravel Pulse, o similar |
| Migrar caché a Redis | Baja | Mejora performance si hay muchos usuarios concurrentes |

---

## 7. Solución de Problemas

### Error: "No application encryption key specified"
```bash
php artisan key:generate
```

### Error: "No such file or directory" en storage
```bash
php artisan storage:link
```

### Error: "Target class does not exist" en rutas
```bash
php artisan config:clear
php artisan route:clear
# Luego volver a cachear: config:cache + route:cache
```

### Error: "Connection refused" en MySQL
```bash
# Verificar que MySQL está corriendo
systemctl status mysql

# Verificar credenciales en .env
```

### Error: "Class not found" o "Undefined function tenantId()"
```bash
composer dump-autoload
```

### Error: Permisos denegados en storage/logs
```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### Error: Las notificaciones no llegan
```bash
# Verificar configuración SMTP
php artisan tinker
> Mail::raw('Test', fn($msg) => $msg->to('admin@ejemplo.com'));

# Verificar cola de trabajos
php artisan queue:listen --tries=1
```

### Error: Error 500 sin mensaje
```bash
# Temporalmente cambiar LOG_LEVEL=debug en .env
# O revisar logs:
tail -f storage/logs/laravel.log
```

---

## 8. Arquitectura para Referencia

```
/var/www/eliber/
├── app/
│   ├── Console/Commands/   # db:respaldo, marcar-atrasados, expirar-reservas
│   ├── Exceptions/         # Handler personalizado (404 → Inertia)
│   ├── Http/
│   │   ├── Controllers/    # Web + Api/
│   │   ├── Middleware/      # HandleInertiaRequests
│   │   └── Requests/       # Form requests con validación
│   ├── Models/             # Eloquent models (todos con TenantScope)
│   ├── Notifications/      # ReservaAprobada, PrestamoVencido, PrestamoProximoVencer
│   ├── Policies/           # Spatie policies para autorización
│   ├── Scopes/             # TenantScope (filtra por tenantId())
│   └── Services/           # PrestamoService, MaterialService, etc.
├── bootstrap/app.php       # Middleware, trustProxies, excepciones
├── config/
│   ├── cors.php           # supports_credentials=true, max_age=86400
│   └── ...
├── database/
│   └── migrations/        # 38 migrations ordenadas
├── public/
│   └── storage → storage/app/public  # symlink
├── resources/js/           # Vue 3 + Inertia SPA
├── routes/
│   ├── web.php            # Rutas SPA (Inertia)
│   ├── api.php            # API REST /api/v1/ (Sanctum)
│   └── console.php        # Tareas programadas
├── storage/
│   ├── app/public/         # QRs, noticias, avatares
│   └── backups/           # Respaldos MySQL (db:respaldo)
├── AUDITORIA.md           # Auditoría completa de seguridad
├── AGENTS.md              # Contexto para desarrollo
└── DEPLOY.md              # Este archivo
```
