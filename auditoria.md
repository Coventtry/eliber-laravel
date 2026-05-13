# Auditoría Completa del Código — e-LibeR

**Fecha:** 2026-05-09
**Versión:** Laravel 12 + Vue 3 (Inertia SPA)

---

## Resumen de Hallazgos

| Severidad | Cantidad |
|-----------|----------|
| 🔴 CRÍTICO | 10 |
| 🟠 ALTO | 16 |
| 🟡 MEDIO | 22 |
| 🔵 BAJO | 15 |

---

## 🔴 CRÍTICOS (deben arreglarse ya)

### Seguridad — Autorización faltante

| # | Archivo | Línea | Problema |
|---|---------|-------|----------|
| 1 | `app/Http/Controllers/MaterialController.php` | 43 | Sin `$this->authorize('create', Material::class)` — cualquier usuario autenticado puede crear materiales |
| 2 | `app/Http/Controllers/Api/ReservaController.php` | 86 | Sin `$this->authorize()` en `store()` — cualquier API user puede crear reservas para cualquier socio |
| 3 | `app/Http/Controllers/Api/ReservaController.php` | 161 | Sin `$this->authorize()` en `aprobar()` — cualquiera puede aprobar reservas (crea préstamos) |
| 4 | `app/Http/Controllers/Api/ReservaController.php` | 200 | Sin `$this->authorize()` en `rechazar()` — cualquiera puede rechazar reservas |
| 5 | `app/Http/Controllers/AlertaController.php` | 14,37,43,49 | Sin authorize en ningún método — alumnos pueden leer/mutear alertas y dar de baja materiales (`bajaAlerta()` decrementa stock) |
| 6 | `app/Http/Controllers/Api/AlertaController.php` | 62,85,102 | Sin authorize — mismo riesgo via API |

### Modelos/Migraciones — Errores en runtime

| # | Archivo | Línea | Problema |
|---|---------|-------|----------|
| 7 | `app/Models/HistorialSocio.php` + `database/migrations/2024_01_01_000007_add_institucion_id_to_all_tables.php` | 14-16, 19 | **Modelo referencia `institucion_id` en TenantScope** pero la tabla `historial_socios` NO tiene esa columna — cualquier query a `HistorialSocio::` crashea con "Column not found" |
| 8 | `app/Models/Reserva.php` | 9 | **Falta trait `SoftDeletes`** — la migration define `softDeletes()` (línea 23 de migración) pero el modelo ignora; hace hard-delete en vez de soft-delete |

### Vue — Bugs de funcionalidad

| # | Archivo | Líneas | Problema |
|---|---------|--------|----------|
| 9 | `Socios/Edit.vue:16` / `Materiales/Create.vue:10` / `Materiales/Edit.vue:15` / `Socios/Edit.vue:82` | 16, 82 / 10 / 15 | `@enviar.prevent="enviar"` — **`enviar` no es un evento Vue válido**, debería ser `@submit.prevent`. Estos formularios NO se envían al presionar Enter |
| 10 | `Materiales/Create.vue:95` / `Materiales/Edit.vue:67` | 95, 67 | `type="enviar"` en botones — no es un type válido, debería ser `type="submit"` |

---

## 🟠 ALTOS

### Seguridad — Autorización faltante en listados

| # | Archivo | Línea | Problema |
|---|---------|-------|----------|
| 11 | `app/Http/Controllers/SocioController.php` | 18 | `index()` sin `$this->authorize('viewAny', Socio::class)` |
| 12 | `app/Http/Controllers/MaterialController.php` | 18 | `index()` sin `$this->authorize('viewAny', Material::class)` |
| 13 | `app/Http/Controllers/AreaController.php` | 13 | `index()` sin `$this->authorize('viewAny', Area::class)` |
| 14 | `app/Http/Controllers/NoticiaController.php` | 14 | `index()` sin `$this->authorize('viewAny', Noticia::class)` |
| 15 | `app/Http/Controllers/AnotacionController.php` | 14 | `index()` sin `$this->authorize('viewAny', Anotacion::class)` |
| 16 | `app/Http/Controllers/PrestamoController.php` | 23 | `index()` sin `$this->authorize('viewAny', Prestamo::class)` |
| 17 | `app/Http/Controllers/MultaController.php` | 17 | `index()` sin `$this->authorize('viewAny', Multa::class)` |

### Seguridad — Configuración

| # | Archivo | Línea | Problema |
|---|---------|-------|----------|
| 18 | `app/Scopes/TenantScope.php` | 13 | Si `auth()->user()->institucion_id` es null/falsy, **no aplica ningún filtro** — retorna datos de todas las instituciones (fuga de datos multi-tenant) |
| 19 | `routes/web.php` | 41 | Logout sin middleware `auth` — invitados pueden POST a `/logout` |
| 20 | `config/sanctum.php` | 50 | Tokens nunca expiran (`expiration => null`) — si se filtra un token, vale para siempre |

