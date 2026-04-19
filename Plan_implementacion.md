# Plan de Implementación — e-LibeR

---

## 1. Objetivo

Transformar el sistema actual en una plataforma con tres capas claramente separadas:

1. **Landing page pública** — visible sin autenticación, con noticias dinámicas y acceso al login
2. **Sistema de administración** (post-login) — Administrador y Bibliotecario con permisos delegables
3. **App de alumnos** *(fase futura, independiente)* — reservas y catálogo; el sistema actual debe quedar preparado para integrarla vía API

---

## 2. Estado Actual

### Lo que existe
* Autenticación básica con modelo `Bibliotecario` (guard 'web', Eloquent, un solo rol implícito)
* CRUD completos: Socios, Materiales, Préstamos, Áreas, Noticias, Anotaciones
* Capa de servicios: `PrestamoService`, `MaterialService`, `SocioService`
* Dashboard funcional con alertas de vencimiento y links de WhatsApp
* Laravel 12 + Vue 3 + Inertia.js + Bootstrap 4.6

### Limitaciones técnicas a resolver antes de avanzar
* `material_id` tiene conflicto de tipo (UNSIGNED INT vs INT) — FK inválida detectada en migraciones
* `$timestamps = false` en todos los modelos legacy — sin `created_at`/`updated_at`
* `activo` en Socio actúa como soft delete sin usar el trait `SoftDeletes` de Laravel
* Ya existe tabla `users` vacía (migración `0001_01_01_000000`) — base para el nuevo sistema de auth

---

## 3. Estrategia Técnica

### Roles y permisos
* **Dos roles activos en el sistema actual**: `admin` y `bibliotecario`
* El `admin` tiene acceso total y puede **delegar permisos individuales** al bibliotecario desde una UI de gestión
* El menú y las acciones disponibles se calculan en base a los **permisos reales del usuario**, no solo su rol
* Usar **`spatie/laravel-permission`** — soporta asignación de permisos tanto a roles como a usuarios individuales

### Multi-tenancy (infraestructura)
* Un solo esquema de base de datos con `institucion_id` en todas las entidades
* **Global Scopes** filtran automáticamente por institución del usuario autenticado
* Preparado para múltiples instituciones, aunque en el despliegue inicial solo haya una

### API para app futura de alumnos
* Instalar **`laravel/sanctum`** ahora para no tener que reestructurar auth después
* Los endpoints del catálogo y reservas se diseñan con prefijo `/api/v1/` desde el inicio
* La app de alumnos se autentica con tokens de Sanctum; el sistema web sigue con sesiones

### Paquetes a instalar
```bash
composer require spatie/laravel-permission
composer require laravel/sanctum
```

---

## 4. Etapas de Implementación

---

### ETAPA 0 — Limpieza técnica previa (prerequisitos)

**Obligatoria. Sin esto, las migraciones siguientes pueden fallar.**

#### 4.0.1 Resolver FK type mismatch en `material_id`

Migración: `fix_material_id_type_in_prestamos`
```php
Schema::table('prestamos', function (Blueprint $table) {
    $table->unsignedBigInteger('material_id')->change();
});
```

#### 4.0.2 Estándar de soft deletes

Decisión: **no migrar `activo` a `SoftDeletes`** para no romper lógica existente.
Las entidades nuevas (Reserva, User) usarán `SoftDeletes` desde el inicio.

#### 4.0.3 Preparar tabla `users` para auth

Migración: `expand_users_table_for_auth`
```
users:
  - id, email, password, remember_token, timestamps  (ya existen)
  - nombre        string
  - institucion_id  unsignedBigInteger, nullable inicialmente
  - activo        boolean, default true
```
> El rol se gestiona exclusivamente via spatie/laravel-permission, no como columna en `users`.

---

### ETAPA 1 — Landing Page Pública

La landing es una ruta pública (`/`) separada del sistema de administración (que pasa a vivir en `/admin` o requiere auth en `/`).

#### 4.1 Definir la ruta pública

En `routes/web.php`:
```php
// Pública — sin middleware auth
Route::get('/', LandingController::class)->name('landing');

// Sistema de administración — protegido
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    // ... resto de rutas
});
```

#### 4.2 LandingController

```php
class LandingController extends Controller {
    public function __invoke(): Response {
        $noticias = Noticia::latest()->take(6)->get(['titulo', 'cuerpo', 'created_at']);
        return Inertia::render('Landing', ['noticias' => $noticias]);
    }
}
```

> Las noticias se cargan desde la tabla existente. No requiere nuevo modelo.

#### 4.3 Componente Vue `Pages/Landing.vue`

Estructura sugerida:
* Header con logo/nombre del sistema
* Sección de noticias/novedades (cards dinámicas)
* Botón o formulario de login que redirige a `/login`

