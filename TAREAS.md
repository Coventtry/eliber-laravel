# Tareas Pendientes — e-LibeR

> Fuente de referencia visual: `Admin-Dashboard/` (React + Tailwind, mock data)  
> Stack objetivo: Laravel 12 + Vue 3 + Inertia.js + Bootstrap 4.6.2

---

## Estado actual de roles

| Rol | Vista | Estado |
|-----|-------|--------|
| `bibliotecario` | App completa (socios, materiales, préstamos, alertas, etc.) | ✅ Implementado |
| `alumno` | Catálogo, mis reservas, perfil | ✅ Implementado |
| `admin` | Dashboard admin con gestión global | ⬜ Pendiente |

---

## 1. Vista del Administrador — Nuevo dashboard

> Adaptar las vistas de `Admin-Dashboard/` de React a Vue 3 + Bootstrap 4.6.

### 1.1 Layout base del admin
- [ ] `AppNavbarAdmin.vue` — Navbar con selector de institución activa y menú de usuario
- [ ] `AppSidebarAdmin.vue` — Sidebar lateral (Overview, Usuarios, Socios, Inventario, Tareas, Feedback, Contenido, Analítica, Configuración)
- [ ] Layout wrapper `AdminLayout.vue` con navbar + sidebar + slot de contenido
- [ ] Ruta `/admin/dashboard` → `AdminController@dashboard`, redirigir al admin en login

### 1.2 Overview / Dashboard
- [ ] Tarjetas de estadísticas reales: Total socios activos, Préstamos del mes, Materiales en stock, Alertas no leídas
- [ ] Tabla "Últimas reservas pendientes" (últimas 5)
- [ ] Tabla "Préstamos próximos a vencer" (próximos 4 días)
- [ ] Vista: `Admin/Dashboard.vue`

### 1.3 Gestión de Usuarios
- [ ] `Admin/Usuarios/Index.vue` — Tabla con búsqueda, filtro por rol e institución, paginación
- [ ] `Admin/Usuarios/Modal.vue` — Modal crear/editar: nombre, email, contraseña, rol, institución, foto de perfil (drag-drop), permisos (toggles)
- [ ] Vincular `socio_id` al usuario alumno desde el modal de edición
- [ ] `AdminController@usuarios` o mover lógica a controlador existente `UserController`

---

## 2. Funcionalidades nuevas — Feedback (Kanban de notas)

> Reemplaza/extiende el sistema actual de Anotaciones.  
> Referencia: `KanbanFeedback.tsx`

- [ ] Migración: nueva tabla `feedback_cards` con campos: `titulo`, `descripcion`, `tags` (JSON), `prioridad` (low/medium/high/urgent), `columna` (backlog/in_progress/completed/published), `creado_por` (FK bibliotecarios), `fecha`, `institucion_id`
- [ ] `FeedbackController` — CRUD + mover entre columnas (`PATCH feedback/{id}/mover`)
- [ ] `Admin/Feedback/Index.vue` — Tablero Kanban con drag-drop (usar `vue-draggable-plus` o `@vueuse/gesture`)
- [ ] Columnas: Backlog → En progreso → Completado → Publicado
- [ ] Tarjeta: título, descripción, tags coloreados, badge de prioridad, autor, fecha
- [ ] Modal de configuración: tiempo de desaparición de publicados, colores por prioridad, gestión de tags
- [ ] Auto-archivado de cards publicadas según configuración de días

---

## 3. Funcionalidades nuevas — Gestión de Contenido

> Referencia: `ContentManagement.tsx`

### 3.1 Editor de FAQs
- [ ] Tabla `faqs`: `pregunta`, `respuesta`, `orden`, `activa`, `institucion_id`
- [ ] `FaqController` — CRUD
- [ ] `Admin/Contenido/Faqs.vue` — Lista expandible, modal edición, reordenamiento
- [ ] Conectar la página pública `/faqs` para mostrar FAQs reales desde BD (actualmente es estática)

### 3.2 Anuncio global (Banner)
- [ ] Tabla `anuncios` o columnas en `instituciones`: `anuncio_texto`, `anuncio_estilo` (warning/danger/info/success), `anuncio_activo`
- [ ] `Admin/Contenido/Anuncio.vue` — Editor de texto + selector de estilo + preview + toggle
- [ ] Mostrar el banner en `AppNavbar.vue` si `anuncio_activo = true` (via Inertia shared data)

### 3.3 Editor de Footer
- [ ] Tabla `footer_links`: `label`, `url`, `orden`, `institucion_id`
- [ ] `Admin/Contenido/Footer.vue` — Lista editable de links
- [ ] `AppFooter.vue` — Leer links dinámicos de Inertia shared data en lugar de links hardcodeados

---

## 4. Funcionalidades nuevas — Analítica

> Referencia: `Analytics` placeholder en sidebar del Admin-Dashboard

- [ ] `Admin/Analitica/Index.vue` — Panel con gráficos reales:
  - Préstamos por mes (últimos 6 meses) — gráfico de barras
  - Distribución de materiales por área — gráfico de torta
  - Socios activos vs dados de baja — gráfico de barras
  - Top 5 materiales más prestados
- [ ] Usar `Chart.js` (ya disponible en el proyecto) o `vue-chartjs`
- [ ] `AdminController@analitica` — Consultas agregadas con Eloquent

---

## 5. Funcionalidades nuevas — Configuración del sistema

> Referencia: `Settings` placeholder en sidebar del Admin-Dashboard

- [ ] `Admin/Configuracion/Index.vue` — Formulario de configuración de la institución:
  - Nombre de la institución
  - Días máximos de préstamo (actualmente hardcodeado en 14)
  - Días de alerta anticipada de vencimiento (actualmente hardcodeado en 4)
  - Logo/imagen institucional
- [ ] Tabla `configuraciones` (clave-valor por `institucion_id`) o nuevos campos en `instituciones`
- [ ] `ConfiguracionController` — get/update

---

## 6. Tareas de acceso y navegación

- [ ] **Usuarios** — Definir desde dónde se accede (quitado del navbar del bibliotecario). Opciones: panel admin, configuración del sistema, o re-agregar al navbar con permiso `gestionar-usuarios`
- [ ] **Anotaciones** — El listado `/anotaciones` no tiene acceso desde ningún punto de navegación. Agregar enlace en el footer o en el dropdown del usuario
- [ ] **Perfil** — Verificar que el navbar del bibliotecario muestre correctamente el enlace "Mi perfil" (ya implementado pero sin test)

---

## 7. Backlog técnico

- [ ] Regenerar Swagger docs: `php artisan l5-swagger:generate` (incluir endpoint `alertas.baja-material`)
- [ ] Agregar campo `wallpaper` (nullable) a `bibliotecarios` para foto de portada del perfil (referencia: `UserModal.tsx`)
- [ ] Tests para las nuevas rutas: `alumno.*`, `perfil.*`, `alertas.baja-material`
- [ ] Middleware de autorización por rol para las rutas `/admin/*` (solo `admin`) y `/alumno/*` (solo `alumno`)

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