### Backend — Calidad de código

| # | Archivo | Línea | Problema |
|---|---------|-------|----------|
| 21 | `app/Http/Controllers/SocioController.php` | 128 | `console.log('confirmarBaja called...')` — código de depuración en producción |
| 22 | `app/Services/PrestamoService.php` | 176-213 | `validarCreacion()` demasiado largo (5 validaciones), debería dividirse |
| 23 | `app/Services/PrestamoService.php` | 58-75 | `extenderPrestamo()` sin `DB::transaction()` — si la alerta falla, la extensión igual se aplica |
| 24 | `app/Services/SocioService.php` | 10-34 | `darDeBaja()` y `reactivar()` sin `DB::transaction()` — múltiples writes sin protección |
| 25 | `app/Services/MultaService.php` | 53-54 | Race condition en `generarMultaPorVencimiento()` — dos requests concurrentes pueden pasar el `if ($existe)` simultáneo |
| 26 | `app/Services/ReservaService.php` | 20-22, 29-31 | Usa `\Exception` genérica vs `ValidationException` del resto de servicios — inconsistente |

### Policies faltantes

| # | Archivo | Problema |
|---|---------|----------|
| 27 | — | **No existe `AlertaPolicy`** — `AlertaController` web + API sin autorización |
| 28 | — | **No existe `ReservaPolicy`** — `PrestamoController::aprobarSolicitud()` + `Api\ReservaController` sin autorización basada en policies |

---

## 🟡 MEDIOS

### Tests

| # | Archivo | Problema |
|---|---------|----------|
| 29 | `database/factories/` | No existen factories para 14/17 modelos (solo `UserFactory`) |
| 30 | `database/factories/UserFactory.php:35` | `institucion_id => 1` hardcodeado — falla si no existe institución con ID 1 |
| 31 | `database/factories/UserFactory.php:27-36` | No genera `usuario`, `apellido`, `telefono`, `anio`, `division` — usuarios factory no pueden autenticarse |
| 32 | — | Faltan tests: MaterialService, SocioService, policies, auth API, aislamiento multi-tenant, permisos específicos |
| 33 | `tests/Feature/SmokeTest.php:591-669` | Helpers privados (`createInstitucion`, `createBibliotecario`, etc.) no reutilizables desde otros tests |
| 34 | `tests/Unit/Services/NotificacionTest.php:69,93,119,138` | `area_id => 1` hardcodeado — rompe si cambia orden de ejecución de tests |

### Migraciones

| # | Archivo | Problema |
|---|---------|----------|
| 35 | `2026_04_24_130000_add_wallpaper_to_users.php` | Sin `hasColumn()` guard |
| 36 | `2026_04_25_200000_add_apellido_anio_division_to_users.php` | Sin `hasColumn()` guard |
| 37 | `2026_04_24_063049_change_anotacion_column_to_text_in_anotaciones.php` | Sin `hasColumn()` guard |
| 38 | `2026_05_09_172423_modify_tipo_column_in_alertas_table.php` | Sin `hasColumn()` guard |

### Modelos — TenantScope faltante

| # | Archivo | Problema |
|---|---------|----------|
| 39 | `app/Models/Configuracion.php` | Sin TenantScope — `Configuracion::all()` retorna datos de todas las instituciones |
| 40 | `app/Models/Faq.php` | Sin TenantScope |
| 41 | `app/Models/FeedbackCard.php` | Sin TenantScope |
| 42 | `app/Models/FooterLink.php` | Sin TenantScope |

### Modelos — Relaciones faltantes

| # | Archivo | Problema |
|---|---------|----------|
| 43 | `app/Models/Institucion.php` | Faltan 11 relaciones HasMany inversas (areas, noticias, anotaciones, alertas, reservas, multas, etc.) |
| 44 | `app/Models/Socio.php` | Falta `reservas()` y `multas()` |
| 45 | `app/Models/Material.php` | Falta `reservas()` |
| 46 | `app/Models/Material.php` | `disponibilidad_reservada` no está en `$fillable` |

### API

| # | Archivo | Problema |
|---|---------|----------|
| 47 | `routes/api.php` | Todas las rutas API sin `->name()` — inconsistente con web routes |
| 48 | `routes/api.php:65` | `alertas/{id}` no usa route model binding (`{alerta}`) |
| 49 | `app/Http/Controllers/Api/*` | Ningún controller API usa route model binding — todos usan `int $id` con `findOrFail` |

### Performance