El formulario de login puede vivir en la misma página (modal o sección inline) o en `/login` por separado — según decisión de diseño.

---

### ETAPA 2 — Institución y migración de datos

#### 4.4 Crear tabla `instituciones`

Migración: `create_instituciones_table`
```
instituciones:
  - id
  - nombre
  - slug       unique
  - estado     enum('activa', 'inactiva'), default 'activa'
  - timestamps
  - softDeletes
```

#### 4.5 Agregar `institucion_id` a tablas existentes y migrar datos

**Crítico**: todos los registros actuales deben asignarse a una institución por defecto en la misma migración que agrega la columna.

Patrón para cada tabla (migraciones individuales, no destructivas):
```php
Schema::table('socios', function (Blueprint $table) {
    $table->unsignedBigInteger('institucion_id')->nullable()->after('id');
    $table->foreign('institucion_id')->references('id')->on('instituciones');
});

// Asignar institución por defecto — sin este paso los registros quedan huérfanos
DB::table('socios')->whereNull('institucion_id')
    ->update(['institucion_id' => $idInstitucionPrincipal]);

// Hacer NOT NULL después de limpiar nulos
Schema::table('socios', function (Blueprint $table) {
    $table->unsignedBigInteger('institucion_id')->nullable(false)->change();
});
```

Tablas afectadas: `socios`, `materiales`, `prestamos`, `areas`, `noticias`, `anotaciones`, `bibliotecarios`, `users`

#### 4.6 Global Scope de tenant

Crear `app/Scopes/TenantScope.php`:
```php
class TenantScope implements Scope {
    public function apply(Builder $builder, Model $model): void {
        if (auth()->check()) {
            $builder->where($model->getTable() . '.institucion_id',
                           auth()->user()->institucion_id);
        }
    }
}
```

Aplicar en cada modelo con `booted()`:
```php
protected static function booted(): void {
    static::addGlobalScope(new TenantScope());
}
```

---

### ETAPA 3 — Roles y Permisos Delegables

#### 4.7 Instalar spatie/laravel-permission

```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

Agregar el trait al modelo `User`:
```php
use Spatie\Permission\Traits\HasRoles;
class User extends Authenticatable {
    use HasRoles;
}
```

#### 4.8 Roles del sistema

Solo dos roles activos en esta fase:

| Rol | Descripción |
|-----|-------------|
| `admin` | Acceso total. Gestiona usuarios y puede delegar permisos al bibliotecario. |
| `bibliotecario` | Acceso según permisos que el admin le haya otorgado. |

> El rol `alumno` no existe en este sistema. Es parte de la app futura independiente.

#### 4.9 Permisos granulares

Seeder: `database/seeders/RolesAndPermissionsSeeder.php`

```php
$permisos = [
    'gestionar-usuarios',     // crear/editar/desactivar usuarios del sistema
    'gestionar-materiales',   // CRUD materiales
    'gestionar-socios',       // CRUD socios
    'gestionar-prestamos',    // crear préstamos, registrar devoluciones
    'gestionar-areas',        // CRUD áreas de clasificación
    'gestionar-noticias',     // CRUD noticias (visibles en landing)
    'gestionar-anotaciones',  // notas internas
    'ver-reportes',           // dashboard con estadísticas
];

Permission::insert(array_map(fn($p) => ['name' => $p, 'guard_name' => 'web'], $permisos));

// El admin tiene todos los permisos siempre — se gestiona por rol, no por permiso individual
Role::create(['name' => 'admin'])->givePermissionTo(Permission::all());

// El bibliotecario empieza sin permisos; el admin los asigna desde la UI
Role::create(['name' => 'bibliotecario']);
```

#### 4.10 Delegación de permisos desde la UI

El `admin` puede ver y editar los permisos de cada bibliotecario desde la vista de gestión de usuarios.

En `UserController::updatePermisos()`:
```php
// Solo el admin puede ejecutar esto
public function updatePermisos(Request $request, User $user): RedirectResponse {
    abort_if(!auth()->user()->hasRole('admin'), 403);
    abort_if($user->hasRole('admin'), 403); // no se puede editar a otro admin

    $user->syncPermissions($request->validated()['permisos']);
    return back();
}
```

**Lógica de evaluación de acceso**: un bibliotecario tiene acceso a una acción si tiene el permiso asignado directamente (por el admin) O si tiene el rol `admin`.

```php
// En los controladores se usa $user->can() — funciona para ambos casos
$this->authorize('gestionar-socios'); // spatie resuelve: rol admin O permiso directo
```

#### 4.11 Migrar autenticación de Bibliotecario a User

1. Cambiar provider en `config/auth.php`: de `bibliotecarios` → `users` (modelo `User`)
2. Migrar registros existentes (comando artisan o seeder de transición):
```php
foreach (Bibliotecario::all() as $bib) {
    User::create([
        'nombre'         => $bib->nombre,
        'email'          => $bib->email,
        'password'       => $bib->password, // ya hasheado
        'institucion_id' => $idInstitucionPrincipal,
        'activo'         => true,
    ])->assignRole('admin'); // el primer bibliotecario se convierte en admin
}
```
3. Mantener tabla `bibliotecarios` como respaldo hasta confirmar estabilidad
4. Actualizar `HandleInertiaRequests` para compartir `auth()->user()` con permisos al frontend

#### 4.12 Registrar middleware

En `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role'       => \Spatie\Permission\Middleware\RoleMiddleware::class,
        'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        'tenant'     => \App\Http\Middleware\EnsureTenantAccess::class,
    ]);
})
```

---

### ETAPA 4 — Menú Dinámico por Permisos

El menú se construye en base a los **permisos reales** del usuario (no solo el rol), de manera que refleja exactamente las secciones habilitadas para cada bibliotecario.

En `app/Http/Middleware/HandleInertiaRequests.php`:
```php
public function share(Request $request): array {
    return [
        ...parent::share($request),
        'auth' => [
            'user'    => $request->user(),
            'permisos' => $request->user()?->getAllPermissions()->pluck('name'),
        ],
        'menu' => $this->buildMenu($request->user()),
    ];
}

