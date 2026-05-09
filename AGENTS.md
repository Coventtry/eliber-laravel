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

**Key models**: `User`, `Socio` (member), `Material`, `MaterialEjemplar` (copy-level tracking), `Prestamo` (loan), `Reserva` (reservation), `Area` (Dewey), `Institucion` (tenant). `Bibliotecario` model is legacy — do not use for new features.

**Inertia pages**: `resources/js/Pages/` — `Inertia::render('Socios/Index')` maps to `Socios/Index.vue`. PascalCase. Shared layout: `AdminLayout.vue`.

**Spanish route names**: resources use Spanish (`socios`, `materiales`, `prestamos`, `noticias`, `anotaciones`, `usuarios`). Some use explicit parameter mapping, e.g. `->parameters(['materiales' => 'material'])`.

## Auth

- Guard: `web`, provider: `App\Models\User`
- Login uses `usuario` field (NOT `email`): `Auth::attempt(['usuario' => ...])`
- REST API: `auth:sanctum` guard
- Roles via `spatie/laravel-permission`: `admin`, `bibliotecario`, `alumno`
- `admin` has all permissions; `bibliotecario` needs individual permissions

## Multi-tenancy

`tenantId()` helper in `app/helpers.php`: returns `session('admin_institucion_id')` for admin, else `auth()->user()->institucion_id`.

`App\Scopes\TenantScope` filters all queries. Every scoped model boots it in `booted()`. `institucion_id` is NEVER a form field — always set from `tenantId()` in controllers.

`Configuracion` model uses explicit `institucion_id` in queries (no TenantScope). Use `Configuracion::get(tenantId(), 'key')` / `::set(tenantId(), 'key', $value)`.

## Authorization

Every model has a Policy in `app/Policies/`. Base `Controller` uses `AuthorizesRequests` trait. All write operations call `$this->authorize()`. Check for 403s when adding routes — ensure roles/permissions are seeded.

## Validation

Form requests in `app/Http/Requests/`: `StoreSocioRequest`, `StoreMaterialRequest`, `StorePrestamoRequest`, `StoreUserRequest`, `UpdateUserRequest`, etc.

## Database & Migrations

- Migrations use `Schema::table()` with `if (Schema::hasColumn(...))` guards — safe on existing data
- Test DB: SQLite in-memory (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:` in `phpunit.xml`)
- Main tables in `biblioteca` MySQL database

## Tests

`tests/Feature/SmokeTest.php` covers main flows. Helper `createBibliotecario()` creates a `User` with admin role + permissions — required for `authorize()` checks to pass.

## Storage

```bash
php artisan storage:link
```

- QR codes: `storage/app/public/qrcodes/`
- News images: `storage/app/public/noticias/`
- User avatars: `storage/app/public/uploads/`
- Wallpapers: `storage/app/public/wallpapers/`

## Production Deploy

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Config template: `nginx/eliber.conf`