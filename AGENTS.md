# AGENTS.md

## Dev Commands

```bash
composer run dev       # Laravel + Vite (parallel)
composer run dev:queue # + queue worker
composer run test      # config:clear → tests (SQLite in-memory)
composer run setup     # Fresh install (deps, .env, migrate, seed, npm)
php artisan pint       # PHP formatting (Laravel Pint)
php artisan optimize:clear  # Wipes all caches (config, route, view, etc.)
```

**Single test:**
```bash
php artisan test --filter test_name
```

## Architecture

Inertia.js SPA (Laravel + Vue 3) + REST API under `/api/v1/` (Sanctum auth). One project, one `.env`.

**Service layer** (`app/Services/`): `PrestamoService`, `MaterialService`, `SocioService`, `ReservaService`. Controllers delegate to these.

**Key models**: `User`, `Socio` (member), `Material`, `Prestamo` (loan), `Reserva` (reservation), `Area` (Dewey), `Institucion` (tenant).

**Inertia pages**: `resources/js/Pages/` — `Inertia::render('Socios/Index')` maps to `Socios/Index.vue`. PascalCase. Shared layout: `AdminLayout.vue`.

**Spanish route names**: resources use Spanish (`socios`, `materiales`, `prestamos`, `noticias`, `anotaciones`, `usuarios`). Some use explicit parameter mapping, e.g. `->parameters(['materiales' => 'material'])`.

**Bootstrap 4 + jQuery legacy**: Vite config globally exposes jQuery (required by Bootstrap 4). Avoid jQuery in new Vue 3 components but be aware it exists globally. `popper.js` is pre-optimized.

**API Resource classes**: All `/api/v1/` responses use `app/Http/Resources/` — `Resource::collection()`, `new Resource()`, or `Resource::make()`. Never return raw models. Nested relations use `$this->whenLoaded()`.

**l5-swagger**: `darkaonline/l5-swagger` installed for API docs; annotate new API controller routes.

## Auth

- Guard: `web`, provider: `App\Models\User`
- Login uses `usuario` field (NOT `email`): `Auth::attempt(['usuario' => ...])`
- REST API: `auth:sanctum` guard
- Roles via `spatie/laravel-permission`: `admin`, `bibliotecario`, `alumno`
- `admin` has all permissions; `bibliotecario` needs individual permissions
- Policies in `app/Policies/` — `$this->authorize()` on all write ops. When adding routes, ensure roles/permissions are seeded (403 otherwise).
- Session driver defaults to `database` (config/session.php)
- Ziggy (`tightenco/ziggy`) available for `route()` helper in Inertia pages

## Multi-tenancy

`tenantId()` helper in `app/helpers.php` (autoloaded via `composer.json` files): returns `session('admin_institucion_id')` for admin, else `auth()->user()->institucion_id`.

`App\Scopes\TenantScope` filters all queries via `tenantId()`. Every scoped model boots it in `booted()`. `institucion_id` is NEVER a form field — always set from `tenantId()` in controllers.

`Configuracion` model uses explicit `institucion_id` in queries (no TenantScope). Use `Configuracion::get(tenantId(), 'key')` / `::set(tenantId(), 'key', $value)`.

## Database & Migrations

- Most migrations use `Schema::table()` with `if (Schema::hasColumn(...))` guards — generally safe on existing data (some newer migrations skip the guard)
- Test DB: SQLite in-memory (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:` in `phpunit.xml`)
- Main tables in `biblioteca` MySQL database

## Tests

`tests/Feature/SmokeTest.php` covers main flows. Private helper `createBibliotecario()` creates a `User` with admin role + all permissions — required for `authorize()` checks to pass. This is not a global helper; same file also has `createAlumno()`, `createSocio()`, `createArea()`, `createInstitucion()`.

## Deploy Checklist

Before deploying to production, verify each item:

### .env Production
- [ ] `APP_ENV=production` | `APP_DEBUG=false`
- [ ] `APP_KEY` generated via `php artisan key:generate`
- [ ] `APP_URL` = production URL (https://...)
- [ ] `DB_USERNAME` / `DB_PASSWORD` = real credentials (not root/empty)
- [ ] `QUEUE_CONNECTION=database` (not sync)
- [ ] `MAIL_MAILER=smtp` with real SMTP credentials
- [ ] `SESSION_DRIVER=database` (not file)
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] `LOG_LEVEL=warning` | `LOG_STACK=daily`
- [ ] `CORS_ALLOWED_ORIGINS` = production domain only
- [ ] `SANCTUM_STATEFUL_DOMAINS` = production domain

### Known Errors (all items fixed — see AUDITORIA.md for full audit)
- A7: Configuracion model no aplica TenantScope (intencional, usa métodos estáticos) - no es un bug
- Items postergables: missing policies (controllers protegidos por auth + TenantScope), Vite peer dep warnings (esbuild moderate, dev-only)

### Commands (in order)
```bash
php artisan key:generate
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan queue:table       # if not done
php artisan migrate --force   # for jobs table
npm run build
```

### Post-deploy
- [ ] Queue worker running: `php artisan queue:work --tries=3 --daemon`
- [ ] Scheduler running: `* * * * * cd /path && php artisan schedule:run >> /dev/null 2>&1`
- [ ] `php artisan db:respaldo --keep=7` works and file is writable
- [ ] Admin can log in and see data (TenantScope fix verified)
- [ ] Alerts (internal) visible in dashboard
- [ ] Storage symlink works (`public/storage` → `storage/app/public`)

## Storage

- QR codes: `storage/app/public/qrcodes/`
- News images: `storage/app/public/noticias/`
- Avatars: `storage/app/public/uploads/`
- Backups: `storage/app/backups/`
- Run `php artisan storage:link` after deploy

## Quirks

- **Vite** aliases jQuery as global for Bootstrap 4 compat (see `vite.config.js`)
- `Alerta::withoutGlobalScopes()` used in `PrestamoService` for alert CRUD
- **Backup**: `php artisan db:respaldo` runs `mysqldump` daily at 03:00, keeps 7 days; files in `storage/app/backups/`
- Requires `mysqldump` installed on the server for backup to function
- `FLied()` MySQL function avoided — use `CASE` for SQLite-compatible ordering

## Queue

- Default: `database` driver (jobs table)
- Worker: `php artisan queue:work --tries=3`
- Failed jobs stored in `failed_jobs` table

## Reference

Full pre-deploy audit: `AUDITORIA.md`
