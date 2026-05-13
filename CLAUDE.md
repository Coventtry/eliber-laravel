# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**e-LibeR** is a Laravel 12 + Vue 3 library management system for small to medium-sized educational institutions. It provides complete control over bibliographic materials, member management, loans, reservations, thematic areas (Dewey classification), and internal communications.

- **Tech Stack**: Laravel 12 (PHP 8.2), Vue 3 with Vite, Inertia.js, MySQL 8+/MariaDB, Bootstrap 4.6.2
- **Architecture**: Monolithic Inertia.js SPA + REST API under `/api/v1/` (Laravel Sanctum auth)
- **Multi-tenancy**: Every entity has `institucion_id`; all queries must scope to the authenticated user's institution

## Development Commands

```bash
# Full setup (first time)
composer run setup

# Run server + Vite (two processes)
composer run dev

# Run server + queue worker + Vite
composer run dev:queue

# Tests (uses SQLite in-memory, runs migrations automatically)
composer run test

# Run a single test file
php artisan test tests/Feature/SmokeTest.php

# Run a single test method
php artisan test --filter test_authenticated_bibliotecario_can_create_a_socio

# PHP linting/formatting
php artisan pint
```

### Production Deployment
```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan config:cache && php artisan route:cache && php artisan view:cache
sudo chown -R www-data:www-data storage bootstrap/cache
sudo systemctl reload php8.2-fpm
```

Use `nginx/eliber.conf` as the vhost template.

## Architecture & Key Patterns

### Authentication & Roles

**Primary auth model is `User`** (not `Bibliotecario` — that is a legacy model for the old Bibliotecario-only workflow). `User` uses `Spatie\Permission\Traits\HasRoles` with three roles:

- `admin` — superuser; can switch between institutions via session (`admin_institucion_id`); access to `/admin/*`
- `bibliotecario` — library staff; manages socios, materiales, préstamos; requires approval (`activo = true`)
- `alumno` — student; self-registers, pending approval; access to `/alumno/*` (catalog + reservations)

Login uses the `usuario` field (not `email`). New registrations start with `activo = false` and require approval. All protected routes require `auth` middleware; the REST API uses `auth:sanctum`.

### Multi-Tenancy

`tenantId()` (global helper in `app/helpers.php`) resolves the active `institucion_id`: for `admin` role it reads from `session('admin_institucion_id')`, otherwise returns `auth()->user()->institucion_id`.

`App\Scopes\TenantScope` is a global Eloquent scope — models that boot it automatically filter by `tenantId()`. When adding new models scoped to an institution, boot `TenantScope` and always include `institucion_id` in `$fillable`.

Per-institution settings are stored as key-value pairs in `configuraciones` via `Configuracion::get(tenantId(), 'key', $default)` and `Configuracion::set(tenantId(), 'key', $value)`.

### Routes

All web routes are in `routes/web.php`. The REST API is in `routes/api.php` under the `v1` prefix.

**Public web routes:**
- `GET /` — Landing; `GET /acerca`, `GET /faqs`
- `GET /materiales/{id}/ficha` — Public material sheet (QR target, no auth)
- `GET|POST /reset-password` — Password reset

**Auth middleware group:**
- `GET /dashboard` — Dashboard (role-aware)
- `socios.*` + PATCH `/baja`, `/reactivar`
- `materiales.*` + GET `/{material}/qr`
- `areas.*`, `categorias.*` (CategoriaFisica), `noticias.*`, `anotaciones.*`
- `prestamos.index/create/store` + PATCH `/devolver`, `/extender` + GET `/{prestamo}/devolucion`
- `usuarios.index/edit/update` + PATCH `/permisos`, `/toggle-activo`, `/aprobar`
- `alertas.*` (index, marcar leída, baja-material)
- `GET|PUT /perfil`
- AJAX: `GET /api/socios/buscar`, `/api/socios/{socio}/prestamos`, `/api/materiales/disponibles`, `/api/materiales/ultimo-codigo`