| # | Archivo | Problema |
|---|---------|----------|
| 50 | `app/Http/Middleware/HandleInertiaRequests.php:32-49` | Múltiples queries DB en cada request (Configuracion, alertas, reservas, footerLinks) — sin caché |

### Vue — XSS potencial

| # | Archivo | Línea | Problema |
|---|---------|-------|----------|
| 51 | `Multas/Index.vue` | 65 | `v-html="multas.links"` renderiza HTML crudo del servidor — riesgo XSS si el backend es comprometido |
| 52 | `Socios/Index.vue`, `Materiales/Index.vue`, `Prestamos/Index.vue`, `Usuarios/Index.vue` | varias | `v-html="link.label"` en paginación — bajo riesgo (labels del paginador de Laravel) pero bypass Vue sanitization |

### Vue — UX

| # | Archivo | Problema |
|---|---------|----------|
| 53 | 9+ archivos | Usan `confirm()`/`prompt()` nativo en vez del componente `ConfirmModal` existente |
| 54 | 10+ páginas | Botones destructivos sin estado `:disabled` durante carga — posible doble-click |
| 55 | 6+ páginas | Llamadas API (axios) sin manejo de error — fallos silenciosos |
| 56 | 15+ páginas | Botones de solo ícono sin `aria-label` — inaccesibles para lectores de pantalla |
| 57 | 6+ páginas | Código de paginación duplicado — debería extraerse a componente `Pagination.vue` |
| 58 | `FlashMessage.vue` | No soporta tipo `info` (solo success/error) — los flash `info` se muestran como `danger` |

### Vue — Estilos

| # | Archivo | Problema |
|---|---------|----------|
| 59 | Todos los .vue | 200+ estilos inline que bypass el dark mode (mayor especificidad que CSS variables) |
| 60 | `resources/css/app.css:1` | Comentario dice "Bootstrap 5" pero el proyecto usa Bootstrap 4 |

---

## 🔵 BAJOS

| # | Archivo | Problema |
|---|---------|----------|
| 61 | Varios controllers | `create()` y `edit()` sin `$this->authorize()` (bajo riesgo porque el `store()`/`update()` sí autoriza) |
| 62 | `app/Http/Controllers/AdminController.php` | Usa `abort_if()` / checks manuales en vez de `UserPolicy` (mitigado por `role:admin` middleware) |
| 63 | `app/Http/Controllers/ContenidoController.php`, `FeedbackController.php` | Usan `authorize_tenant()` / `authorize_institucion()` custom en vez de policies (mitigado por `role:admin`) |
| 64 | `app/Models/Bibliotecario.php` | Modelo existe pero la tabla fue dropeada — si algún código lo referencia, crashea |
| 65 | `app/Models/Area.php` | Columna `Abreviado` (con A mayúscula) rompe convención snake_case |
| 66 | `routes/web.php:70` | `anotaciones` resource sin ruta `destroy`, pero `AnotacionPolicy::delete()` existe |
| 67 | Varios | Sin middleware CORS, CSP, ni TrustProxies configurados |
| 68 | `RegisterController.php` | Sin rate limiting en registro público |
| 69 | `app/Http/Middleware/HandleInertiaRequests.php:60` | Expone lista completa de permisos al frontend |
| 70 | `Socios/Edit.vue:113`, `Materiales/Create.vue:146-153`, `Materiales/Create.vue:171-179` | 3 patrones distintos de modales (Bootstrap 4 jQuery, manual `d-block`, `confirm()` nativo) |
| 71 | `AdminLayout.vue:163-185` + `app.css:507-518` | Dos sistemas de variables CSS para dark mode que pueden derivar |
| 72 | `Composables/useDarkMode.js` | No respeta `prefers-color-scheme` del SO — siempre arranca en light |
| 73 | `AppFooter.vue:36-41` | Carga imágenes desde CDN externo (`cdn.jsdelivr.net`) |
| 74 | `app.js:9-12` | Expone jQuery globalmente para Bootstrap 4 — posible conflicto con Vue reactivity |
| 75 | `resources/js/Pages/ResetPassword.vue:10` | Usa sintaxis legacy `$page.props` (Inertia 1.x) en vez de `usePage()` |

---

## ✅ Lo que está bien (fortalezas)

### Arquitectura

- **Service layer pattern** consistente: 5 servicios (`PrestamoService`, `MaterialService`, `SocioService`, `ReservaService`, `MultaService`) con responsabilidades bien delimitadas
- **Inyección de dependencias** via constructor en todos los controllers principales
- **Multi-tenancy vía TenantScope** en todos los modelos principales con `institucion_id`
- **Migraciones con `hasColumn()`** en la mayoría de los casos — seguras para producción
- **Notificaciones email** implementadas con `ShouldQueue` y 3 notification classes

### Seguridad

