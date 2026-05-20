# AGENTS.md

## Dev Commands

```bash
composer run dev          # artisan serve + Vite (parallel via concurrently)
composer run dev:queue    # + queue worker
composer run test         # config:clear → tests (SQLite in-memory)
composer run setup        # Fresh install (composer install, .env, key:generate, migrate --seed, npm, npm build)
php artisan pint          # Laravel Pint (PHP formatting)
php artisan test --filter test_name  # Single test
```

## Architecture

Inertia.js SPA (Laravel 12 + Vue 3) + REST API under `/api/v1/` (Sanctum auth). One project, one `.env`.

**Service layer** (`app/Services/`): `PrestamoService`, `MaterialService`, `SocioService`, `ReservaService`, `MultaService`. Controllers delegate business logic. All write ops use `$this->authorize()`.

**Inertia pages**: `resources/js/Pages/` — `Inertia::render('Socios/Index')` → `Socios/Index.vue`. PascalCase. Shared layout: `AdminLayout.vue`.

## Auth & Roles

- **Login uses `usuario` field** (NOT `email`): `Auth::attempt(['usuario' => ...])`
- Guard: `web`, provider: `App\Models\User`
- Roles via `spatie/laravel-permission` (two guards: `web` + `sanctum`):
  - `admin` — all 12 permissions including `ver-reportes`
  - `bibliotecario` — all 9 except `ver-reportes`
  - `alumno` — `ver-materiales`, `crear-reservas`, `ver-reservas`
- Users have `activo` boolean; inactive accounts rejected at login
- All protected routes use `auth` middleware; REST API uses `auth:sanctum`
- Policies in `app/Policies/` — `$this->authorize()` on all write ops. When adding routes, seed roles/permissions (403 otherwise).
- Ziggy (`tightenco/ziggy`) available for `route()` helper in Inertia pages
- Session driver defaults to `database`

## Multi-Tenancy

- `tenantId()` helper (`app/helpers.php`): returns `session('admin_institucion_id')` for admin, else `auth()->user()->institucion_id`
- `App\Scopes\TenantScope` applied via `booted()` on scoped models — auto-filters by `tenantId()`
- `institucion_id` is NEVER a form field — set from `tenantId()` in controllers
- Avoid `FIELD()` MySQL — use `CASE` for SQLite-compatible ordering
- `User` model is NOT tenant-scoped

### Configuracion quirk

`Configuracion` model **does** boot `TenantScope`, but its static `get()`/`set()` methods take explicit `institucion_id`:
```php
Configuracion::get(tenantId(), 'key', $default);
Configuracion::set(tenantId(), 'key', $value);
```

## Routes (Spanish resource names)

Resources use Spanish names with explicit parameter mapping:
```php
Route::resource('materiales', ...)->parameters(['materiales' => 'material']);
Route::resource('usuarios', ...)->parameters(['usuarios' => 'user']);
```

**Route groups:**
- `/admin/*` (role:admin) — users CRUD, feedback Kanban, FAQs/footer/anuncio, analytics, config, institution switcher
- `/alumno/*` (role:alumno) — dashboard, mis-prestamos, mis-reservas, catalogo, reservas
- Export: `/exportar/{socios|materiales|prestamos|multas}/{csv|pdf}`
- AJAX: `/api/socios/buscar`, `/api/socios/{socio}/prestamos`, `/api/materiales/disponibles`, `/api/materiales/ultimo-codigo`
- REST API: `/api/v1/*` (public GET materiales/noticias; Sanctum for CRUD on socios, prestamos, reservas, areas, alertas, multas, usuarios)

API responses use `app/Http/Resources/` — `Resource::collection()`, `new Resource()`, or `Resource::make()`. Never return raw models. Nested relations use `$this->whenLoaded()`. Annotate new API routes with OpenAPI (`darkaonline/l5-swagger`).

## Tests

- **SQLite in-memory** (`phpunit.xml`: `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`)
- `tests/Feature/SmokeTest.php` covers main flows
- `tests/Support/TestHelpers.php` trait provides: `createBibliotecario()` (creates User + admin role + all permissions), `createAlumno()`, `createSocio()`, `createArea()`, `createInstitucion()`, `actingAsBibliotecario()`
- All service tests use `RefreshDatabase` + SQLite-safe ordering (no `FIELD()`)

## Quirks

- **jQuery global**: Vite config aliases `jquery` to `window.jQuery` for Bootstrap 4 compat. Avoid jQuery in new Vue 3 components.
- **`popper.js`** pre-optimized in Vite `optimizeDeps`
- **`Alerta::withoutGlobalScopes()`** used in `PrestamoService` for alert CRUD (alerts have TenantScope)
- **`barryvdh/laravel-dompdf`** for PDF exports; **`simplesoftwareio/simple-qrcode`** for QR codes
- **Manifest icons**: PWA icons in `manifest.json` must be square with exact pixel dimensions (192×192, 512×512). Non-square images cause browser warnings.
- **Dashboard logo fallback**: `<img>` tags use `@error="logoError = true"` to show a `bi-image` icon instead of a broken image (pattern in `Dashboard.vue` and `Alumno/Dashboard.vue`).
- **Mailer in dev**: Notifications (PrestamoVencido, etc.) are `ShouldQueue`. If no SMTP/mailpit running, set `MAIL_MAILER=log` in `.env` to avoid 500 errors.
- **Missing migrations**: If you hit "table not found" errors, run `php artisan migrate` — many tables (multas, material_ejemplares) were added after the initial migration.

## Queue & Scheduler

- Default driver: `database` (jobs table)
- Worker: `php artisan queue:work --tries=3`
- Scheduler (`routes/console.php`):
  - `reservas:expirar` — hourly
  - `prestamos:marcar-atrasados` — daily at 01:00
  - `db:respaldo --keep=7` — daily at 03:00 (requires `mysqldump`)
- Backup files in `storage/app/backups/`

## Storage

- QR codes: `storage/app/public/qrcodes/`
- News images: `storage/app/public/noticias/`
- Avatars: `storage/app/public/uploads/`
- Wallpapers: `storage/app/public/wallpapers/`
- Run `php artisan storage:link` after deploy

## Deploy

Two options:
1. **Docker** (`docker-compose.prod.yml`): `./deploy.sh` (git pull → rebuild → up)
2. **Bare-metal** (Nginx reverse-proxy): `nginx/eliber.conf` as vhost template

Production commands (in order): `key:generate`, `storage:link`, `config:cache`, `route:cache`, `view:cache`, `migrate --force`, `queue:table` (if needed), `migrate --force` (jobs table), `npm run build`.

Full audit/production checklist: `AUDITORIA.md`, `CHECKLIST_PRODUCCION.md`, `DEPLOY.md`.