**Admin group** (`role:admin`, prefix `/admin`, name `admin.*`):
- `admin.dashboard`, `admin.usuarios.*` (CRUD + toggle/aprobar)
- `admin.feedback.*` (Kanban board)
- `admin.contenido.*` (FAQs, footer links, anuncio)
- `admin.analitica.index`, `admin.configuracion.index/update`
- `POST admin/switch-institucion` — switches active institution in session

**Alumno group** (`role:alumno`, prefix `/alumno`, name `alumno.*`):
- `alumno.dashboard`, `alumno.catalogo`, `alumno.reservas` (mis-reservas)
- `POST alumno/reservas`, `DELETE alumno/reservas/{reserva}`

**REST API (public + Sanctum):**
- Public: `GET /api/v1/materiales`, `GET /api/v1/materiales/{id}`, `GET /api/v1/noticias`
- Sanctum-auth: `GET/POST /api/v1/reservas`, `DELETE /api/v1/reservas/{id}`, `PATCH /api/v1/reservas/{id}/aprobar`, `PATCH /api/v1/reservas/{id}/rechazar`

### Authentication & Authorization

The auth user model is `App\Models\User` (not `Bibliotecario` — that model is legacy/deprecated). Login uses the `usuario` field (not `email`). Users have an `activo` boolean; inactive accounts are rejected at login.

Post-login redirect is role-based: `admin` → `admin.dashboard`, `alumno` → `alumno.dashboard`, others → `dashboard`.

**Roles (Spatie Permissions)** — defined in `RolesAndPermissionsSeeder`:
- `admin` — all 9 permissions
- `bibliotecario` — all except `ver-reportes`
- `alumno` — `ver-materiales`, `crear-reservas`, `ver-reservas`

**User model extras**: optional `socio_id` FK (links a user account to a Socio record), plus `wallpaper`, `apellido`, `anio`, `division` fields (for student accounts).

All protected web routes use `auth` middleware; the REST API uses `auth:sanctum`. Sanctum token expiry is 1440 min (24h). Set `SANCTUM_STATEFUL_DOMAINS` in production for cookie-based auth.

### Multi-Tenancy

`TenantScope` is applied via `booted()` on scoped models. If `auth()->user()->institucion_id` is null, the scope returns `whereRaw('1 = 0')` — a hard safety block. Policies also enforce `isSameInstitution()` checks as a second layer. The `User` model itself is not tenant-scoped.

When adding new models or queries, always register `TenantScope` in `booted()` and include `institucion_id` in `fillable`.

### Inertia Shared Data

`HandleInertiaRequests` passes to every page:
- `auth.user` — id, nombre, usuario, picture_url
- `auth.permisos`, `auth.roles`, `auth.es_admin`
- `vencimientos_proximos` (configurable days, default 4), `alertas_no_leidas`, `solicitudes_pendientes`
- `anuncio` (active institution announcement with style), `footer_links`
- Navigation menu filtered by permission

### Core Models & Relationships

**User** — primary auth user (`bibliotecarios` table via `User` model, uses `HasRoles`); fields: nombre, apellido, email, usuario, password, picture, wallpaper, anio, division, activo, institucion_id, socio_id

**Socio** (Member) — `prestamos()`, `historial()`; scopes: `activos()`, `buscarEmail()`; `full_name` attr; fields: nombre, apellido, telefono, direccion, email, anio, division, activo, institucion_id

**Material** (Bibliographic item) — `area`, `prestamos()`, `ubicacion()`; scopes: `disponible()`, `porArea()`; fields: titulo, autor, anio_publicacion, area_id, categoria, codigo, disponibilidad, disponibilidad_reservada, editorial, clasificacion_fisica, institucion_id

**Prestamo** (Loan) — `socio`, `material`, `detalles()`, `notificaciones()`; scopes: `activo()`, `atrasado()`, `pendiente()`, `devuelto()`, `vencimientoProximo()`; `link_whatsapp` attr (Argentine format); estado enum: activo/pendiente/atrasado/devuelto; institucion_id

