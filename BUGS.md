# Bugs — Sistema de Ejemplares

Auditadas las capas de modelo, servicio, controlador y vista.
Severidades: 🔴 HIGH · 🟡 MEDIUM · 🟢 LOW

---

## ✅ Resueltas

| # | Descripción | Fix aplicado en |
|---|-------------|-----------------|
| HIGH-1 | `devolverPrestamo` ignora `cantidad` | No aplica — diseño correcto (N Prestamo rows × cantidad=1) |
| HIGH-2 | `crearReserva` — check fuera de transaction | `ReservaService`: check movido dentro de DB::transaction con lockForUpdate |
| HIGH-3 | `ajustarEjemplares` — ignora estado baja | `MaterialService`: conteo corregido + nuevo `sincronizarDisponibilidad()` |
| HIGH-4 | `rechazarReserva` — no libera ejemplar aprobado | `ReservaService`: ahora restaura ejemplar en ambos estados |
| HIGH-5 | `destroy` material sin verificar exemplares | `MaterialController`: bloquea si hay ejemplares prestados/reservados |
| MED-6 | Sin gestión manual de exemplares | `MaterialController::bajaEjemplar` + botón en modal Edit.vue |
| MED-7 | Return.vue sin feedback tras acciones | `onSuccess`/`onError` + confirm en extender |
| MED-8 | `crearPrestamo` sin lock en fila material | `lockForUpdate()` en Material antes de exemplars |
| MED-9 | API `ejemplaresDisponibles` sin filtro tenant | Validación `institucion_id` en el controller |
| MED-10 | `MigrarEjemplares` sin lockForUpdate | `MigrarEjemplares.php`: lockForUpdate en migrarMateriales y migrarPrestamosActivos |
| R2-1 | `bajaEjemplar` permitía estado reservado | Resuelto — bloquea ambos estados + test |
| R2-2 | `ExpirarReservas` no rest. ejemplar | Resuelto — delega a service |
| R2-3 | `aprobarReserva` sin lock en ejemplar | Resuelto — lockForUpdate agregado |
| R2-4 | `update` sin lock en fila material | Resuelto — lockForUpdate en controller |
| LOW-11 | Return.vue extender sin confirmación | `confirm()` antes de enviar |
| LOW-12 | Scope `ejemplaresDisponibles` muerto | Eliminado del modelo Material |
| LOW-13 | Create.vue sin preview de exemplares | `<small>` informativo: "Se crearán N exemplar(es)" |
| LOW-14 | StoreMaterialRequest sin mensaje | `messages()` personalizados para disponibilidad |
| LOW-15 | Modal ejemplares duplicado en Edit.vue | Teleport duplicado eliminado; Index.vue ahora link a editar |
| LOW-16 | `extenderPrestamo` sin verificar estado ejemplar | Verifica `ejemplar.estado === 'prestado'` |
| LOW-17 | `marcarAtrasados` sin lock | `DB::transaction` con `lockForUpdate()` |
