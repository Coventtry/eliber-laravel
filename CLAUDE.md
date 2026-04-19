# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**e-LibeR** is a Laravel 12 + Vue 3 library management system for small to medium-sized educational institutions. It provides complete control over bibliographic materials, member management, loans, thematic areas (Dewey classification), and internal communications.

- **Status**: Core features complete; multi-institution expansion planned
- **Tech Stack**: Laravel 12 (PHP 8.2), Vue 3 with Vite, Inertia.js, MySQL 8+/MariaDB, Bootstrap 4.6.2
- **Database**: Single database `biblioteca` with 12 tables, no schema destruction on migrations (safe for production)
- **Architecture**: Monolithic Inertia.js app (single project, unified .env, no separate API)

## Development Setup & Commands

### Initial Setup
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
mysql -u root biblioteca < SQL/biblioteca.sql  # Import existing data if available
php artisan migrate
php artisan storage:link
```

### Development Commands
```bash
# Run Laravel server + Vite + queue/logs in parallel
composer run dev

# Or manually (two terminals):
php artisan serve                    # http://localhost:8000
npm run dev                          # Watch Vite

# Run tests
composer run test

# Code quality
php artisan pint                     # PHP formatting/linting
```

### Production Deployment
```bash
cd /var/www/eliber
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan config:cache && php artisan route:cache && php artisan view:cache
sudo chown -R www-data:www-data storage bootstrap/cache
sudo systemctl reload php8.2-fpm
```

Use `nginx/eliber.conf` as the vhost template (replace domain accordingly).

## Architecture & Key Patterns

### Directory Structure
```
app/
  ├── Models/           # 12 Eloquent models (Socio, Material, Prestamo, Area, etc.)
  ├── Http/
  │   ├── Controllers/  # Resource-based: SocioController, MaterialController, etc.
  │   ├── Requests/     # Form validation (StoreSocioRequest, StoreMaterialRequest, StorePrestamoRequest)
  │   └── Middleware/   # HandleInertiaRequests
  └── Services/         # Business logic layer: PrestamoService, MaterialService, SocioService

