# AGENTS.md

## Dev Commands

```bash
composer run dev       # Laravel + Vite (parallel)
composer run dev:queue # Add queue worker
composer run test     # Clears config, runs tests
composer run setup    # Fresh install (deps, .env, migrate, seed, npm)
php artisan pint      # PHP formatting
```

## Architecture

Inertia.js SPA (Laravel routing + auth + DB; Vue 3 UI) **plus** a REST API under `/api/v1/` authenticated with Laravel Sanctum. One project, one `.env`.

**Service layer** (`app/Services/`): `PrestamoService`, `MaterialService`, `SocioService`, `ReservaService` contain business logic. Controllers delegate to these.

**Key models**: `User` (auth), `Socio` (member), `Material` (item), `Prestamo` (loan), `Reserva` (reservation), `Area` (Dewey), `Institucion` (tenant)

## Auth

- Guard: `web` (session), provider: `App\Models\User`
- Login uses `usuario` field, not `email` — `Auth::attempt(['usuario' => ...])`
- REST API uses `auth:sanctum` guard
- Roles/permissions via `spatie/laravel-permission` — roles: `admin`, `bibliotecario`, `alumno`
- `admin` role has all permissions; `bibliotecario` gets permissions delegated individually

## Multi-tenancy

`TenantScope` (in `app/Scopes/`) filters all queries by `institucion_id` of the authenticated user. Applied via `booted()` in every model. `institucion_id` is never a form field — always set from `auth()->user()->institucion_id` in controllers.

## Authorization

Every model has a Policy in `app/Policies/` (auto-discovered). Base `Controller` includes `AuthorizesRequests` trait. All write operations call `$this->authorize()`. Check for 403s when adding new routes — ensure roles/permissions are seeded.

## Validation

Form request classes in `app/Http/Requests/`: `StoreSocioRequest`, `StoreMaterialRequest`, `StorePrestamoRequest`, `StoreUserRequest`, `UpdateUserRequest`, etc.

## Database & Migrations

- Migrations use `Schema::table()` with `if (Schema::hasColumn(...))` guards — safe on production DB with data
- Test DB is SQLite in-memory (set in `phpunit.xml`)
- All main tables in `biblioteca` database

## Tests

`tests/Feature/SmokeTest.php` covers all main flows. Helper `createBibliotecario()` creates a `User` with admin role + permissions — required for `authorize()` checks to pass.

## Storage

- QR codes: `storage/app/public/qrcodes/`
- News images: `storage/app/public/noticias/`
- Run `php artisan storage:link` once after setup

## Production Deploy

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Config template: `nginx/eliber.conf`
