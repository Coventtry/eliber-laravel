# Manual del Desarrollador — e-LibeR

Guía técnica para contribuir al proyecto. Para instalar el entorno de desarrollo, ver `README.md`.

---

## Tabla de contenidos

1. [Stack tecnológico](#stack-tecnológico)
2. [Estructura del proyecto](#estructura-del-proyecto)
3. [Arquitectura general](#arquitectura-general)
4. [Base de datos y multi-tenancy](#base-de-datos-y-multi-tenancy)
5. [Autenticación y roles](#autenticación-y-roles)
6. [Capa de servicios](#capa-de-servicios)
7. [Concurrencia y condiciones de carrera](#concurrencia-y-condiciones-de-carrera)
8. [Rutas](#rutas)
9. [Convenciones de código](#convenciones-de-código)
10. [Patrón Inertia + Vue](#patrón-inertia--vue)
11. [Controladores API (REST)](#controladores-api-rest)
12. [Cómo agregar un módulo nuevo](#cómo-agregar-un-módulo-nuevo)
13. [Testing](#testing)
14. [Caché](#caché)
15. [Comandos de Artisan y scheduler](#comandos-de-artisan-y-scheduler)
16. [Variables de entorno clave](#variables-de-entorno-clave)
17. [Despliegue en producción](#despliegue-en-producción)
18. [Referencia rápida de archivos clave](#referencia-rápida-de-archivos-clave)

---

## Stack tecnológico

| Capa | Tecnología |
|------|-----------|
| Backend | Laravel 12, PHP 8.2+ |
| Frontend | Vue 3, Vite 8, Bootstrap 4.6.2 |
| SPA bridge | Inertia.js v2 |
| Auth | Laravel Session + Spatie Permission v6 (roles/permisos) |
| API REST | Laravel Sanctum (token expiry: 1440 min) |
| DB | MySQL 8+ / MariaDB 10.6+ |
| Íconos | Bootstrap Icons |
| QR | simplesoftwareio/simple-qrcode |
| Rutas JS | Ziggy (genera `route()` en Vue) |
| Testing | PHPUnit 11, SQLite en memoria |
| Documentación API | darkaonline/l5-swagger (zircote/swagger-php) |

---

## Estructura del proyecto

```
app/
  Console/Commands/       # db:respaldo, reservas:expirar, ejemplares:migrar, prestamos:marcar-atrasados
  Http/
    Controllers/          # Un controlador por módulo (+ Api/ para REST)
    Middleware/           # HandleInertiaRequests (props globales Inertia)
    Requests/             # Form Requests (StoreSocioRequest, StorePrestamoRequest, etc.)
  Models/                 # Eloquent: User, Socio, Material, MaterialEjemplar, Prestamo, Reserva,
                          #   Area, Alerta, Multa, Noticia, Anotacion, Configuracion, Institucion,
                          #   HistorialSocio, CategoriaFisica, Faq, FeedbackCard, FooterLink
  Services/               # Lógica de negocio (PrestamoService, ReservaService, etc.)
  Scopes/                 # TenantScope — filtra todas las queries por institucion_id
  Notifications/          # PrestamoVencido, PrestamoProximoVencer, ReservaAprobada

database/
  migrations/             # ~40 migraciones ordenadas cronológicamente
  seeders/                # RolesAndPermissionsSeeder, DefaultAdminSeeder, SampleDataSeeder

resources/
  js/
    Components/           # AppNavbar, AppNavbarAlumno, AppFooter, FlashMessage, Pagination…
    Composables/          # useDarkMode, useAlertSound
    Layouts/              # AdminLayout
    Pages/
      Admin/              # Dashboard, Usuarios, Configuracion, Contenido, Analitica, Feedback
      Alumno/             # Dashboard, Catalogo, MisReservas, MisPrestamos
      Auth/               # Login (con tab Registro), ResetPassword
      Socios/             # Index, Create, Edit
      Materiales/         # Index, Create, Edit, Ejemplares
      Prestamos/          # Index, Create, Solicitudes
      Multas/             # Index, Create
      Alertas/            # Index
      Anotaciones/        # Index
      Noticias/           # Index, Create, Edit
      Areas/              # Index, Create, Edit
      Categorias/         # Index
      Usuarios/           # Index, Edit
      Perfil/             # Edit

routes/
  web.php                 # Rutas Inertia + AJAX interno
  api.php                 # API REST /api/v1/ con Sanctum
  console.php             # Tareas programadas (schedule)
```

---

## Arquitectura general

El proyecto es un **monolito SPA** usando Inertia.js como puente entre Laravel y Vue 3. No hay API separada para el frontend: Inertia serializa los props PHP directamente como props Vue.

```
Request HTTP
    → Laravel Router
    → Controller (valida, llama Service, prepara datos)
    → Inertia::render('Pagina/Vista', [...props])
    → Vue component recibe props como defineProps()
    → Respuesta HTML (primera carga) o JSON delta (navegación SPA)
```

Para operaciones AJAX internas (búsquedas predictivas, paneles expandibles) se usan rutas bajo `/api/` dentro del grupo `auth`, llamadas con `axios` desde Vue.

---

## Base de datos y multi-tenancy

**Todas las entidades tienen `institucion_id`**. El aislamiento se aplica automáticamente vía `TenantScope`:

```php
// app/Scopes/TenantScope.php
public function apply(Builder $builder, Model $model): void
{
    $tenantId = tenantId(); // helper global en app/helpers.php
    if ($tenantId === null) {
        $builder->whereRaw('1 = 0'); // bloqueo de seguridad si no hay tenant
        return;
    }
    $builder->where($model->getTable() . '.institucion_id', $tenantId);
}
```

`tenantId()` resuelve el tenant activo: para `admin` lee `session('admin_institucion_id')`, para los demás roles retorna `auth()->user()->institucion_id`.

Los modelos registran el scope en `booted()`:

```php
protected static function booted(): void
{
    static::addGlobalScope(new TenantScope());
}
```

**Regla crítica:** cualquier modelo nuevo con datos por institución debe incluir `institucion_id` en `$fillable` y aplicar `TenantScope` en `booted()`.

### Tabla de modelos

| Modelo | Tabla | TenantScope | Notas |
|--------|-------|:-----------:|-------|
| `User` | `users` | No | Tiene `institucion_id` pero no scope; campo auth |
| `Socio` | `socios` | Sí | `full_name` attr; scopes: `activos()`, `buscarEmail()` |
| `Material` | `materiales` | Sí | `disponibilidad` es contador denormalizado |
| `MaterialEjemplar` | `material_ejemplares` | Sí | Fuente de verdad de stock físico |
| `Prestamo` | `prestamos` | Sí | `link_whatsapp` attr; scopes: `activo()`, `atrasado()`, `vencimientoProximo()` |
| `Reserva` | `reservas` | Sí | estados: pendiente/aprobada/rechazada/expirada |
| `Multa` | `multas` | Sí | estados: pendiente/pagada/perdonada |
| `Area` | `areas` | No (global) | `hasMany Material` |
| `Alerta` | `alertas` | Sí | `belongsTo Prestamo` |
| `Noticia` | `noticias` | Sí | SoftDeletes |
| `Anotacion` | `anotaciones` | Sí | SoftDeletes |
| `CategoriaFisica` | `categorias_fisicas` | Sí | — |
| `Institucion` | `instituciones` | N/A | Raíz tenant, SoftDeletes |
| `Configuracion` | `configuraciones` | No | KV store; usar `get()`/`set()` estáticos |
| `HistorialSocio` | `historial_socios` | Sí | `belongsTo Socio` |
| `Faq` | `faqs` | Sí | campo `activa` toggle |
| `FooterLink` | `footer_links` | No | Filtro manual por `institucion_id` |
| `FeedbackCard` | `feedback_cards` | No | Kanban interno (columnas: todo/progreso/hecho) |

---

## Autenticación y roles

- El modelo auth es `App\Models\User` (campo `usuario`, no `email`).
- Login por campo `usuario`. El email existe pero no se usa para autenticación.
- Roles con **Spatie Permission v6**: `admin`, `bibliotecario`, `alumno`.
- Cuentas creadas vía registro público nacen con `activo = false` → requieren aprobación.
- Cuentas inactivas son rechazadas en login con mensaje según el rol que aprueba.

### Redirección post-login por rol

```php
// AuthenticatedSessionController::store()
return match(true) {
    $user->hasRole('admin')  => redirect()->route('admin.dashboard'),
    $user->hasRole('alumno') => redirect()->route('alumno.dashboard'),
    default                  => redirect()->route('dashboard'),
};
```

### Props globales compartidas (HandleInertiaRequests)

Disponibles en todos los componentes Vue via `usePage().props`:

```js
auth.user              // { id, nombre, usuario, picture_url }
auth.roles             // array de roles
auth.permisos          // array de permisos (Spatie)
auth.es_admin          // boolean shortcut
menu                   // links del menú filtrados por permiso
flash                  // { success, error, info, warning }
vencimientos_proximos  // conteo de préstamos próximos a vencer
alertas_no_leidas      // conteo de alertas sin leer
solicitudes_pendientes // conteo de reservas pendientes de aprobación
anuncio                // { texto, estilo } | null
footer_links           // array de { label, url } (cacheado 1h)
institucion_activa     // { id, nombre, slug }
instituciones          // array (solo admin, para el switch de institución)
logo_url               // URL del logo institucional
```

### Verificar permisos en controladores

```php
// Por rol
abort_if(!auth()->user()->hasRole('admin'), 403);

// Por permiso Spatie
$this->authorize('gestionar-socios');

// Por Policy Eloquent
$this->authorize('update', $socio);
```

---

## Capa de servicios

La lógica de negocio vive en `app/Services/`, no en controladores.

| Servicio | Métodos principales |
|----------|-------------------|
| `PrestamoService` | `crearPrestamo()` → `Prestamo[]`, `devolverPrestamo()`, `extenderPrestamo()`, `marcarAtrasados()`, `obtenerVencimientosProximos()` |
| `ReservaService` | `crearReserva()`, `aprobarReserva()`, `rechazarReserva()`, `cancelarReserva()`, `expirarReservasVencidas()` |
| `SocioService` | `darDeBaja()`, `reactivar()` — ambos registran en `HistorialSocio` |
| `MaterialService` | `generarCodigo()`, `generarClasificacionFisica()`, `generarQR()`, `crearEjemplares()`, `ajustarEjemplares()` |
| `MultaService` | `generarMulta()`, `pagar()`, `perdonar()` |

**Importante:** `crearPrestamo()` retorna `Prestamo[]` (array), no un único modelo, porque se pueden crear varios ejemplares en un solo préstamo.

**Regla:** los controladores llaman al servicio y manejan la respuesta. La lógica transaccional (`DB::transaction`) va siempre en el servicio.

```php
// Correcto — controlador delgado
public function store(StorePrestamoRequest $request): RedirectResponse
{
    $prestamos = $this->prestamoService->crearPrestamo(
        $request->socio_id, $request->material_id,
        $request->cantidad, $request->fecha_devolucion
    );
    return redirect()->route('prestamos.index')->with('success', 'Préstamo registrado.');
}
```

---

## Concurrencia y condiciones de carrera

Todas las operaciones que modifican stock usan el patrón **pre-flight + lockForUpdate**:

1. **Pre-flight fuera de la transacción**: validaciones rápidas para UX (devuelven error antes de abrir la transacción)
2. **Re-checks atómicos dentro de la transacción** con `lockForUpdate`: garantía real de consistencia

```php
// Ejemplo: crearPrestamo
public function crearPrestamo(...): array
{
    // PRE-FLIGHT (UX rápida)
    if ($socio->prestamosActivos()->count() >= 3) {
        throw ValidationException::withMessages([...]);
    }

    return DB::transaction(function () use (...) {
        // RE-CHECK ATÓMICO (seguridad real)
        $activos = Prestamo::where('socio_id', $socio->id)
            ->whereIn('estado', ['activo', 'pendiente', 'atrasado'])
            ->lockForUpdate()
            ->count();
        if (($activos + $cantidad) > 3) {
            throw ValidationException::withMessages([...]);
        }

        $ejemplares = MaterialEjemplar::where('material_id', $material->id)
            ->where('estado', 'disponible')
            ->lockForUpdate()
            ->limit($cantidad)
            ->get();
        // ...
    });
}
```

Operaciones protegidas con este patrón:
- `crearPrestamo` — lock en Material + MaterialEjemplar; re-check de límite 3 préstamos y duplicado
- `crearReserva` — check de duplicado + selección de ejemplar + incremento de `disponibilidad_reservada` dentro de una sola transacción
- `expirarReservasVencidas` — lockForUpdate en Reserva + MaterialEjemplar para evitar doble decremento en ejecuciones concurrentes del scheduler
- `marcarAtrasados` — transacción + lockForUpdate

**No agregar operaciones que modifiquen stock fuera de `DB::transaction()`.**

---

## Rutas

### Grupos web (`routes/web.php`)

```
/ (público)                    Landing, FAQs, Acerca
/login, POST /register         Auth (throttle: 10/min login, 3/min registro)
/reset-password                Cambio de contraseña
/dashboard                     auth middleware → dashboard bibliotecario

# Módulos principales (middleware: auth)
/socios                        CRUD + baja + reactivar
/materiales                    CRUD + QR + ejemplares + baja ejemplar
/areas                         CRUD
/categorias                    CRUD
/prestamos                     index, create, store + devolver + extender + solicitudes
/multas                        index, create, store + pagar + perdonar
/noticias                      CRUD
/anotaciones                   index + store
/alertas                       index + marcar leída + todas-leídas
/usuarios                      index, edit, update + permisos + toggle-activo + aprobar
/perfil                        GET edit, PUT update

# AJAX interno (middleware: auth, respuesta JSON)
/api/socios/buscar             Búsqueda predictiva de socios
/api/socios/{socio}/prestamos  Panel expandible del socio
/api/materiales/ejemplares-disponibles  Búsqueda de materiales con stock

# Admin (middleware: auth + role:admin, prefijo /admin)
/admin/dashboard               Métricas generales
/admin/usuarios                CRUD + toggle + aprobar
/admin/feedback                Kanban (CRUD + mover columna)
/admin/contenido               FAQs + footer links + anuncio
/admin/analitica               Gráficos y estadísticas
/admin/configuracion           Parámetros del sistema
POST /admin/switch-institucion Cambiar tenant activo en sesión

# Alumno (middleware: auth + role:alumno, prefijo /alumno)
/alumno/dashboard
/alumno/catalogo
/alumno/mis-prestamos
/alumno/mis-reservas
POST /alumno/reservas           Crear reserva
DELETE /alumno/reservas/{id}    Cancelar reserva

# Exportaciones (middleware: auth, prefijo /exportar)
/exportar/socios/csv|pdf
/exportar/materiales/csv|pdf
/exportar/prestamos/csv|pdf
/exportar/multas/csv|pdf
```

### API REST (`routes/api.php`) — prefijo `/api/v1`

Throttle global: 60 req/min (autenticadas), 30 req/min (públicas).

Pública (sin auth): `GET /materiales`, `GET /materiales/{id}`, `GET /noticias`.

Sanctum auth: socios, materiales, áreas, noticias, préstamos, reservas, multas, alertas, usuarios — CRUD completo más acciones específicas (`devolver`, `extender`, `pagar`, `aprobar`, etc.).

---

## Convenciones de código

### Idioma: español camelCase

Todo el código custom se escribe en **español**. Las convenciones de Laravel (`$request`, métodos CRUD, etc.) se respetan en inglés.

```php
$socio         // no $member
$prestamo      // no $loan
$institucion   // no $tenant
$enviarAlerta  // no $sendAlert
```

```js
busqueda       // no search
mostrarModal   // no showModal
enviar()       // no submit()
formatearFecha() // no formatDate()
```

### Sin comentarios obvios

Solo comentar cuando el **por qué** no es deducible del código: restricciones ocultas, workarounds de bugs, invariantes no obvios.

### Sin abstracción prematura

Tres líneas similares no justifican un helper. Extraer solo cuando hay 4+ usos reales o la complejidad es genuina.

---

## Patrón Inertia + Vue

### Controlador → Vista

```php
return Inertia::render('Socios/Index', [
    'socios'  => SocioResource::collection($socios->through(fn ($s) => $s)),
    'filters' => $request->only(['busqueda', 'activo', 'anio', 'division']),
    'areas'   => Area::all(['id', 'nombre']),
]);
```

### Componente Vue

```vue
<script setup>
const props = defineProps({
    socios:  { type: Object, required: true },  // paginado
    filters: { type: Object, default: () => ({}) },
    areas:   { type: Array,  default: () => [] },
})
</script>
```

### Paginación con filtros

```js
function buscar() {
    router.get(route('socios.index'), {
        busqueda: form.busqueda,
        activo:   form.activo,
    }, { preserveState: true, replace: true })
}
```

### Formularios Inertia

```js
const form = useForm({ nombre: '', apellido: '' })
form.post(route('socios.store'))
form.put(route('socios.update', socio.id))
form.delete(route('socios.destroy', socio.id))
```

### AJAX interno (axios)

```js
// Búsqueda predictiva
const { data } = await axios.get(route('api.socios.buscar'), {
    params: { q: termino },
})

// Panel expandible
const { data: prestamos } = await axios.get(
    route('api.socios.prestamos', socio.id)
)
```

### Endpoints duales HTML/JSON

```php
public function extender(Request $request, Prestamo $prestamo): RedirectResponse|JsonResponse
{
    $this->prestamoService->extenderPrestamo($prestamo, $request->dias);

    if ($request->wantsJson()) {
        return response()->json(['message' => 'OK', 'fecha_devolucion' => $prestamo->fecha_devolucion]);
    }
    return redirect()->route('prestamos.index')->with('success', 'Préstamo extendido.');
}
```

---

## Controladores API (REST)

**Regla PHP 8.2:** todos los métodos que declaran `JsonResponse` como tipo de retorno **deben** devolver `->response()` sobre el recurso, no el recurso directamente.

```php
// Correcto
public function index(): JsonResponse
{
    return SocioResource::collection($socios)->response();
}

public function show(Socio $socio): JsonResponse
{
    return (new SocioResource($socio))->response();
}

public function store(Request $request): JsonResponse
{
    return (new SocioResource($socio))->response()->setStatusCode(201);
}

// INCORRECTO — TypeError en PHP 8.2
public function show(Socio $socio): JsonResponse
{
    return new SocioResource($socio); // falta ->response()
}
```

Los recursos envuelven la respuesta en una clave `data`:
```json
{ "data": { "id": 1, "nombre": "Juan" } }
```
Los tests deben usar `assertJsonPath('data.nombre', 'Juan')`, no `assertJsonPath('nombre', 'Juan')`.

---

## Cómo agregar un módulo nuevo

Ejemplo: módulo `Multas` (ya implementado, úsalo como referencia).

**1. Migración** — siempre incluir `institucion_id`
```bash
php artisan make:migration create_multas_table
```

**2. Modelo** — aplicar TenantScope
```php
class Multa extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());
    }

    protected $fillable = [
        'socio_id', 'prestamo_id', 'monto', 'motivo',
        'estado', 'pagada_en', 'institucion_id',
    ];
}
```

**3. Form Request**
```bash
php artisan make:request StoreMultaRequest
```

**4. Servicio** (si hay lógica de negocio o transacciones)
```php
// app/Services/MultaService.php
class MultaService
{
    public function registrar(int $socioId, float $monto, string $motivo): Multa
    {
        return DB::transaction(fn () => Multa::create([...]));
    }
}
```

**5. Controlador**
```bash
php artisan make:controller MultaController
```
Inyectar el servicio. Usar `Inertia::render('Multas/Index', [...])`.

**6. Rutas**
```php
// routes/web.php — dentro del grupo auth
Route::resource('multas', MultaController::class)->only(['index', 'create', 'store']);
Route::patch('multas/{multa}/pagar',    [MultaController::class, 'pagar'])->name('multas.pagar');
Route::patch('multas/{multa}/perdonar', [MultaController::class, 'perdonar'])->name('multas.perdonar');
```

**7. Vistas Vue** en `resources/js/Pages/Multas/`
Seguir la convención: `defineProps`, `useForm`, paginación con `router.get`.

**8. Navbar** — agregar entrada en `AppNavbar.vue` con el permiso adecuado.

**9. Permiso** — agregar en `RolesAndPermissionsSeeder.php` y asignar a los roles correspondientes.

**10. API REST** (opcional) — crear `app/Http/Controllers/Api/MultaController.php` con retornos `->response()`.

---

## Testing

Los tests usan **SQLite en memoria** con `RefreshDatabase`. Esto está configurado en `phpunit.xml` via variables de entorno:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE"   value=":memory:"/>
```

```bash
# Todos los tests
composer run test

# Un archivo
php artisan test tests/Feature/ApiTest.php

# Un método específico
php artisan test --filter test_bibliotecario_puede_crear_socio

# Con output detallado
php artisan test --verbose
```

### Tests con MaterialEjemplar

Cualquier test que llame a `crearPrestamo()` o `crearReserva()` debe crear primero los registros de `MaterialEjemplar` — son la fuente de verdad del stock:

```php
$material = Material::forceCreate([
    'titulo' => 'Libro', 'disponibilidad' => 2, ...,
    'institucion_id' => $institucion->id,
]);

// OBLIGATORIO: crear los ejemplares físicos
MaterialEjemplar::forceCreate([
    'material_id'    => $material->id,
    'institucion_id' => $material->institucion_id,
    'codigo_ejemplar' => '100-001-E1',
    'estado'         => 'disponible',
]);
```

Sin los ejemplares, los servicios lanzan *"Material no disponible"* aunque `disponibilidad > 0`.

### Escribir un test de Feature

```php
class MultaTest extends TestCase
{
    use RefreshDatabase;

    public function test_bibliotecario_puede_registrar_multa(): void
    {
        $institucion = Institucion::create(['nombre' => 'Test', 'slug' => 'test', 'estado' => 'activa']);
        $user = User::factory()->create(['institucion_id' => $institucion->id]);
        $user->assignRole('bibliotecario');

        $this->actingAs($user)
            ->post(route('multas.store'), [
                'socio_id' => $socio->id,
                'monto'    => 500,
                'motivo'   => 'Retraso 5 días',
            ])
            ->assertRedirect(route('multas.index'));
    }
}
```

---

## Caché

Driver: `file` por defecto, configurable a `redis` o `database` en producción. El proyecto usa `database` en Docker.

### Keys cacheadas (TTL: 1 hora)

| Key | Propósito | Se invalida en |
|-----|-----------|----------------|
| `faqs_{institucion_id}` | FAQs activas del tenant | store/update/destroy de FAQ |
| `footer_links_{institucion_id}` | Footer links del tenant | store/update/destroy de FooterLink |

Usadas en `ContenidoController` y `HandleInertiaRequests::share()`.

---

## Comandos de Artisan y scheduler

| Comando | Descripción | Frecuencia |
|---------|-------------|-----------|
| `prestamos:marcar-atrasados` | Marca préstamos vencidos como "atrasado", genera multas, envía notificaciones | Diario 01:00 |
| `reservas:expirar` | Expira reservas vencidas, libera ejemplares | Cada hora |
| `db:respaldo --keep=N` | Backup MySQL comprimido a `storage/app/backups/` | Diario 03:00 |
| `ejemplares:migrar --dry-run` | One-shot: crea ejemplares para materiales existentes sin ejemplares | Manual |

Todos usan `->withoutOverlapping()` para evitar ejecuciones concurrentes del scheduler.

La programación está en `routes/console.php`:

```php
Schedule::command('prestamos:marcar-atrasados')->dailyAt('01:00')->withoutOverlapping();
Schedule::command('reservas:expirar')->hourly()->withoutOverlapping();
Schedule::command('db:respaldo', ['--keep=7'])->dailyAt('03:00')->withoutOverlapping();
```

---

## Variables de entorno clave

| Variable | Uso |
|----------|-----|
| `APP_KEY` | Encriptación — generado con `artisan key:generate` |
| `APP_URL` | URL base (afecta QR, storage links, Sanctum) |
| `DB_*` | Conexión MySQL |
| `DEFAULT_ADMIN_USUARIO` | Usuario del admin inicial (seed) |
| `DEFAULT_ADMIN_PASSWORD` | Contraseña del admin inicial |
| `DEFAULT_INSTITUCION_NOMBRE` | Institución creada en el seed |
| `DEFAULT_INSTITUCION_SLUG` | Slug de la institución inicial |
| `SEED_SAMPLE_DATA=true` | Activa el seeder de datos de prueba en dev |
| `SESSION_ENCRYPT=true` | Encripta datos de sesión |
| `SESSION_SECURE_COOKIE=true` | Cookie solo por HTTPS |
| `SANCTUM_STATEFUL_DOMAINS` | Dominios para auth cookie en producción |
| `L5_SWAGGER_GENERATE_ALWAYS` | Regenera docs Swagger en cada request (solo dev) |

---

## Despliegue en producción

Ver `DEPLOY.md` para el proceso Docker completo. Ver `CHECKLIST_PRODUCCION.md` antes de cualquier deploy.

Resumen de comandos manuales (sin Docker):

```bash
composer install --optimize-autoloader --no-dev
npm ci && npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan db:seed --class=RolesAndPermissionsSeeder --force
```

**Post-deploy:**
- Queue worker: `php artisan queue:work --tries=3 --daemon`
- Cron: `* * * * * cd /ruta && php artisan schedule:run >> /dev/null 2>&1`

### Permisos de storage en Docker

El directorio `storage/` se monta como bind mount desde el host. El entrypoint del contenedor aplica `chown -R www-data:www-data` automáticamente, pero si el mount es propiedad de otro usuario (uid distinto de 33) y el chown falla por permisos del host, puede ocurrir un error 500 en el primer request. En ese caso, correr manualmente en el host:

```bash
sudo chown -R www-data:www-data ./storage ./bootstrap/cache
```

---

## Referencia rápida de archivos clave

| Archivo | Propósito |
|---------|-----------|
| `app/Http/Middleware/HandleInertiaRequests.php` | Props globales para todos los componentes Vue |
| `app/Scopes/TenantScope.php` | Filtro automático por `institucion_id` |
| `app/helpers.php` | Helper global `tenantId()` |
| `app/Services/PrestamoService.php` | Lógica completa de préstamos |
| `app/Services/ReservaService.php` | Lógica completa de reservas |
| `app/Services/MaterialService.php` | QR, códigos, ejemplares |
| `routes/web.php` | Todas las rutas web e AJAX interno |
| `routes/api.php` | API REST Sanctum |
| `routes/console.php` | Scheduler (schedule) |
| `resources/js/Components/AppNavbar.vue` | Navbar principal con control de permisos |
| `resources/js/Pages/Auth/Login.vue` | Login + Registro en una sola vista (tabs) |
| `database/seeders/RolesAndPermissionsSeeder.php` | Definición de roles y permisos |
| `database/seeders/DefaultAdminSeeder.php` | Creación del admin inicial |
| `docker/entrypoint.sh` | Arranque del contenedor: migrate, seed, cache, permisos |
| `docker-compose.prod.yml` | 5 servicios: app, db, web (nginx), queue, scheduler |
| `DEPLOY.md` | Guía paso a paso para producción Docker |
| `CHECKLIST_PRODUCCION.md` | Lista de verificación pre-deploy |
| `CLAUDE.md` | Contexto para el agente IA |
