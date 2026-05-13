# Auditorías

> Checklist de auditorías del proyecto e-Liber.  
> Las completadas llevan `[x]`, pendientes `[ ]`.

---

## 🎨 CSS / Estilos

- [x] Consistencia de colores y tokens CSS (paleta unificada en `:root`)
- [x] Utilidades de espaciado (`.gap-*`)
- [x] Utilidades de tipografía (`.fs-*`, `.lh-*`, `.text-eliber`)
- [x] Utilidades de bordes (`.rounded-lg`, `.rounded-xl`)
- [x] Tamaños de íconos consistentes (`.icon-xl`, `.icon-lg`, `.icon-sm`, `.icon-2xl`)
- [x] Modal backdrop unificado (`.modal-backdrop-custom`)
- [x] Dark mode cubre todas las clases nuevas
- [x] Tabla hover sin scale (look más limpio)
- [x] Navbar avatar/profile/dropdown unificados (`.nav-avatar-initials`, `.nav-user-*`, `.navbar-dropdown-menu`)
- [x] Login page: input groups y tabs con scoped CSS
- [x] Páginas públicas (Login, Landing, FAQs, Acerca) limpias de inline styles
- [x] Formularios: `main-container` centrado automático
- [x] Tech badges con clase `.badge-tech`

## 🏎️ Performance / N+1 Queries

- [x] Revisar `resources/js/Pages/` por componentes que acceden a relaciones sin eager loading
  - ✅ `PrestamoController::index` — `with(['socio', 'material.area'])` correcto
  - ✅ `AlumnoController::dashboard/misPrestamos/misReservas` — `with('material')` correcto
  - ✅ `MultaController::index` — `with('socio:id,nombre,apellido')` correcto
  - ✅ `AdminController::dashboard` — `with(['material', 'socio'])` correcto
  - ✅ 35+ controladores verificados con eager loading correcto
- [x] Verificar que `TenantScope` no cause queries adicionales en listados
  - ✅ No causa N+1 — solo agrega `WHERE institucion_id = ?` a cada query
- [x] `PrestamoService`, `SocioService` — revisar `with()` y `load()` en consultas
  - ✅ PrestamoService: `marcarAtrasados()` usa `with(['socio', 'material'])`
  - ✅ `obtenerVencimientosProximos()` usa `with(['socio', 'material'])`
  - ✅ `devolverPrestamo()` — ✅ fijado con `load('material')` explícito
  - ✅ `ReservaService::crearReserva()` — ✅ fijado con `load('socio')` explícito
  - ✅ `MultaService::generarMultaPorVencimiento()` — ✅ fijado con `load('material')` explícito
- [x] Lazy loading de rutas Vue (dynamic `import()`) para reducir bundle inicial
  - ✅ `import.meta.glob()` usado en `app.js` — todas las rutas lazy-loaded por defecto
- [x] **BUG FIX**: `Prestamos/Index.vue` — `p.material?.socio?.telefono` → `p.socio?.telefono` (WhatsApp nunca se mostraba)

## 📱 Responsive / Mobile

- [ ] Sidebar admin: overlay y comportamiento en mobile
- [ ] Tablas: `.table-responsive` en todos los listados
- [ ] Touch targets: botones pequeños (`btn-sm`) en mobile
- [ ] Navbar colapsa correctamente en todos los tamaños

## ♿ Accesibilidad

- [ ] Focus trapping en modales
- [ ] `aria-live` en regiones flash / errores
- [ ] Color contrast ratio en dark mode
- [ ] Skip navigation link

## 🧪 Cobertura de Tests

- [ ] Tests para controladores API
- [ ] Casos borde de `PrestamoService` (extender vencido, devolver ya devuelto, etc.)
- [ ] `ReservaService::expirarReservasVencidas()` con datos reales
- [ ] Tests de policies para todos los permisos

## 🐛 Error Handling

- [ ] API: errores de validación devuelven JSON consistente
- [ ] Páginas 404/500 personalizadas (sin stack trace)
- [ ] `try-catch` en controladores — los servicios lanzan excepciones, ¿quién las atrapa?

## 📦 Bundle Audit

- [ ] Revisar `npm run build` output — bibliotecas pesadas para code-split
- [ ] jQuery: importar solo lo necesario de Bootstrap 4 JS
- [ ] Bootstrap 4 CSS: tree-shaking con PurgeCSS