- **Fillable explícito** en todos los modelos — sin `$guarded = []`
- **Autorización con policies** en store/update/delete de Socio, Area, Noticia, Prestamo, Multa, User
- **Sin SQL injection** — todas las queries usan parameter binding de Laravel
- **Sin mass assignment vulnerable** — controllers construyen arrays explícitos, no pasan `$request->all()`
- **Sanctum** para API auth con tokens

### Frontend

- **Uso correcto de Ziggy `route()`** — sin URLs hardcodeadas
- **Inertia SPA** con buenas prácticas (page props, flash messages, form helper)
- **Dark mode funcional** con persistencia y toggle
- **Componentes reutilizables** básicos (`AppNavbar`, `AppFooter`, `FlashMessage`)
- **Sin dependencias externas pesadas** — Bootstrap 4 via npm, no CDN

### Tests

- **RefreshDatabase** en todos los tests que tocan DB
- **SmokeTest** cubre 19 flows principales (login, CRUD, export, permisos)
- **Unit tests** para PrestamoService (10 tests), ReservaService (9 tests), MultaService (5 tests), NotificacionTest (4 tests)
- **Notification::fake()** usado correctamente en tests de email
- **forceCreate()** para modelos con TenantScope en tests

---

## Prioridades Recomendadas

| Prioridad | Acción | Archivos afectados |
|-----------|--------|-------------------|
| **🔥 Inmediata** | Arreglar `@enviar.prevent` → `@submit.prevent` y `type="enviar"` → `type="submit"` | `Socios/Edit.vue`, `Materiales/Create.vue`, `Materiales/Edit.vue` |
| **🔥 Inmediata** | Agregar `$this->authorize()` en `AlertaController` y `Api\AlertaController` | `AlertaController.php`, `Api/AlertaController.php` |
| **🔥 Inmediata** | Agregar `$this->authorize('create')` en `MaterialController::store()` | `MaterialController.php` |
| **🔥 Inmediata** | Agregar `$this->authorize()` en `Api\ReservaController` (store, aprobar, rechazar) | `Api/ReservaController.php` |
| **🔥 Inmediata** | Agregar `institucion_id` a `historial_socios` (columna faltante) | Migration + `HistorialSocio.php` |
| **⚡ Alta** | Agregar `SoftDeletes` trait a `Reserva` | `Reserva.php` |
| **⚡ Alta** | Arreglar `TenantScope` para cuando `auth()->user()->institucion_id` es null | `TenantScope.php` |
| **⚡ Alta** | Agregar `$this->authorize('viewAny')` a los 7 `index()` faltantes | 7 controllers |
| **⚡ Alta** | Crear `AlertaPolicy` y `ReservaPolicy` | `Policies/` |
| **⚡ Alta** | Mover logout dentro del grupo `auth` | `routes/web.php` |
| **🟡 Media** | Extraer helpers de tests a trait reutilizable | `tests/` |
| **🟡 Media** | Reemplazar `v-html` en paginación por componente | 6+ Vue files |
| **🟡 Media** | Agregar `loading` state a botones destructivos | 10+ Vue files |
| **🟡 Media** | Agregar `aria-label` a botones de solo ícono | 15+ Vue files |
| **🟡 Media** | Agregar TenantScope a `Configuracion`, `Faq`, `FeedbackCard`, `FooterLink` | 4 models |
| **🟡 Media** | Agregar `Schema::hasColumn()` guards a migrations faltantes | 4 migrations |
| **🟡 Media** | Agregar route model binding en API controllers | `Api/*Controller.php` |
| **🟡 Media** | Agregar `->name()` a rutas API | `routes/api.php` |
| **🟡 Media** | `DB::transaction()` en `extenderPrestamo()`, `SocioService` | `PrestamoService.php`, `SocioService.php` |
| **🔵 Baja** | Agregar factories para modelos faltantes | `database/factories/` |
| **🔵 Baja** | Extraer componente `Pagination.vue` y `ModalWrapper.vue` | `resources/js/Components/` |
| **🔵 Baja** | Consolidar estilos inline a CSS clases | Múltiples Vue files |
| **🔵 Baja** | Configurar CORS, CSP, TrustProxies | `config/`, middleware |
| **🔵 Baja** | Agregar rate limiting a registro público | `RegisterController.php` |
| **🔵 Baja** | Arreglar `UserFactory` (institucion_id, usuario, etc.) | `UserFactory.php` |

---

## Totales

| Categoría | Tests | Líneas de código (aprox.) |
|-----------|-------|--------------------------|
| Tests | 52 tests, 115 assertions | ~1,600 |
| PHP (app/) | — | ~4,500 |
| Vue (resources/js/) | — | ~5,500 |
| Migraciones | 20 archivos | ~800 |
| Total | — | ~12,400 |
