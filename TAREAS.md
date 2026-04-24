# Tareas Pendientes — e-LibeR

> Fuente de referencia visual: `Admin-Dashboard/` (React + Tailwind, mock data)  
> Stack objetivo: Laravel 12 + Vue 3 + Inertia.js + Bootstrap 4.6.2

---

## Estado actual de roles

| Rol | Vista | Estado |
|-----|-------|--------|
| `bibliotecario` | App completa (socios, materiales, préstamos, alertas, etc.) | ✅ Implementado |
| `alumno` | Catálogo, mis reservas, perfil | ✅ Implementado |
| `admin` | Dashboard admin con gestión global | ✅ Implementado (crítico) |

---

## 1. Vista del Administrador — Nuevo dashboard

> Adaptar las vistas de `Admin-Dashboard/` de React a Vue 3 + Bootstrap 4.6.

### 1.1 Layout base del admin
- [x] `AdminLayout.vue` — Navbar + sidebar fijo + content area con layout persistente de Inertia
- [x] Sidebar con: Overview, Usuarios, Socios, Materiales, Préstamos, Alertas (con badge), Feedback/Contenido/Analítica/Config (deshabilitados)
- [x] Rutas `/admin/*` protegidas con middleware `role:admin`
- [x] Login redirige al admin a `/admin/dashboard` automáticamente

### 1.2 Overview / Dashboard
- [x] Tarjetas de estadísticas reales: socios activos, préstamos del mes, unidades en stock, alertas sin leer
- [x] Tabla "Últimas reservas pendientes" (últimas 5)
- [x] Tabla "Próximos vencimientos" (4 días)
- [x] Vista: `Admin/Dashboard.vue`

### 1.3 Gestión de Usuarios
- [x] `Admin/Usuarios/Index.vue` — Tabla con búsqueda, filtro por rol, paginación, avatares
- [x] Modal crear/editar inline: nombre, email, usuario, contraseña, rol, foto de perfil, `socio_id` (solo para alumno)
- [x] Vincular `socio_id` al usuario alumno desde el modal
- [x] `AdminController` con CRUD completo + toggle activo

---

## 2. Funcionalidades nuevas — Feedback (Kanban de notas)

> Reemplaza/extiende el sistema actual de Anotaciones.  
> Referencia: `KanbanFeedback.tsx`

- [x] Migración: nueva tabla `feedback_cards` con campos: `titulo`, `descripcion`, `tags` (JSON), `prioridad` (low/medium/high/urgent), `columna` (backlog/in_progress/completed/published), `creado_por` (FK users), `institucion_id`
- [x] `FeedbackController` — CRUD + mover entre columnas (`PATCH feedback/{id}/mover`)
- [x] `Admin/Feedback/Index.vue` — Tablero Kanban con drag-drop (HTML5 nativo + vuedraggable instalado)
- [x] Columnas: Backlog → En progreso → Completado → Publicado
- [x] Tarjeta: título, descripción, tags coloreados, badge de prioridad, autor, fecha
- [x] Modal de configuración: tiempo de desaparición de publicados, colores por prioridad, gestión de tags
- [x] Auto-archivado de cards publicadas según configuración de días

---

## 3. Funcionalidades nuevas — Gestión de Contenido

> Referencia: `ContentManagement.tsx`

### 3.1 Editor de FAQs
- [x] Tabla `faqs`: `pregunta`, `respuesta`, `orden`, `activa`, `institucion_id`
- [x] `ContenidoController` — CRUD FAQs + Footer + Anuncio (todo en uno)
- [x] `Admin/Contenido/Index.vue` — Tabs: FAQs / Anuncio / Footer con modales inline
- [x] Conectar la página pública `/faqs` para mostrar FAQs reales desde BD (fallback estático)

### 3.2 Anuncio global (Banner)
- [x] Columnas en `instituciones`: `anuncio_texto`, `anuncio_estilo`, `anuncio_activo`
- [x] Tab Anuncio en `Admin/Contenido/Index.vue` — editor + preview + toggle
- [x] Mostrar el banner en `AppNavbar.vue` si `anuncio_activo = true` (via Inertia shared data)

