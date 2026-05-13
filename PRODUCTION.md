# Checklist de Producción — e-LibeR

## Seguridad

- [x] Autenticación con `usuario` + contraseña y verificación de cuenta activa
- [x] Roles y permisos granulares (spatie/laravel-permission)
- [x] Multi-tenancy con TenantScope global
- [x] Políticas de acceso (Policies) en todas las entidades
- [x] CSRF protection activa
- [x] Rate limiting en login: 10 intentos/min
- [x] Rate limiting en API pública: 30 req/min, autenticada: 60 req/min
- [x] Cambio de contraseña protegido (requiere contraseña actual + sesión activa)
- [ ] Configurar `SESSION_SECURE_COOKIE=true` en `.env` si se sirve sobre HTTPS
- [ ] Configurar `SESSION_SAME_SITE=strict` si es viable

## Infraestructura

- [x] Plantilla Nginx con cabeceras de seguridad (`X-Frame-Options`, `X-Content-Type-Options`)
- [ ] Descomentar bloque HTTPS en `nginx/eliber.conf` y configurar certbot/Let's Encrypt
- [x] `php artisan storage:link` ejecutado (fotos, QR, logos)
- [x] Cache y sesiones usan driver `database` (funcional sin Redis)
- [ ] Opcional: migrar a Redis para mejor performance bajo carga alta
- [x] Comando `reservas:expirar` cada hora vía scheduler
- [ ] Configurar cron de Laravel (`* * * * * php artisan schedule:run`)
- [ ] Configurar queue worker si se usan colas (`php artisan queue:work`)

## Base de datos

- [x] Migraciones seguras con `Schema::hasColumn()`
- [x] Soft deletes en instituciones, noticias, anotaciones
- [x] Backup automático recomendado (ver `docs/OPERACION.md`)
- [ ] Verificar que `DB_DATABASE=biblioteca` existe en MySQL
- [ ] Verificar `SEED_SAMPLE_DATA=false` en producción

## Monitoreo y errores

- [x] Página 404 personalizada
- [ ] Configurar pag 500 personalizada
- [ ] Configurar logging (daily, Slack, o serviço externo)
- [ ] Health check en `/up` (ya habilitado por Laravel)

## Funcional pendiente (post-MVP)

- [ ] Notificaciones por email (recordatorios, confirmaciones)
- [ ] Gestión de multas/sanciones por devolución tardía
- [ ] Reportes exportables (PDF/CSV de socios, materiales, préstamos)
- [ ] Auditoría de acciones de administradores
- [ ] Inventario físico (conteo cíclico / stock-taking)
- [ ] Búsqueda full-text en materiales
- [ ] App móvil para alumnos (API ya preparada con Sanctum)

## Prerequisitos de servidor

- PHP 8.2+
- MySQL 8+ / MariaDB 10.6+
- Composer 2.x
- Node.js 18+ (solo para build)
- Extensiones PHP: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `zip`, `gd`, `bcmath`
- Cron habilitado para el scheduler de Laravel
- Permisos de escritura en `storage/` y `bootstrap/cache/`
