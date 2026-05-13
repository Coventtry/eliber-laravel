# AGENTS.md

## Dev Commands

```bash
composer run dev          # php artisan serve + Vite (concurrent)
composer run dev:queue    # + queue worker
composer run test         # config:clear → phpunit (SQLite in-memory)
php artisan pint           # PHP formatting (Laravel Pint)
php artisan optimize:clear # cache bust when things break
npm run build              # Vite production build
# Single test: php artisan test --filter test_name
```

## Architecture

**Inertia SPA** (Laravel 12 + Vue 3) with a **REST API** under `/api/v1/` (Sanctum). One project, one `.env`.

**Auth provider**: `App\Models\User` (guard `web`). Login uses `usuario` field — `Auth::attempt(['usuario' => ...])`. Roles via `spatie/laravel-permission`: `admin` (all perms), `bibliotecario` (delegated), `alumno`.

**Permissions used in policies**: `gestionar-socios`, `gestionar-materiales`, `gestionar-prestamos`, `gestionar-areas`, `gestionar-noticias`, `gestionar-anotaciones`, `gestionar-usuarios`, `gestionar-multas`, `ver-reportes`

**Service layer** (`app/Services/`): `PrestamoService`, `MaterialService`, `SocioService`, `ReservaService` — controllers delegate business logic. All write operations use `$this->authorize()`.

**Multi-tenancy**: `TenantScope` filters every query by `auth()->user()->institucion_id` via `booted()` in every model. `institucion_id` is never user-supplied — always set from auth in controllers.

## Key Models

`User` (auth), `Socio`, `Material`, `Prestamo`, `Reserva`, `Area` (Dewey), `Institucion` (tenant), `Noticia`, `Anotacion`, `Alerta`, `HistorialSocio`, `Configuracion`, `Faq`, `FeedbackCard`

## Loan Rules (PrestamoService)

- Max 3 active loans per socio, no duplicate material active
- Date range: today to `dias_prestamo` config (default 14 days)
- Extension: 1–30 days
- Real stock: `disponibilidad - disponibilidad_reservada`

## Material Codes (MaterialService)

- Code format: `{codigo_dewey}-{seq:3digits}` (e.g. `1300-002`)
- QR stored as `qrcodes/QR_{id}.png` (or `.svg` fallback)

## Database & Migrations

- Migrations use `Schema::table()` with `Schema::hasColumn()` guards — safe on prod
- Test DB: SQLite `:memory:` (set in `phpunit.xml`)
- Seed sample data: set `SEED_SAMPLE_DATA=true` in `.env`, run `php artisan migrate:fresh --seed`

## Tests

- `tests/Feature/SmokeTest.php` covers all main flows
- Helper `createBibliotecario()` creates a `User` with `admin` role + all permissions
- Models with TenantScope need `forceCreate()` when creating outside auth context
- API tests authenticate with `Sanctum`: `actingAs($user, 'sanctum')`

## Storage

- QR codes: `storage/app/public/qrcodes/`
- News images: `storage/app/public/noticias/`
- Avatars: `storage/app/public/uploads/`
- Run `php artisan storage:link` after deploy

## Quirks

- **Vite** aliases jQuery as global for Bootstrap 4 compat (see `vite.config.js`)
- `Alerta::withoutGlobalScopes()` used in `PrestamoService` for alert CRUD
- Transient `Bibliotecario` model exists (separate `bibliotecarios` table) but is NOT the auth provider — `User` is
- **Backup**: `php artisan db:respaldo` runs `mysqldump` daily at 03:00, keeps 7 days; files in `storage/app/backups/`
- Requires `mysqldump` installed on the server for backup to function

## Email Notifications

3 Notification classes in `app/Notifications/` (all implement `ShouldQueue`):
- `PrestamoVencido` — sent by `PrestamoService::marcarAtrasados()` when a loan is overdue
- `PrestamoProximoVencer` — sent by `PrestamoService::obtenerVencimientosProximos()` for loans due within 4 days
- `ReservaAprobada` — sent by `ReservaService::aprobarReserva()` when a reservation is approved

Email is sent to the `User` linked via `User.socio_id` → `Prestamo.socio_id`. If no User is linked (`socio_id` is null), no notification is sent.

Mail config in `.env` uses SMTP (defaults to `log` driver as fallback). For local dev, `MAIL_MAILER=log` writes to `storage/logs/laravel.log`.
