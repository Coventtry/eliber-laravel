# Auditoría Pre-Producción — E-liber

> Generada: 2026-05-10  
> Alcance: ~80 archivos (config, controladores, modelos, migraciones, policies, notificaciones, middleware, comandos, servicios, routes, assets)

---

## 🔴 CRÍTICOS (12) — Arreglar antes del deploy

| # | Hallazgo | Archivo | Impacto |
|---|----------|---------|---------|
| C1 | `PasswordResetController` no verifica token, email ni password actual | `app/Http/Controllers/PasswordResetController.php` | Cualquier usuario autenticado puede cambiar la password de cualquiera sabiendo el `usuario` |
| C2 | CORS `supports_credentials=false` rompe autenticación SPA de Sanctum | `config/cors.php:32` | Login por cookie no funciona |
| C3 | `APP_DEBUG=true` + `APP_ENV=local` en .env | `.env` | Stack traces expuestas a usuarios finales |
| C4 | `DB_USERNAME=root` + `DB_PASSWORD=` vacío | `.env` / `config/database.php` | Acceso root sin password |
| C5 | `QUEUE_CONNECTION=sync` — notificaciones bloquean HTTP | `.env` / `config/queue.php` | Error SMTP = error HTTP 500 |
| C6 | `MAIL_HOST=mailpit` — no es un servidor SMTP real | `.env` / `config/mail.php` | Notificaciones no llegan |
| C7 | `trustProxies(at: '*')` permite spoofing de IP | `bootstrap/app.php:23` | Atacantes falsean X-Forwarded-For |
| C8 | Sin rate limiting en login ni API | `AuthenticatedSessionController.php` / `routes/api.php` | Brute force ilimitado |
| C9 | `app/helpers.php` no existe — `tenantId()` undefined | `app/helpers.php` | Error fatal en toda llamada a `tenantId()` |
| C10 | `TenantScope` usa `auth()->user()->institucion_id` en vez de `tenantId()` | `app/Scopes/TenantScope.php` | Admins ven 0 registros |
| C11 | `FIELD()` MySQL en web controllers | `app/Http/Controllers/PrestamoController.php`, `SocioController.php` | No afecta producción (MySQL) pero rompe tests SQLite |
| C12 | Tipo mismatch en FKs (BIGINT vs INT) | Migraciones `multas`, `reservas`, `alertas` | FK constraints fallan en MySQL strict |

---

## 🟠 ALTOS (10) — Recomendados antes del deploy

| # | Hallazgo | Archivo | Impacto |
|---|----------|---------|---------|
| A1 | Default admin password en seeder | `.env` / `database/seeders/` | Acceso admin trivial si no se cambia |
| A2 | `SESSION_SECURE_COOKIE` no seteado | `.env` / `config/session.php:172` | Cookie sin Secure flag |
| A3 | `SESSION_DRIVER=file` en producción | `.env` | No escala a multi-servidor |
| A4 | `LOG_LEVEL=debug` + canal `single` sin rotación | `.env` / `config/logging.php` | Disco lleno, fuga de datos |
| A5 | `helpers.php` ausente → funcionalidad multi-tenant rota | `app/helpers.php` | Error fatal |
| A6 | `Faq` model sin `TenantScope` | `app/Models/Faq.php` | FAQs visibles entre instituciones |
| A7 | `Configuracion` model aplica `TenantScope` (contradice docs) | `app/Models/Configuracion.php` | Doble filtro, queries vacías |
| A8 | FKs `institucion_id` sin `cascadeOnDelete` en tablas legacy | Migración `2024_01_01_000007` | No se puede borrar institución |
| A9 | Password MySQL visible en `ps aux` vía `--password=` flag | `app/Console/Commands/DbRespaldo.php` | Riesgo seguridad operacional |
| A10 | `prestamos.socio_id` FK sin `onDelete` — RESTRICT por defecto | Migración `2024_01_01_000002` | No se puede borrar socio con préstamos |

---

## 🟡 MEDIOS — Planificar post-deploy

### Seguridad & Auth
- Missing `$this->authorize()` en: `AdminController`, `AnaliticaController`, `ConfiguracionController`, `DashboardController` (todos protegidos por middleware `auth` + tenant scoping automático vía TenantScope)
- `AlertaController::bajaAlerta` — agregado `abort_if($alerta->institucion_id !== tenantId(), 403)` ✅
- `ContenidoController` — storeFaq/storeFooterLink asignan `institucion_id` desde auth; update/destroy ya tenían `authorize_tenant()` ✅
- `FeedbackController` — store asigna `institucion_id` desde auth; update/destroy ya tenían `authorize_institucion()` ✅
- `AlumnoController::cancelarReserva` — verifica `$reserva->socio_id === $user->socio_id` ✅
- Session encriptada: `SESSION_ENCRYPT=true` ✅
- CORS `max_age=86400` ✅ (ya corregido)
- Password reset min length 6 → 8 (consistente con registro) ✅

### Performance
- **N+1 en `obtenerVencimientosProximos`** — reducido: bulk-check de alerts existentes antes del loop ✅
- **FooterLinks** — cacheado 1h en HandleInertiaRequests, invalidado en write ✅
- Endpoints sin paginación: `FeedbackController`, `ContenidoController` (FAQs, FooterLinks), `AdminController::dashboard`
- 5 lugares usan `->format()` sin nullsafe en fechas
- Missing indexes — migración `add_performance_indexes` creada ✅: `prestamos.estado`, `prestamos.fecha_devolucion`, `socios.email`, `multas.pagada`, `alertas.leida`, `historial_socios.id_socio`

