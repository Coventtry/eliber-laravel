# AGENTS.md

## Dev Commands

```bash
composer run dev     # Laravel server + Vite + queue + logs (parallel)
composer run test   # Clears config, then runs tests
php artisan pint    # PHP linting/formatting
```

## Critical Quirks

- **Vue components NOT in repo**: Route render paths like `Inertia::render('Socios/Index', ...)` expect files at `resources/js/Pages/Socios/Index.vue`. Components are compiled by Vite, not version-controlled.
- **Migrations are non-destructive**: All migrations use `Schema::table()` only — never `Schema::create()`. Safe to run on production DB with data.
- **Test DB is SQLite in-memory**: `phpunit.xml` sets `DB_CONNECTION=sqlite` with `:memory:`. Factories work normally.
- **QR codes stored at `public/qrcodes/`**: Served directly, not via storage symlink. Run `php artisan storage:link` for other uploads.

## Architecture

Single Inertia.js app. Laravel handles routing, auth, DB queries. Vue 3 renders UI. No separate API.

**Service layer** (`app/Services/`): `PrestamoService`, `MaterialService`, `SocioService` contain business logic. Controllers delegate to these.

**Key models**: `Socio` (member), `Material` (item), `Prestamo` (loan), `Area` (Dewey classification)

## Validation

Use form request classes (`StoreSocioRequest`, `StoreMaterialRequest`, etc.) in `app/Http/Requests/`.

## Auth

Laravel default guard with `Bibliotecario` model. All routes except `/login` require `auth` middleware.