### 3.3 Editor de Footer
- [x] Tabla `footer_links`: `label`, `url`, `orden`, `institucion_id`
- [x] Tab Footer en `Admin/Contenido/Index.vue` — lista editable de links
- [x] `AppFooter.vue` — Leer links dinámicos de Inertia shared data

---

## 4. Funcionalidades nuevas — Analítica

> Referencia: `Analytics` placeholder en sidebar del Admin-Dashboard

- [x] `Admin/Analitica/Index.vue` — Panel con gráficos reales:
  - [x] Préstamos por mes (últimos 6 meses) — gráfico de barras (Chart.js)
  - [x] Distribución de materiales por área — gráfico de barras horizontal
  - [x] Socios activos vs dados de baja — gráfico donut
  - [x] Top 5 materiales más prestados — ranking con badges
  - [x] Tarjetas de totales: préstamos, activos, vencidos, materiales, socios
- [x] Instalado `chart.js` + `vue-chartjs`
- [x] `AnaliticaController@index` — Consultas agregadas con Eloquent

---

## 5. Funcionalidades nuevas — Configuración del sistema

> Referencia: `Settings` placeholder en sidebar del Admin-Dashboard

- [x] `Admin/Configuracion/Index.vue` — Formulario de configuración de la institución:
  - [x] Nombre de la institución (actualiza tabla `instituciones`)
  - [x] Días máximos de préstamo (guardado en BD, referencial)
  - [x] Días de alerta anticipada de vencimiento (guardado en BD, referencial)
  - [x] Logo/imagen institucional (almacenado en `storage/app/public/logos/`)
- [x] Tabla `configuraciones` (clave-valor por `institucion_id`)
- [x] `ConfiguracionController` — get/update

---

## 6. Tareas de acceso y navegación

- [x] **Usuarios** — Accesible desde panel admin en `/admin/usuarios`
- [x] **Anotaciones** — Enlace agregado en el dropdown del usuario en `AppNavbar.vue` (visible con permiso `gestionar-anotaciones`)
- [x] **Perfil** — Verificado con test: `test_authenticated_user_can_view_and_update_perfil`

---

## 7. Backlog técnico

- [x] Regenerar Swagger docs: `php artisan l5-swagger:generate`
- [x] Campo `wallpaper` (nullable) en tabla `users` + accessor `wallpaper_url` en modelo User
- [x] Tests para nuevas rutas: `perfil.*`, `alumno.*`, middleware de roles (18/18 pasando)
- [x] Middleware `role:alumno` en grupo `/alumno/*` y `role:admin` ya existía en `/admin/*`

---

## 8. Modo oscuro global (todas las vistas)

- [x] Composable `useDarkMode.js` — singleton con clave `localStorage` por usuario (`eliber-dark-mode-{id}`)
- [x] `AdminLayout.vue` — toggle en navbar, variables dark en `:global([data-theme="dark"]) .admin-shell`
- [x] `AppNavbar.vue` (bibliotecario) — toggle `.dm-toggle` + composable
- [x] `AppNavbarAlumno.vue` — toggle + composable
- [x] `app.css` — bloque global `[data-theme="dark"]` cubre todas las vistas Bootstrap

---

## 9. Gestión de usuarios alumno (bibliotecario)

- [x] `UserController` — soporte para rol alumno con `socio_id` en create/store/edit/update/index
- [x] `Usuarios/Index.vue` — filtro por rol, columna "Socio vinculado" con badge warning si falta vínculo
- [x] `Usuarios/Create.vue` — selector de socio dinámico al elegir rol alumno
- [x] `Usuarios/Edit.vue` — selector de socio para alumno, badge de rol correcto, permisos solo para bibliotecario
- [x] `AppNavbar.vue` — ítem "Usuarios" visible con permiso `gestionar-usuarios`
- [x] Seeder — rol `bibliotecario` recibe los 8 permisos de gestión automáticamente
- [x] Bibliotecario puede activar/desactivar usuarios (excepto admins y a sí mismo)

