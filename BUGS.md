# Bugs — Sistema de Ejemplares

Auditadas las capas de modelo, servicio, controlador y vista.
Severidades: 🔴 HIGH · 🟡 MEDIUM · 🟢 LOW

---

## 🔴 HIGH-1 — `devolverPrestamo` ignora `cantidad`

**Ubicación:** `app/Services/PrestamoService.php:71`

**Problema:** Siempre hace `increment('disponibilidad', 1)` sin leer el campo `cantidad`
del préstamo. Un préstamo creado con `cantidad = 3` devuelve 3 préstamos separados
(uno por ejemplar), cada uno inflando `disponibilidad` en +1 cuando debería ser +1 total.

**Escenario:** Socio pide 2 libros. Se crean 2 Prestamo registros con `cantidad = 1` cada uno.
Se devuelven por separado. Cada devolución suma 1, totalizando +2. `disponibilidad` sube
2 cuando debería subir 1.

**Fix esperado:** El préstamo de cantidad > 1 genera un solo registro, no N registros.
Revisar `crearPrestamo` — si cantidad > 1 debería ser un solo Prestamo con `cantidad = N`
y ejemplar_ids en tabla pivote, O mantener N Prestamo rows pero que `devolverPrestamo`
solo haga `increment(1)` si `cantidad == 1` y sume lo correspondiente.

**Verificado en tests:** ❌ No cubre el escenario de cantidad > 1.

---

## 🔴 HIGH-2 — `crearReserva` — check de disponibilidad fuera de la transacción

**Ubicación:** `app/Services/ReservaService.php:17-23 + 35-42`

**Problema:** La verificación de stock disponible se ejecuta **antes** de `DB::transaction`.
Dos usuarios concurrentes pueden pasar el check simultáneamente, racear por el lock en
el mismo ejemplar, y uno recibe un error luego de ocupar el recurso.

**Fix esperado:** Mover el check de disponibilidad dentro de la transacción,
preferentemente usando `lockForUpdate()` en la consulta del ejemplar también.

---

## 🔴 HIGH-3 — `ajustarEjemplares` — desincronización con exemplares de baja

**Ubicación:** `app/Services/MaterialService.php:54-57 y 70-77`

**Problema:** El conteo de exemplares actuales (`54-57`) incluye solo
`['disponible', 'prestado', 'reservado']` — excluye estado `'baja'`.
Pero la lógica de decremento (`70-77`) solo toca ejemplars `disponible`.

Si un material tiene 5 exemplares, 1 prestado, 2 dados de baja:
- Conteo actual: 1+2=3 (debería ser 4 incluyendo el prestado)
- El delta contra `nuevaDisponibilidad` se calcula mal

Además: `ajustarEjemplares` se llama **antes** de `$material->update()` en el controller,
por lo que la columna numérica `disponibilidad` queda desincronizada con la suma real
de estados de exemplars.

**Fix esperado:** El conteo debe incluir todos los estados excepto `baja`. Y después de
ajustar exemplars, sincronizar `disponibilidad` en el material con la suma real.

---

## 🔴 HIGH-4 — `rechazarReserva` — no restaura ejemplar si estaba aprobada

**Ubicación:** `app/Services/ReservaService.php:97-112`

**Problema:**
1. Si el estado es `aprobada`, el ejemplar quedó en `prestado` y nunca se libera.
2. `disponibilidad_reservada` solo se decrementa para `estado === 'pendiente'`.
   Para reservas `aprobada`, nunca se decrementa, corrompiendo el contador.

**Fix esperado:** En `rechazarReserva`, si `estado === 'aprobada'`, marcar ejemplar como
`disponible` y NO decrementar `disponibilidad_reservada` (ya se usó en aprobación).
Si `estado === 'pendiente'`, restaurar ejemplar + decrementar `disponibilidad_reservada`.

---

## 🔴 HIGH-5 — `destroy` de material sin limpieza de exemplares

**Ubicación:** `app/Http/Controllers/MaterialController.php:114-119`

**Problema:** Se puede eliminar un material con exemplares en estado `prestado` o
`reservado`. Las FK en cascada borran los registros de exemplars pero no se alerta
al bibliotecario, no se verifican préstamos activos, y no se restaura la disponibilidad.

**Fix esperado:** Verificar en el controller que no haya exemplars con estado
`prestado` o `reservado` antes de eliminar. Los de estado `disponible` o `baja` sí
pueden eliminarse. Retornar error si hay préstamos/reservas activas.

---

## 🟡 MEDIUM-6 — Sin CRUD manual de exemplares

**Problema:** No existe ruta ni controller para gestionar exemplares individualmente.
Un bibliotecario no puede:
- Marcar un exemplar como `baja` (dañado/extraviado)
- Agregar un exemplar individual
- Editar notas de un exemplar
- Ver detalle de un exemplar específico

Solo se gestionan indirectamente a través del campo `disponibilidad` del material.
El modal existente en Edit.vue es de solo lectura.

**Fix esperado (mínimo):** Endpoint + botón para dar de baja un exemplar individual.
Lo óptimo: CRUD completo de exemplars.

---

## 🟡 MEDIUM-7 — `Return.vue` sin feedback tras acciones

**Ubicación:** `resources/js/Pages/Prestamos/Return.vue:70-78`

