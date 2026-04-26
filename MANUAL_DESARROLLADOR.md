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
7. [Rutas](#rutas)
8. [Convenciones de código](#convenciones-de-código)
9. [Patrón Inertia + Vue](#patrón-inertia--vue)
10. [Cómo agregar un módulo nuevo](#cómo-agregar-un-módulo-nuevo)
11. [Testing](#testing)
12. [Variables de entorno clave](#variables-de-entorno-clave)
13. [Despliegue en producción](#despliegue-en-producción)

---

## Stack tecnológico

| Capa | Tecnología |
|------|-----------|
| Backend | Laravel 12, PHP 8.2 |
| Frontend | Vue 3, Vite, Bootstrap 4.6.2 |
| SPA bridge | Inertia.js v2 |
| Auth | Laravel Session + Spatie Permission (roles/permisos) |
| API REST | Laravel Sanctum |
| DB | MySQL 8+ / MariaDB 10.6+ |
| Íconos | Bootstrap Icons |
| QR | simplesoftwareio/simple-qrcode |
| Rutas JS | Ziggy (genera `route()` en Vue) |

---

## Estructura del proyecto

```
app/
  Http/
    Controllers/          # Un controlador por módulo
    Middleware/           # HandleInertiaRequests (comparte props globales)
    Requests/             # Form Requests para validación
  Models/                 # Eloquent models con TenantScope
  Services/               # Lógica de negocio (PrestamoService, etc.)
  Policies/               # Autorización por modelo

database/
  migrations/             # Una migración por cambio de esquema
  seeders/                # Datos iniciales (roles, institución, admin)

resources/
  js/
    Components/           # Componentes reutilizables (AppNavbar, FlashMessage…)
    Composables/          # Composables Vue (useDarkMode)
    Layouts/              # AdminLayout
    Pages/                # Vistas Inertia (mapeadas 1:1 a Inertia::render())
      Admin/
      Auth/
      Materiales/
      Prestamos/
      Socios/
      Usuarios/
      Perfil/
      …

routes/
  web.php                 # Rutas web + AJAX interno
  api.php                 # API REST pública (v1) con Sanctum
```

---

## Arquitectura general

El proyecto es un **monolito SPA** usando Inertia.js como puente entre Laravel y Vue 3. No hay API separada para el frontend — Inertia serializa los props PHP directamente como props Vue.

```
Request HTTP
    → Laravel Router
    → Controller (valida, llama Service, prepara datos)
    → Inertia::render('Pagina/Vista', [...props])
    → Vue component recibe props como defineProps()
    → Respuesta HTML (primera carga) o JSON (navegación SPA)
```

Para operaciones AJAX internas (búsqueda predictiva, panel de detalle) se usan endpoints bajo `/api/` dentro del grupo `auth`, llamados con `axios` desde Vue.

---

## Base de datos y multi-tenancy

**Todas las entidades tienen `institucion_id`**. El aislamiento se aplica automáticamente vía `TenantScope`:

```php
// app/Scopes/TenantScope.php
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where($model->getTable() . '.institucion_id', auth()->user()?->institucion_id);
    }
}
```

Los modelos que lo usan lo registran en `booted()`:

```php
protected static function booted(): void
{
    static::addGlobalScope(new TenantScope());
}
```

> **Regla crítica**: cualquier modelo nuevo que almacene datos por institución debe incluir `institucion_id` en `$fillable` y aplicar `TenantScope`.

### Modelos principales

| Modelo | Tabla | Relaciones clave |
|--------|-------|-----------------|
| `User` | `users` | `belongsTo Institucion`, `belongsTo Socio` |
| `Socio` | `socios` | `hasMany Prestamo`, `hasMany HistorialSocio` |
| `Material` | `materiales` | `belongsTo Area`, `hasMany Prestamo` |
| `Prestamo` | `prestamos` | `belongsTo Socio`, `belongsTo Material` |
| `Reserva` | `reservas` | `belongsTo Socio`, `belongsTo Material` |
| `Area` | `areas` | `hasMany Material` |
| `Institucion` | `instituciones` | raíz tenant, SoftDeletes |

---

## Autenticación y roles

- El modelo auth es `App\Models\User` (no `Bibliotecario`).
- Login por campo `usuario` (no `email`).
- Roles con **Spatie Permission**: `admin`, `bibliotecario`, `alumno`.
- Cuentas creadas vía registro público nacen con `activo = false` → requieren aprobación.

### Redirección post-login por rol

```php
// AuthenticatedSessionController::store()
match($rol) {
    'admin'         => redirect('/admin/dashboard'),
    'alumno'        => redirect('/alumno/dashboard'),
    default         => redirect('/dashboard'),
}
```

### Verificar permisos en controladores

```php
// Por rol
abort_if(!auth()->user()->hasRole('admin'), 403);

// Por permiso (Spatie)
$this->authorize('gestionar-socios');

// En Policy
$this->authorize('update', $socio);
```

### Props globales compartidas (HandleInertiaRequests)

Disponibles en todos los componentes Vue via `usePage().props`:

```js
auth.user        // usuario autenticado
auth.roles       // array de roles
auth.permisos    // array de permisos
auth.es_admin    // boolean
menu             // links del menú según rol
flash.success / flash.error / flash.info
vencimientos_proximos  // count
alertas_no_leidas      // count
anuncio          // objeto {texto, estilo} o null
footer_links     // array
```

---

## Capa de servicios

La lógica de negocio compleja vive en `app/Services/`, no en los controladores.

| Servicio | Responsabilidad |
|----------|----------------|
| `PrestamoService` | Crear, devolver, extender préstamos. Valida: socio activo, < 3 préstamos, no duplicado, fecha dentro de 14 días. |
| `ReservaService` | Crear, aprobar, rechazar, cancelar, expirar reservas. Gestiona `disponibilidad_reservada`. |
| `SocioService` | `darDeBaja()` y `reactivar()`. Ambos logean en `HistorialSocio`. |
| `MaterialService` | `generarCodigo()`, `generarClasificacionFisica()`, `generarQR()`. |

**Regla**: los controladores llaman al servicio y devuelven la respuesta. La lógica transaccional (DB::transaction) va en el servicio.

```php
// Correcto
public function store(StorePrestamoRequest $request): RedirectResponse
{
    $this->prestamoService->crearPrestamo(
        $request->socio_id, $request->material_id,
        $request->cantidad, $request->fecha_devolucion
    );
    return redirect()->route('prestamos.index')->with('success', '...');
}
```

---

## Rutas

Todas las rutas web están en `routes/web.php`. Las rutas REST públicas en `routes/api.php`.

### Grupos principales

```
/ (público)                    Landing, FAQs
/login, /register, /logout     Auth
/dashboard                     auth middleware → bibliotecario
/socios, /materiales, etc.     auth middleware
/prestamos/create              Terminal de Préstamos
/api/socios/buscar             AJAX interno (auth)
/api/socios/{id}/prestamos     AJAX panel detalle (auth)
/api/materiales/disponibles    AJAX búsqueda predictiva (auth)
/admin/*                       auth + role:admin
/alumno/*                      auth + role:alumno
/api/v1/*                      Sanctum (público + auth)
```

### Agregar una ruta nueva

```php
// En routes/web.php, dentro del grupo auth
Route::get('mi-modulo', [MiModuloController::class, 'index'])->name('mi-modulo.index');
Route::resource('mi-modulo', MiModuloController::class);
```

---

## Convenciones de código

### Idioma: español camelCase

Todo el código custom se escribe en **español**. Las convenciones de Laravel (métodos CRUD, `$request`, etc.) se respetan en inglés.

**PHP:**
```php
$usuario      // no $user
$datos        // no $data
$archivo      // no $file
$nombreArchivo // no $filename
$idInstitucion // no $instId
```

**Vue:**
```js
busqueda       // no search
enviar()       // no submit()
formatearFecha() // no formatDate()
claseEstado()  // no estadoClass()
confirmarBaja() // no confirmBaja()
mostrarModal   // no showModal
```

### Comentarios

Solo cuando el **por qué** no es obvio. No comentar lo que el código ya dice.

### Sin abstracción prematura

Tres líneas similares no justifican un helper. Solo extraer cuando hay 4+ usos o la complejidad es genuina.

---

## Patrón Inertia + Vue

### Controlador → Vista

```php
return Inertia::render('Socios/Index', [
    'socios'  => $socios,          // paginado con ->through()
    'filters' => $request->only(['busqueda', 'activo']),
]);
```

### Componente Vue

```vue
<script setup>
const props = defineProps({
    socios:  { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
})
</script>
```

### Paginación con filtros

```js
function buscar() {
    router.get(route('socios.index'), { busqueda: busqueda.value }, {
        preserveState: true,
        replace: true,
    })
}
```

### Formularios Inertia

```js
const form = useForm({ campo: '' })
form.post(route('socios.store'))     // POST
form.put(route('socios.update', id)) // PUT
```

### AJAX interno (axios)

Para búsquedas predictivas o paneles que no necesitan navegación:

```js
const { data } = await axios.get(route('api.socios.buscar'), {
    params: { q: termino },
})
```

### Endpoints que responden HTML y JSON

```php
public function extender(Request $request, Prestamo $prestamo)
{
    // ...lógica...
    if ($request->wantsJson()) {
        return response()->json(['message' => 'OK']);
    }
    return redirect()->route('prestamos.index')->with('success', 'OK');
}
```

---

## Cómo agregar un módulo nuevo

Ejemplo: agregar un módulo `Multas`.

**1. Migración**
```bash
php artisan make:migration create_multas_table
```
Incluir siempre `institucion_id` en la tabla.

**2. Modelo**
```php
class Multa extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());
    }

    protected $fillable = ['socio_id', 'monto', 'motivo', 'pagada', 'institucion_id'];
}
```

**3. Form Request** (opcional pero recomendado)
```bash
php artisan make:request StoreMultaRequest
```

**4. Servicio** (si hay lógica de negocio)
```php
// app/Services/MultaService.php
class MultaService
{
    public function registrar(int $socioId, float $monto, string $motivo): Multa { ... }
}
```

**5. Controlador**
```bash
php artisan make:controller MultaController
```
Inyectar el servicio en el constructor. Usar `Inertia::render('Multas/Index', [...])`.

**6. Rutas**
```php
// routes/web.php — dentro del grupo auth
Route::resource('multas', MultaController::class);
```

**7. Vistas Vue**
Crear `resources/js/Pages/Multas/Index.vue` (y Create, Edit según necesidad).
Seguir la convención: `defineProps`, `useForm`, búsqueda con `router.get`.

**8. Navbar** (si corresponde)
Agregar entrada en `AppNavbar.vue` bajo el permiso adecuado.

**9. Permiso** (si se usa Spatie)
Agregar en `RolesAndPermissionsSeeder.php` y en la constante de `UserController`.

---

## Testing

Los tests usan **SQLite en memoria** con `RefreshDatabase`. La suite de humo (`SmokeTest.php`) cubre los flujos principales.

```bash
# Correr todos los tests
composer run test

# Test específico
php artisan test --filter test_nombre_del_test

# Con output detallado
php artisan test --verbose
```

### Escribir un test nuevo

```php
// tests/Feature/MultaTest.php
class MultaTest extends TestCase
{
    use RefreshDatabase;

    public function test_bibliotecario_puede_registrar_multa(): void
    {
        $institucion = Institucion::factory()->create();
        $user = User::factory()->create(['institucion_id' => $institucion->id]);
        $user->assignRole('bibliotecario');

        $this->actingAs($user)
            ->post(route('multas.store'), [...])
            ->assertRedirect(route('multas.index'));
    }
}
```

> Los tests NO deben usar la base de datos real de MySQL — usan SQLite en memoria por configuración en `phpunit.xml`.

---

## Variables de entorno clave

| Variable | Uso |
|----------|-----|
| `DB_*` | Conexión MySQL |
| `APP_KEY` | Encriptación (generado con `artisan key:generate`) |
| `DEFAULT_ADMIN_*` | Credenciales del admin inicial (seed) |
| `DEFAULT_INSTITUCION_*` | Institución creada en el seed |
| `SEED_SAMPLE_DATA=true` | Activa el seeder de datos de prueba |
| `L5_SWAGGER_GENERATE_ALWAYS` | Regenera docs Swagger en cada request (solo dev) |

---

## Despliegue en producción

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

Usar `nginx/eliber.conf` como plantilla de vhost. Variables críticas en `.env` de producción:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://eliber.tuinstitucion.edu.ar
```

---

## Referencia rápida de archivos clave

| Archivo | Propósito |
|---------|-----------|
| `app/Http/Middleware/HandleInertiaRequests.php` | Props globales para todos los componentes Vue |
| `app/Scopes/TenantScope.php` | Filtro automático por `institucion_id` |
| `app/Services/PrestamoService.php` | Toda la lógica de préstamos |
| `app/Services/ReservaService.php` | Toda la lógica de reservas |
| `routes/web.php` | Todas las rutas web y AJAX interno |
| `routes/api.php` | API REST pública (Sanctum) |
| `resources/js/Components/AppNavbar.vue` | Navbar principal con control de permisos |
| `database/seeders/DatabaseSeeder.php` | Punto de entrada del seed |
| `CLAUDE.md` | Instrucciones para el agente de IA (también útil como referencia técnica) |
