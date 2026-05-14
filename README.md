# e-LibeR — Sistema de Gestión Bibliotecaria

Laravel 12 + Vue 3 + Inertia.js SPA con REST API. Gestión de materiales, socios, préstamos y reservas para instituciones educativas.

**Stack:** Laravel 12 · PHP 8.2 · Vue 3 · Vite · Inertia.js · MySQL 8+ · Bootstrap 4.6 · Docker

---

## Desarrollo local

**Requisitos:** PHP 8.2+, Composer 2.x, Node.js 18+, MySQL 8+

```bash
git clone <url> eliber-laravel && cd eliber-laravel
cp .env.example .env
# Editar .env: DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD
composer run setup      # install + key + migrate + seed + npm build
composer run dev        # servidor + Vite (hot reload)
```

### Comandos útiles

```bash
composer run dev:queue          # + queue worker
composer run test               # tests (SQLite in-memory)
php artisan test --filter nombre_test
php artisan pint                # linting PHP
npm run build                   # build assets producción
```

---

## Despliegue con Docker

### 1. Clonar y configurar

```bash
git clone -b deploy <url> eliber-laravel && cd eliber-laravel
cp .env.example .env
```

Editar `.env` con los valores de producción (ver sección **Variables de entorno**).

### 2. Deploy

```bash
chmod +x deploy.sh
./deploy.sh
```

El script hace: `git pull origin deploy` → rebuild de imágenes → `docker compose up`.

### 3. Crear primer usuario admin

```bash
docker exec -it eliber-app php artisan tinker
```

```php
$user = App\Models\User::create([
    'name'          => 'Administrador',
    'nombre'        => 'Administrador',
    'usuario'       => 'admin',
    'email'         => 'admin@tuinstitucion.edu.ar',
    'password'      => bcrypt('TuContraseñaSegura'),
    'activo'        => true,
    'institucion_id'=> 1,
]);
$user->assignRole('admin');
```

### Variables de entorno requeridas en producción

| Variable | Descripción |
|----------|-------------|
| `APP_KEY` | Generar con `php artisan key:generate --show` |
| `APP_URL` | URL pública: `https://eliber.tuinstitucion.edu.ar` |
| `DB_PASSWORD` | Contraseña del usuario de BD |
| `DB_ROOT_PASSWORD` | Contraseña root MySQL (solo Docker) |
| `DEFAULT_ADMIN_PASSWORD` | Contraseña del admin inicial |
| `SESSION_SECURE_COOKIE` | `true` si se usa HTTPS |
| `SANCTUM_STATEFUL_DOMAINS` | Dominio para auth cookie Sanctum |
| `CORS_ALLOWED_ORIGINS` | Origen permitido para la API |

Ver `.env.example` para la lista completa con valores por defecto.

### Nginx (host) como proxy inverso

Configurar un bloque `server` que haga proxy a `http://localhost:8005`. Plantilla en `docker/nginx/eliber.conf`.

---

## Arquitectura

**Inertia.js SPA** — Laravel sirve datos vía Inertia, Vue renderiza el frontend sin API calls desde el browser (excepto la REST API pública).

**Multi-tenant** — Cada registro lleva `institucion_id`. El helper `tenantId()` resuelve la institución activa (admins pueden cambiar con `POST /admin/switch-institucion`). `TenantScope` filtra automáticamente en todos los modelos salvo `Configuracion`.

**Roles** (Spatie Permission): `admin` · `bibliotecario` · `alumno`

**Service layer**: `PrestamoService` · `MaterialService` · `SocioService` · `ReservaService`

### Flujo de préstamo

```
Alumno reserva → Bibliotecario aprueba → Préstamo creado automáticamente
                                       → Alumno devuelve → Stock restaurado
```

### REST API `/api/v1/`

| Recurso | Auth | Métodos |
|---------|------|---------|
| `/materiales`, `/noticias` | Público | GET |
| `/socios`, `/prestamos`, `/reservas` | Sanctum | CRUD completo |
| `/areas`, `/alertas`, `/usuarios`, `/multas` | Sanctum | CRUD completo |

Autenticación: `POST /api/v1/login` devuelve token Bearer. Expiración: 24h.

---

## Ramas

| Rama | Uso |
|------|-----|
| `master` | Desarrollo activo |
| `deploy` | Producción — el servidor hace `git pull origin deploy` |

Para deployar: merge de `master` → `deploy`, luego `./deploy.sh` en el servidor.

---

## Protección de datos (Ley 25.326)

Este sistema procesa datos personales de alumnos menores de edad (nombre, dirección, teléfono, historial de préstamos). La institución que lo opere es responsable de:

- Obtener consentimiento informado de los tutores.
- Informar la finalidad del tratamiento de datos.
- Garantizar el derecho de acceso, rectificación y supresión.
- Notificar incidentes de seguridad según la normativa vigente.

---

## Licencia

Uso institucional interno.