private function buildMenu(?User $user): array {
    if (!$user) return [];

    $items = [
        ['label' => 'Dashboard',    'route' => 'dashboard',          'permission' => null],
        ['label' => 'Socios',       'route' => 'socios.index',       'permission' => 'gestionar-socios'],
        ['label' => 'Materiales',   'route' => 'materiales.index',   'permission' => 'gestionar-materiales'],
        ['label' => 'Préstamos',    'route' => 'prestamos.index',    'permission' => 'gestionar-prestamos'],
        ['label' => 'Áreas',        'route' => 'areas.index',        'permission' => 'gestionar-areas'],
        ['label' => 'Noticias',     'route' => 'noticias.index',     'permission' => 'gestionar-noticias'],
        ['label' => 'Anotaciones',  'route' => 'anotaciones.index',  'permission' => 'gestionar-anotaciones'],
        ['label' => 'Usuarios',     'route' => 'usuarios.index',     'permission' => 'gestionar-usuarios'],
        ['label' => 'Reportes',     'route' => 'reportes.index',     'permission' => 'ver-reportes'],
    ];

    return collect($items)
        ->filter(fn($item) => $item['permission'] === null || $user->can($item['permission']))
        ->values()
        ->toArray();
}
```

> `$user->can()` de spatie evalúa tanto el rol como los permisos individuales asignados, por lo que el admin siempre ve todo y el bibliotecario solo lo que tenga habilitado.

---

### ETAPA 5 — Preparación de API para App de Alumnos

Esta etapa no implementa la app de alumnos, pero instala la infraestructura necesaria para que la integración futura no requiera cambios estructurales.

#### 4.13 Configurar Sanctum

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

En `config/auth.php`, agregar guard para la API:
```php
'guards' => [
    'web' => [...],  // sigue siendo el guard del sistema web
    'api' => [
        'driver'   => 'sanctum',
        'provider' => 'users',
    ],
],
```

#### 4.14 Endpoints de API a diseñar ahora

Crear `routes/api.php` con los endpoints del catálogo público (no requieren auth para consulta, sí para reservas):

```php
// Prefijo: /api/v1/
Route::prefix('v1')->group(function () {
    // Catálogo — público, sin auth
    Route::get('materiales', [Api\MaterialController::class, 'index']);
    Route::get('materiales/{material}', [Api\MaterialController::class, 'show']);
    Route::get('noticias', [Api\NoticiaController::class, 'index']);

    // Reservas — requieren token de alumno (auth:sanctum)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('reservas', [Api\ReservaController::class, 'index']);
        Route::post('reservas', [Api\ReservaController::class, 'store']);
        Route::delete('reservas/{reserva}', [Api\ReservaController::class, 'destroy']);
    });
});
```

> Los controladores en `app/Http/Controllers/Api/` son independientes de los del sistema web. Devuelven JSON puro (no Inertia).

#### 4.15 Consideraciones de diseño para la API

* **Paginación** desde el inicio: todos los listados usan `paginate()` con respuesta en formato `{ data: [], meta: {} }`
* **Recursos API**: usar `JsonResource` para controlar qué campos se exponen (evitar exponer `institucion_id`, campos internos, etc.)
* **CORS**: configurar `config/cors.php` para permitir el origen de la app de alumnos
* **Rate limiting**: aplicar el throttle `api` de Laravel por defecto (60 req/min)

---

### ETAPA 6 — Seguridad y Políticas de Acceso

#### 4.16 Policies como segunda línea de defensa

El Global Scope filtra automáticamente, pero siempre validar ownership en operaciones destructivas:

```bash
php artisan make:policy SocioPolicy --model=Socio
php artisan make:policy MaterialPolicy --model=Material
# ... una por entidad principal
```

Policy base para entidades con `institucion_id`:
```php
public function update(User $user, Socio $socio): bool {
    return $user->institucion_id === $socio->institucion_id
        && $user->can('gestionar-socios');
}
```

#### 4.17 Reglas adicionales

* `institucion_id` nunca es campo editable en formularios — se asigna siempre desde `auth()->user()->institucion_id` en el controlador
* En `StoreXRequest` / `UpdateXRequest`: remover `institucion_id` de `rules()`
* El admin no puede editar los permisos de otro admin (validar en `UserController`)
* Para la API: nunca exponer datos de otra institución, validar en `JsonResource` o en el controlador

---

## 5. Checklist por entidad

| Entidad        | `institucion_id` | Global Scope | Policy | API Resource |
|----------------|:---:|:---:|:---:|:---:|
| User           | ✓   | ✓   | ✓  | —  |
| Socio          | ✓   | ✓   | ✓  | —  |
| Material       | ✓   | ✓   | ✓  | ✓  |
| Prestamo       | ✓   | ✓   | ✓  | —  |
| Area           | ✓   | ✓   | ✓  | —  |
| Noticia        | ✓   | ✓   | ✓  | ✓  |
| Anotacion      | ✓   | ✓   | ✓  | —  |
| HistorialSocio | ✓   | ✓   | —  | —  |
| Notificacion   | ✓   | ✓   | —  | —  |

---

## 6. Orden de implementación

```
ETAPA 0  →  ETAPA 1      →  ETAPA 2       →  ETAPA 3      →  ETAPA 4  →  ETAPA 5  →  ETAPA 6
Limpieza    Landing Page     Multi-tenant     Roles/Permisos  Menú         API base     Seguridad
```

Dependencias críticas:
* Etapa 0 → prerequisito de todo (FK correcto, tabla users preparada)
* Etapa 2 → prerequisito de Etapa 3 (los permisos necesitan `institucion_id` en users)
* Etapa 3 → prerequisito de Etapa 4 (el menú filtra por permisos reales)

---

## 7. Riesgos y mitigaciones

| Riesgo | Mitigación |
|--------|-----------|
| Datos existentes sin `institucion_id` | La migración asigna institución por defecto en el mismo paso |
| FK `material_id` inválida | Etapa 0 resuelve el type mismatch antes de nuevas migraciones |
| Pérdida de sesiones al cambiar guard | Ejecutar en mantenimiento; limpiar tabla `sessions` tras el cambio |
| Bibliotecario accede a URL directa sin permiso | Policies + middleware `permission:` como doble validación |
| API expone datos de otra institución | `JsonResource` filtra campos + Global Scope activo también en API |
| Permisos inconsistentes (rol admin sin permiso explícito) | spatie evalúa OR: `hasRole('admin') OR hasPermission(x)` — admin siempre pasa |

---

## 8. Estimación de esfuerzo

| Etapa | Complejidad | Esfuerzo estimado |
|-------|-------------|-------------------|
| 0 — Limpieza técnica | Baja | 2–4 horas |
| 1 — Landing Page | Baja–Media | 4–8 horas |
| 2 — Multi-tenant + datos | Media–Alta | 1–2 días |
| 3 — Roles + permisos delegables + migración auth | Alta | 2–3 días |
| 4 — Menú dinámico por permisos | Baja | 3–5 horas |
| 5 — API base (Sanctum + endpoints catálogo) | Media | 1 día |
| 6 — Policies + seguridad | Media | 1 día |
| **Total** | | **~7–11 días** |

---

## 9. Fase futura — App de Alumnos (independiente)

Una vez completadas las etapas anteriores, la app de alumnos se integra sin cambios estructurales:

**Qué requiere implementar:**
* Tabla `reservas` con estados: `pendiente`, `aprobada`, `rechazada`, `expirada`
* Campo `disponibilidad_reservada` en `materiales` (ver nota de stock en sección reservas)
* `ReservaService` con flujo: solicitar → aprobar/rechazar → generar préstamo automático
* Rol `alumno` en spatie, con permisos: `ver-materiales`, `crear-reservas`
* Comando `php artisan reservas:expirar` para el scheduler
* Completar endpoints API de reservas (ya definidos con prefijo `/api/v1/`)

**Nota sobre stock y reservas:**
Al aprobar una reserva, `PrestamoService::crearPrestamo()` debe considerar `disponibilidad - disponibilidad_reservada` como el stock real disponible para nuevos préstamos directos.

**La API ya estará lista** en Etapa 5 — solo falta implementar la lógica de negocio de reservas y el rol alumno.

---

Fin del documento