---

## 10. Registro de alertas de préstamos

- [x] Migración + modelo `Alerta` con `TenantScope`
- [x] `PrestamoService` genera alertas automáticas: `proximo_vencer`, `vencido`, `renovacion`
- [x] `AlertaController` — index paginado con filtros, marcar leída, marcar todas leídas
- [x] Badge en `AppNavbar` con conteo de alertas no leídas (via Inertia shared data)
- [x] `Alertas/Index.vue` — tabla con tipo (badge color), descripción, fecha, estado, acción

---

## 11. Tareas pendientes

### Alta prioridad
- [ ] **Conectar parámetros operacionales**: `PrestamoService` usa `14` días hardcodeado para el límite de devolución y `HandleInertiaRequests` usa `4` días para alertas previas. Leer ambos desde `Configuracion::get(institucion_id, 'dias_prestamo', 14)` / `'dias_alerta_previa'`

### Media prioridad
- [ ] **Sidebar mobile en admin**: al reducir pantalla el sidebar desaparece sin reemplazo. Agregar botón hamburger que muestre/oculte el sidebar como drawer overlay
- [ ] **Dark mode en Chart.js**: los gráficos de `Admin/Analitica/Index.vue` usan colores fijos, no responden al tema oscuro. Watcher en `darkMode` que actualice las opciones de Chart.js

### Baja prioridad
- [ ] **UI de wallpaper**: campo `wallpaper` existe en BD y modelo `User` pero no hay pantalla para subir/visualizar el fondo personalizado del usuario
- [ ] **Limpiar `Admin-Dashboard/`**: directorio en la raíz del proyecto con el dashboard React de referencia (ya no necesario)

---

## Resumen de archivos a crear/modificar

| Acción | Archivo |
|--------|---------|
| Crear | `app/Http/Controllers/AdminController.php` |
| Crear | `app/Http/Controllers/FeedbackController.php` |
| Crear | `app/Http/Controllers/FaqController.php` |
| Crear | `app/Http/Controllers/ConfiguracionController.php` |
| Crear | `database/migrations/*_create_feedback_cards_table.php` |
| Crear | `database/migrations/*_create_faqs_table.php` |
| Crear | `database/migrations/*_create_footer_links_table.php` |
| Modificar | `database/migrations/*_add_anuncio_to_instituciones.php` |
| Crear | `app/Models/FeedbackCard.php` |
| Crear | `app/Models/Faq.php` |
| Crear | `app/Models/FooterLink.php` |
| Crear | `resources/js/Components/AppNavbarAdmin.vue` |
| Crear | `resources/js/Components/AppSidebarAdmin.vue` |
| Crear | `resources/js/Pages/Admin/Dashboard.vue` |
| Crear | `resources/js/Pages/Admin/Usuarios/Index.vue` |
| Crear | `resources/js/Pages/Admin/Feedback/Index.vue` |
| Crear | `resources/js/Pages/Admin/Contenido/Faqs.vue` |
| Crear | `resources/js/Pages/Admin/Contenido/Anuncio.vue` |
| Crear | `resources/js/Pages/Admin/Contenido/Footer.vue` |
| Crear | `resources/js/Pages/Admin/Analitica/Index.vue` |
| Crear | `resources/js/Pages/Admin/Configuracion/Index.vue` |
| Modificar | `routes/web.php` — rutas `/admin/*` con middleware de rol |
| Modificar | `app/Http/Controllers/DashboardController.php` — redirect admin |
| Modificar | `app/Http/Middleware/HandleInertiaRequests.php` — compartir anuncio activo y footer links |
| Modificar | `resources/js/Components/AppNavbar.vue` — mostrar banner de anuncio |
| Modificar | `resources/js/Components/AppFooter.vue` — links dinámicos |
| Modificar | `resources/js/Pages/FAQs.vue` — leer FAQs desde BD |