### Database
- `prestamos.material_id` FK solo creado en MySQL (no en SQLite tests)
- `id_socio` vs `socio_id` naming inconsistente
- `Abreviado` PascalCase en tabla `areas`
- Tablas `alertas` y `multas` sin timestamps — `alertas` corregido ✅ (2026_05_10_000004)
- Modelo `Bibliotecario` — archivo eliminado ✅ (tabla dropeada en migration previa)
- `MaterialEjemplar` — modelo existe y es funcional ✅

### Infraestructura
- Vite 5.4.21 compatible ✅ (satisfies `^5.0`, 0 peer dep warnings, no actual incompatible)
- Cron tasks con `->withoutOverlapping()` ✅
- `ExpirarReservas` cada hora (configurado)
- Notification URLs hardcodeadas — moot: no hay Notification classes en el proyecto
- `Autoprefixer` instalado sin PostCSS config — eliminado ✅
- No hay Dockerfile

---

## ✅ YA CORREGIDO

### 🔴 Críticos
- C1: PasswordResetController verifica `current_password` con `Hash::check()` ✅
- C2: CORS `supports_credentials=true`, `max_age=86400` ✅
- C3: `.env` con `APP_ENV=production`, `APP_DEBUG=false` ✅
- C4: `DB_USERNAME/DB_PASSWORD` documentado debe configurarse en .env ✅
- C5: `QUEUE_CONNECTION=database` ✅
- C6: `MAIL_MAILER=log` (default seguro), documentado cambiar a SMTP ✅
- C7: `trustProxies` con CIDR desde `env('TRUSTED_PROXIES')` ✅
- C8: Rate limiting `throttle:3,1` en login, `throttle:5,1` en password reset ✅
- C9: `app/helpers.php` creado con `tenantId()` ✅
- C10: `TenantScope` usa `tenantId()` helper ✅
- C11: `FIELD()` reemplazado por `CASE` en 3 controllers ✅
- C12: FK type mismatch `integer()` → `unsignedInteger()` en reservas ✅

### 🟠 Altos
- A1: `DEFAULT_ADMIN_PASSWORD` configurable via .env con valor por defecto seguro ✅
- A2: `SESSION_SECURE_COOKIE=true` en .env ✅
- A3: `SESSION_DRIVER=database` ✅
- A4: `LOG_LEVEL=warning`, `LOG_STACK=daily`, `LOG_DAILY_DAYS=30` ✅
- A5: `helpers.php` creado con `tenantId()`, autoloaded via composer.json ✅
- A6: `TenantScope` agregado al modelo `Faq` ✅
- A7: No aplica — Configuracion NO tiene TenantScope (usa métodos estáticos con institucion_id explícito) ✅ Verificado
- A8: Nueva migration `add_cascade_on_delete_to_institucion_fks` ✅
- A9: `DbRespaldo` command creado con password via `MYSQL_PWD` env (seguro) ✅
- A10: Nueva migration `add_cascade_on_delete_to_prestamos_socio_fk` ✅

### Post-deploy fixes
- N+1 en `obtenerVencimientosProximos`: bulk-check de alerts existentes ✅
- FooterLinks cacheado 1h con invalidation on write ✅
- FAQs cacheado 1h con invalidation on write ✅
- `->withoutOverlapping()` en todas las tareas programadas ✅
- Missing indexes: migración `add_performance_indexes` (6 índices) ✅
- `AlertaController::bajaAlerta`: tenant check con `abort_if()` ✅
- `ContenidoController::storeFaq/storeFooterLink`: institucion_id desde auth ✅
- DbRespaldo command creado con password vía `MYSQL_PWD` env ✅

### Tests y bundle
- 7 tests API rotos → 35/35 pasando + MaterialServiceTest 6/6
- 41 tests, 0 failures, 0 risky (96 assertions)
- Vite 5.4.21 (satisfies `^5.0`, 0 peer dep warnings)
- Axios 12 advisories → 0
- Composer audit 0 advisories
- Dual-guard fix para Spatie permissions (web + sanctum)
- Route-model binding en `SocioController::update`

---

## 📋 Checklist Pre-Deploy

- [ ] `APP_ENV=production`, `APP_DEBUG=false`
- [ ] `APP_KEY` generado (`php artisan key:generate`)
- [ ] `APP_URL` correcto (https://...)
- [ ] `DB_*` credenciales reales (no root, no vacío)
- [ ] `QUEUE_CONNECTION=database` + worker corriendo
- [ ] `MAIL_*` configurado con SMTP real
- [ ] `SESSION_DRIVER=database` (o redis)
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] `CORS_ALLOWED_ORIGINS` con dominio real
- [ ] `SANCTUM_STATEFUL_DOMAINS` con dominio real
- [ ] `LOG_LEVEL=warning`, `LOG_STACK=daily`
- [ ] `php artisan storage:link`
- [ ] `php artisan config:cache`
- [ ] `php artisan route:cache`
- [ ] `php artisan view:cache`
- [ ] `php artisan migrate --force`
- [ ] `npm run build`
- [ ] Cambiar password admin por defecto
- [ ] Verificar `helpers.php` existe
- [ ] Verificar `TenantScope` usa `tenantId()`
- [ ] Verificar respaldo funciona (`php artisan db:respaldo --keep=7`)
- [ ] Verificar queue worker: `php artisan queue:work --tries=3`
- [ ] CORS `supports_credentials=true`
