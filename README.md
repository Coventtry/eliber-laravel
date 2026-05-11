# e-LibeR — Sistema de Gestión Bibliotecaria

Laravel 12 + Vue 3 + Inertia.js SPA + REST API. Gestión de materiales, socios, préstamos y reservas para instituciones educativas.

---

## Requisitos

| Herramienta | Versión |
|-------------|---------|
| PHP | 8.2+ |
| Composer | 2.x |
| Node.js | 18+ |
| MySQL / MariaDB | 8.0 / 10.6 |

Extensiones PHP requeridas: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `gd`, `zip`, `xml`, `curl`, `json`.

---

## Instalación (desarrollo)

```bash
git clone <url> eliber-laravel
cd eliber-laravel
cp .env.example .env
# Editar .env con DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD
composer run setup
php artisan storage:link
```

### Servir la aplicación

**Con XAMPP (Windows):** el proyecto va en `C:\xampp\htdocs\eliber-laravel\`, abrir `http://localhost/eliber-laravel/public`.

**Con Artisan serve:** `php artisan serve` abre `http://localhost:8000`.

En ambos casos, en una terminal aparte ejecutar `npm run dev` para Vite (hot reload).

El comando `composer run setup` ejecuta: `composer install` → genera `APP_KEY` → migra BD con seeders → `npm ci` → `npm run build`.

---

## Credenciales iniciales

| Campo | Valor por defecto |
|-------|-------------------|
| Usuario | `admin` |
| Contraseña | `eLiber#Admin2026` (configurable via `DEFAULT_ADMIN_PASSWORD` en `.env`) |
| Rol | Administrador |

---

## Comandos

```bash
composer run dev          # Servidor + Vite
composer run dev:queue    # + queue worker
composer run test         # Tests (SQLite in-memory)
composer run setup        # Instalación completa
php artisan test --filter test_name
php artisan optimize:clear
npm run build             # Assets producción
```

---

## Arquitectura

**Inertia.js SPA** — Laravel sirve datos vía Inertia, Vue renderiza frontend. Sin API calls desde el frontend (excepto REST API pública).

**REST API** — `/api/v1/` con auth via Laravel Sanctum (tokens). Recursos públicos: materiales, noticias. Protegidos: préstamos, reservas, áreas, alertas, socios, usuarios.

**Multi-tenant** — Cada registro tiene `institucion_id`. Helper `tenantId()` retorna la institución activa. `TenantScope` filtra automáticamente en la mayoría de los modelos (excepto `Configuracion` que usa `get`/`set` estáticos con `institucion_id` explícito).

**Roles** — `admin` (todo), `bibliotecario` (gestión), `alumno` (catálogo + reservas). vía `spatie/laravel-permission`.

**Service layer** — `PrestamoService`, `MaterialService`, `SocioService`, `ReservaService`. Controllers delegan en services.

---

## Flujo del sistema

```
1. Usuario se loguea (login con campo "usuario", no email)
         ↓
2. Admin/Bibliotecario gestiona socios, materiales, préstamos
         ↓
3. Alumno ve catálogo público y hace reservas vía API
         ↓
4. Bibliotecario aprueba/rechaza reservas → crea préstamo automático
         ↓
5. Alertas de vencimientos próximos en dashboard
```

---

## Estructura del proyecto

```
app/
├── Console/Commands/        # db:respaldo, marcar-atrasados, expirar-reservas
├── Http/
│   ├── Controllers/         # Web + Api/
│   ├── Middleware/           # HandleInertiaRequests
│   └── Requests/            # Form requests
├── Models/                  # 17 modelos Eloquent (la mayoría con TenantScope)
├── Policies/                # Spatie authorization
├── Scopes/                  # TenantScope (filtro automático por institución)
└── Services/                # PrestamoService, MaterialService, etc.
├── helpers.php              # tenantId()
database/migrations/         # ~40 migraciones ordenadas
resources/js/                # Vue 3 SPA (Pages/, Components/)
routes/
├── web.php                  # Rutas SPA (Inertia)
├── api.php                  # API REST /api/v1/ (Sanctum)
└── console.php              # Tareas programadas
```

---

## API REST

Endpoint base: `/api/v1/`

| Recurso | Auth | Descripción |
|---------|------|-------------|
| `GET /materiales` | Público | Catálogo (con filtros) |
| `GET /noticias` | Público | Noticias |
| `POST /login` | Público | Login (usuario + password) → devuelve token |
| `GET/POST /areas` | Sanctum | CRUD áreas Dewey |
| `GET /alertas` | Sanctum | Alertas + marcar leídas |
| `GET/POST/PUT/DELETE /socios` | Sanctum | CRUD socios + baja/reactivar |
| `GET/POST /prestamos` | Sanctum | Préstamos + devolver/extender |
| `GET/POST/DELETE /reservas` | Sanctum | Reservas + aprobar/rechazar |
| `GET/POST/PUT/DELETE /usuarios` | Sanctum | CRUD usuarios + permisos/toggle activo |

---

## Despliegue en producción

Ver `DEPLOY.md` para instrucciones paso a paso (`.env`, comandos, post-deploy).

---

## Notas para XAMPP (Windows)

- Apache + MySQL deben estar corriendo
- Colocar proyecto en `C:\xampp\htdocs\eliber-laravel\`
- Agregar `C:\xampp\php` al PATH si `php` no se reconoce
- Usar Git Bash o PowerShell

---

## Licencia

Uso institucional interno.