**Problema:** Las funciones `devolver()` y `extender()` usan `router.patch()` sin
`onSuccess` ni `onError`. El componente `FlashMessage` se renderiza pero nunca recibe
un flash. El usuario no tiene confirmación visual.

**Fix esperado:** Agregar `onSuccess` que dispare un flash message, o usar un ref
al componente FlashMessage y mostrar estado de loading/error inline.

---

## 🟡 MEDIUM-8 — `crearPrestamo` sin lock en la fila `material`

**Ubicación:** `app/Services/PrestamoService.php:31`

**Problema:** El `lockForUpdate()` solo se aplica a la consulta de exemplars,
no a la fila `material`. Una request concurrente puede decrementar
`material.disponibilidad` entre el lock del exemplar y el `decrement()` de la línea 57,
causando valores negativos o incorrectos.

**Fix esperado:** Incluir `Material::where('id', $material->id)->lockForUpdate()` antes
de consultar exemplars.

---

## 🟡 MEDIUM-9 — `ejemplaresDisponibles` API sin filtro de tenant

**Ubicación:** `app/Http/Controllers/MaterialController.php:171-184`

**Problema:** El endpoint `/api/materiales/ejemplares-disponibles` no tiene verificación
de `institucion_id`. Cualquier usuario autenticado en cualquier institución puede
consultar exemplars disponibles de cualquier material de cualquier tenant.

**Fix esperado:** Validar que el `material_id` pertenezca a la institución del usuario
autenticado (`auth()->user()->institucion_id`), o usar el modelo con TenantScope
(que ya filtra por defecto si el controller usa el modelo correctamente).

**Nota:** El controller usa `MaterialEjemplar::where(...)` directamente, sin TenantScope
explícito, lo que expone datos cross-tenant.

---

## 🟡 MEDIUM-10 — `MigrarEjemplares` — sin `lockForUpdate` al asignar

**Ubicación:** `app/Console/Commands/MigrarEjemplares.php:87-105`

**Problema:** Al asignar exemplars disponibles a préstamos activos existentes,
la consulta `where('estado', 'disponible')` no usa `lockForUpdate()`. Si otro proceso
crea un préstamo durante la migración, el mismo exemplar podría asignarse a dos
préstamos.

**Fix esperado:** Usar `lockForUpdate()` en la query de ejemplar dentro de la
transacción.

---

## 🟢 LOW-11 — `Return.vue` extender sin confirmación

**Ubicación:** `resources/js/Pages/Prestamos/Return.vue:76-78`

**Problema:** El input de días envía inmediatamente al hacer click en el botón.
Un clic accidental extiende el préstamo sin posibilidad de deshacer.

**Fix esperado:** Agregar `confirm()` antes de enviar.

---

## 🟢 LOW-12 — `ejemplaresDisponibles` scope definido pero nunca usado

**Ubicación:** `app/Models/Material.php:42-45`

El scope `ejemplaresDisponibles()` existe pero no se usa en ningún lugar.
Código muerto. Eliminar o documentar su propósito.

---

## 🟢 LOW-13 — Create.vue sin preview de exemplares a crear

**Ubicación:** `resources/js/Pages/Materiales/Create.vue`

**Problema:** El campo "Disponibilidad" no indica que al guardar se crearán N exemplars.
Un usuario que cambia el número no ve feedback hasta después de guardar.

**Fix esperado:** Mostrar "Se crearán N exemplar(es)" cuando disponibilidad > 0.

---

## 🟢 LOW-14 — `StoreMaterialRequest` sin mensaje sobre exemplars

**Ubicación:** `app/Http/Requests/StoreMaterialRequest.php:22`

**Problema:** La validación solo dice `integer|min:0`. No hay mensaje personalizado
indicando que este valor define cuántos exemplars se generarán.

**Fix esperado:** Agregar mensaje personalizado al rule: `'disponibilidad.integer' => 'Cantidad de exemplares físicos disponibles.'`

---

## 🟢 LOW-15 — Edit.vue — modal de exemplars renderizado dos veces

**Ubicación:** `resources/js/Pages/Materiales/Edit.vue:123-165 y 251-296`

**Problema:** El bloque del modal existe tanto inline (antes del `</form>`) como en un
`<Teleport to="body">`. Ambos están dentro del mismo `v-if="ejemplaresModal"`. Cuando
`ejemplaresModal` es un objeto no-nulo, ambos se renderizan.

**Fix esperado:** Eliminar la versión inline o el Teleport. Dejar solo uno.

---

## 🟢 LOW-16 — `extenderPrestamo` no verifica estado del ejemplar

**Ubicación:** `app/Services/PrestamoService.php:81-98`

**Problema:** Extiende la fecha sin verificar que el ejemplar siga en estado `prestado`
(válido). Un ejemplar en estado `baja` extendería su préstamo sin restricciones.

**Fix esperado:** Verificar `ejemplar.estado === 'prestado'` antes de extender.

---

## 🟢 LOW-17 — `marcarAtrasados` sin lock en actualización masiva

**Ubicación:** `app/Services/PrestamoService.php:100-119`

**Problema:** El método fetch-ea y actualiza en loop sin lock. Una devolución concurrente
en uno de esos préstamos puede racear con el cambio de estado a `atrasado`.

**Fix esperado:** Usar `lockForUpdate()` en la query de préstamos o envolver en
transacción con row-level locking.