**Reserva** — `socio`, `material`; estado enum: pendiente/aprobada/rechazada/expirada; fields: socio_id, material_id, fecha_reserva, fecha_vencimiento, institucion_id

**Area** (Dewey) — `materiales()`; fields: codigo_dewey, nombre, Abreviado, institucion_id

**Institucion** — `socios()`, `materiales()`, `prestamos()`; uses SoftDeletes; fields: nombre, slug, estado, anuncio_activo, anuncio_texto, anuncio_estilo

**Configuracion** — per-institution key-value settings; no TenantScope (uses explicit `institucion_id` in queries); use `Configuracion::get()` / `::set()` everywhere

**Supporting**: Bibliotecario (legacy auth model — do not use for new features), HistorialSocio, Alerta, Noticia, Anotacion, Notificacion, UbicacionFisica, PrestamoDetalle, CategoriaFisica, Faq, FeedbackCard, FooterLink

### Service Layer

**PrestamoService** — Loan lifecycle (transactional)
- `crearPrestamo(socioId, materialId, cantidad, fechaDevolucion)` — validates: member active, < 3 active loans, no duplicate, date within 14 days; decrements stock
- `devolverPrestamo(Prestamo)` — increments stock, marks devuelto
- `extenderPrestamo(Prestamo, dias)` — extends due date (1–30 days)
- `marcarAtrasados()`, `obtenerVencimientosProximos(dias)`

**MaterialService** — QR + code generation
- `generarCodigo(Area)` → `{codigo_dewey}-{seq:3digits}` (e.g. `1300-002`)
- `generarClasificacionFisica(Area, pasillo, tipo, estante, nivel)` → `{AREA_ABREV}-{PASILLO}-({TIPO}){ESTANTE}-{NIVEL}`
- `generarQR(Material)` → PNG stored at `public/qrcodes/QR_{id}.png`

**SocioService** — `darDeBaja(Socio, observaciones)` and `reactivar(Socio)` — both log to `HistorialSocio`

**ReservaService** — Reservation lifecycle (transactional)
- `crearReserva(socioId, materialId)` — checks real stock (`disponibilidad - disponibilidad_reservada`), increments `disponibilidad_reservada`
- `aprobarReserva(Reserva, dias)` — creates Prestamo, decrements both `disponibilidad_reservada` and `disponibilidad`
- `rechazarReserva(Reserva)`, `cancelarReserva(Reserva)`, `expirarReservasVencidas()`

### Inertia Shared Props

`HandleInertiaRequests` shares these props to every page:
- `auth.user` (id, nombre, usuario, picture_url), `auth.permisos`, `auth.roles`, `auth.es_admin`
- `menu` — filtered by user permissions
- `flash` — success / error / info session messages
- `vencimientos_proximos`, `alertas_no_leidas` — badge counters
- `anuncio` — `{texto, estilo}` or null
- `footer_links`, `institucion_activa` (`{id, nombre, slug}`), `instituciones` (admin only), `logo_url`

### Vue Components

Components live in `resources/js/Pages/` (version-controlled). Inertia render paths like `Inertia::render('Socios/Index')` map to `resources/js/Pages/Socios/Index.vue`. PascalCase convention throughout. Shared layout: `resources/js/Layouts/AdminLayout.vue`.

### Form Requests

`StoreSocioRequest`, `StoreMaterialRequest`, `StorePrestamoRequest`, `StoreUserRequest`, `UpdateUserRequest` — update these when changing model requirements.

### Storage

- QR codes → `storage/app/public/qrcodes/`
- News images → `storage/app/public/noticias/`
- User avatars → `storage/app/public/uploads/`
- Wallpapers → `storage/app/public/wallpapers/`

Always run `php artisan storage:link` after deployment.

### Database Safety

Migrations use `Schema::table()` (not `Schema::create()`), preserving existing data. Tests use SQLite in-memory (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`) with `RefreshDatabase`.
