# Fases 1-4 — Completadas

## Fase 1 — Seguridad UX y robustez
- **Axios error handling**: 5 llamadas sin `.catch()` → `try/catch` con `alert()` (Socios/Index, Materiales/Create, Prestamos/Create)
- **`:disabled` + spinner**: 10 botones (búsqueda ×4, reactivar, marcar todas, rechazar modal, extender, logout ×2)

## Fase 2 — Consolidación de patrones
- **BaseModal.vue**: componente reutilizable (`show`, `title`, `size`, `centered`, slots)
- **5 modales custom** migrados a BaseModal (Admin/Feedback ×3, Admin/Contenido ×2)
- **ConfirmModal.vue** eliminado (código muerto, dependía de `window.$()`)
- **Bibliotecario.php**: `@deprecated`
- **6 SVGs devicon** → CDN a `public/img/devicon/` (AppFooter)
- jQuery se mantiene (requerido por Bootstrap 4)

## Fase 3 — Accesibilidad
- **`aria-label`**: 24 botones de solo íconos (editar, eliminar, toggle pass, dark mode, etc.)
- **`:disabled` + spinner**: 7 botones destructivos críticos (logout, baja, eliminar, toggle activo)

## Fase 4 — CSS
- **Variables de AdminLayout** modo oscuro → referencian `var(--dm-*)` de `app.css` (14 valores deduplicados)
- **`.form-container`** clase utilitaria agregada a `app.css`

## Tests
- **72 tests, 143 assertions** — todos verdes después de cada fase
- **Build Vite** exitoso después de cada fase