routes/web.php          # All routes: auth + protected resource routes + AJAX endpoints
database/migrations/    # Schema adjustments (non-destructive, safe for existing data)
resources/views/        # Two Blade templates: app.blade.php (SPA mount), welcome.blade.php
resources/js/           # Vue components mounted by Inertia (not in repo, compiled by Vite)
```

### Core Models & Relationships

**Socio** (Member)
- One-to-many: `prestamos()`, `historial()`
- Helper methods: `full_name` attr, `prestamosActivos()`, scopes: `activos()`, `buscarEmail()`
- Fields: nombre, apellido, telefono, direccion, email, anio, division, activo (boolean)

**Material** (Bibliographic item)
- Belongs-to: `area`
- One-to-many: `prestamos()`
- One-to-one: `ubicacion()`
- Fields: titulo, autor, anio_publicacion, area_id, categoria, codigo (auto-generated), disponibilidad (stock), editorial, clasificacion_fisica (physical location)
- Scopes: `disponible()`, `porArea()`

**Prestamo** (Loan transaction)
- Belongs-to: `socio`, `material`
- One-to-many: `detalles()`, `notificaciones()`
- Fields: socio_id, material_id, fecha_prestamo, fecha_devolucion, estado (enum: activo/pendiente/atrasado/devuelto), cantidad
- Scopes: `activo()`, `atrasado()`, `pendiente()`, `devuelto()`, `vencimientoProximo()`
- Special: `link_whatsapp` attr (generates WhatsApp reminder URL for Argentine phone format)

**Area** (Dewey classification)
- One-to-many: `materiales()`
- Fields: codigo_dewey, nombre, Abreviado

**Supporting Models**: Bibliotecario (user/auth), HistorialSocio (member action log), Noticia (news), Anotacion (internal notes), Notificacion, UbicacionFisica, PrestamoDetalle

### Service Layer

**PrestamoService** — Handles loan lifecycle with transactional safety
- `crearPrestamo(socioId, materialId, cantidad, fechaDevolucion)` — Validates member status, material availability (< 3 active loans, no duplicate loans, date within 14 days), creates loan + decrements stock
- `devolverPrestamo(Prestamo)` — Increments material stock, marks as devuelto
- `extenderPrestamo(Prestamo, dias)` — Extends due date (1–30 days)
- `marcarAtrasados()` — Batch update overdue loans
- `obtenerVencimientosProximos(dias)` — For dashboard alerts

**MaterialService** — QR + code generation
- `generarCodigo(Area)` — Auto-generates sequential code: `{codigo_dewey}-{seq:3digits}` (e.g., 1300-002)
- `generarClasificacionFisica(Area, pasillo, tipo, estante, nivel)` — Physical location code: `{AREA_ABREV}-{PASILLO}-({TIPO}){ESTANTE}-{NIVEL}`
- `generarQR(Material)` — PNG QR with title, author, year, category, code; stored at `public/qrcodes/QR_{id}.png`
- `urlQR(Material)` — Retrieves existing QR URL

**SocioService** — Member lifecycle
- `darDeBaja(Socio, observaciones)` — Deactivates member + creates HistorialSocio log
- `reactivar(Socio)` — Reactivates member + logs action

### Controller Patterns

Controllers use dependency injection for services, return Inertia responses (Vue components), or JSON for AJAX endpoints.

**Key routes:**
- GET `/` — Dashboard (shows vencimientos próximos with WhatsApp links)
- `socios.*` + PATCH `/baja`, `/reactivar` — Full CRUD + soft deactivation
- `materiales.*` + GET `/qr` — Full CRUD + QR display
- `areas.*`, `noticias.*`, `anotaciones.*` — Standard CRUD
- `prestamos.index/create/store` + PATCH `/devolver`, `/extender` + GET `/devolucion` — Loan operations
- AJAX: `GET /api/socios/buscar`, `/api/materiales/disponibles`, `/api/materiales/ultimo-codigo` — Frontend autocomplete/validation

### Database Safety

Migrations use `Schema::table()` (not `Schema::create()`), preserving existing data. Safe to run in production with active data.

### Authentication

Uses Laravel's default guard with `Bibliotecario` model. Middleware: `auth` (protected routes).

## Important Notes for Future Development

### Multi-Tenant Roadmap
A `Plan_implementacion.md` exists outlining the next phase: multi-institution support (one DB, all entities get `institucion_id`). This affects all Models, Controllers, and Requests when implemented.

### Email/Notifications
.env configured for Mailpit (local dev). For production, update MAIL_MAILER, MAIL_HOST, etc.

### Storage & File Uploads
- Materials: QR codes → `storage/app/public/qrcodes/`
- News images: `storage/app/public/noticias/`
- Member avatars: `storage/app/public/uploads/` (referenced in Bibliotecario)
- Run `php artisan storage:link` to expose `public/storage` symlink

### Vue Component Mapping
Components are not version-controlled (compiled by Vite). Route render paths like `Inertia::render('Socios/Index', ...)` expect Vue files at `resources/js/Pages/Socios/Index.vue`. Naming convention: PascalCase matches directory structure.

### Validation Requests
Use dedicated form request classes (StoreSocioRequest, StoreMaterialRequest, StorePrestamoRequest) for validation. Update these when changing model requirements.

## Testing & Quality

- **Test command**: `composer run test` (clears config before running)
- **Linter**: `php artisan pint` (PHP code style)
- **Unit tests** run in isolation; use factories from `database/factories/` and seeders from `database/seeders/`

## Deployment Notes

- Domain configuration in `nginx/eliber.conf`
- SSL via Certbot (recommended)
- Use `--force` flag for production migrations (`php artisan migrate --force`)
- Always run caching commands before reload: `config:cache`, `route:cache`, `view:cache`
