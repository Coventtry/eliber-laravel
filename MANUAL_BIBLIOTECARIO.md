# Manual del Bibliotecario — e-LibeR

Sistema de Gestión Bibliotecaria para instituciones educativas.

---

## Tabla de contenidos

1. [Acceso al sistema](#acceso-al-sistema)
2. [Dashboard](#dashboard)
3. [Panel de Socios](#panel-de-socios)
4. [Terminal de Préstamos](#terminal-de-préstamos)
5. [Listado de Préstamos](#listado-de-préstamos)
6. [Solicitudes de Reserva](#solicitudes-de-reserva)
7. [Multas](#multas)
8. [Gestión de Materiales](#gestión-de-materiales)
9. [Gestión de Áreas (Dewey)](#gestión-de-áreas-dewey)
10. [Categorías Físicas](#categorías-físicas)
11. [Noticias](#noticias)
12. [Anotaciones](#anotaciones)
13. [Alertas](#alertas)
14. [Gestión de Usuarios](#gestión-de-usuarios)
15. [Exportaciones](#exportaciones)
16. [Mi Perfil](#mi-perfil)

---

## Acceso al sistema

1. Ingresar a `http://[url-del-sistema]/login`
2. Escribir el **usuario** (no el email) y la **contraseña**
3. El sistema redirige automáticamente a `/dashboard`

### Primera vez — cuenta pendiente de aprobación

Las cuentas nuevas creadas por registro público comienzan con estado **inactivo** y no pueden ingresar hasta que el administrador las apruebe desde `/admin/usuarios`. Una vez aprobada, la cuenta queda activa y el sistema permite el ingreso.

### Cambiar contraseña

La contraseña se puede cambiar desde `/reset-password` o desde **Mi Perfil**. El formulario requiere ingresar la **contraseña actual**, la nueva contraseña y la confirmación. Si no se recuerda la contraseña actual, el administrador puede reestablecerla desde la edición del usuario en `/admin/usuarios`.

---

## Dashboard

**Ruta:** `/dashboard`

Muestra un resumen del estado actual:
- Préstamos activos y atrasados
- Alertas sin leer (badge en la barra superior)
- Acceso rápido a las secciones principales

---

## Panel de Socios

**Ruta:** `/socios`

Vista central de gestión de miembros. Desde aquí se accede al historial completo de cada alumno.

### Filtros disponibles

| Filtro | Descripción |
|--------|-------------|
| Búsqueda | Por nombre, apellido o email |
| Estado | Activos / Inactivos / Todos |
| Año | Año escolar del alumno (1 al 6) |
| División | División del alumno (1 al 6) |
| Solo morosos | Muestra únicamente socios con préstamos atrasados |

Presionar **Buscar** para aplicar. **Limpiar** borra todos los filtros.

### Tabla de socios

**Columnas:** Nombre (apellido, nombre), Email, Año/División, Estado (Activo / Inactivo), Acciones.

Los socios con préstamos atrasados muestran un badge rojo en la columna de deudas.

### Panel de detalle expandible

Al hacer clic en el ícono de **ojo** de cualquier fila, se despliega un panel con dos columnas:

**Columna izquierda — Circulación activa**
- Lista de préstamos vigentes con material, código de ejemplar, fecha de vencimiento y estado
- Las filas con estado **atrasado** se muestran en rojo
- **Botón Extender**: ingresá la cantidad de días (1–30) y presioná ✓ para confirmar la prórroga directamente sin salir del panel

**Columna derecha — Historial y deudas**
- Banner rojo si el socio tiene préstamos atrasados sin devolver (muestra el conteo)
- Historial reciente: últimas devoluciones con fecha

**Botón "Nuevo préstamo"** (verde, esquina superior derecha del panel): abre la Terminal de Préstamos con el socio ya pre-seleccionado.

### Crear un socio manualmente

Desde el botón **"Dar de alta desde Usuarios"** en el encabezado se llega a la gestión de usuarios donde se puede aprobar un alumno registrado o crear uno manualmente.

### Editar un socio

El ícono de **lápiz** abre el formulario de edición con campos: nombre, apellido, email, teléfono, dirección, año y división.

**Dar de baja**: disponible en la pantalla de edición. Marca al socio como inactivo y registra la observación en el historial. Un socio dado de baja no puede recibir préstamos.

**Reactivar**: disponible en la misma pantalla para socios inactivos.

---

## Terminal de Préstamos

**Ruta:** `/prestamos/create` · Acceso también desde **Préstamos → Terminal de préstamos**

Formulario en dos pasos para registrar la salida de material.

### Paso 1 — Identificar al socio

Escribir nombre, apellido o email en el buscador. El sistema muestra sugerencias en tiempo real. Hacer clic en el socio correcto.

Si llegaste desde el botón **"Nuevo préstamo"** del Panel de Socios, el socio ya viene pre-cargado.

### Paso 2 — Seleccionar el material

Escribir título o código en el buscador de material. El sistema muestra resultados con el stock disponible. Hacer clic en el material deseado y completar:

- **Cantidad**: número de ejemplares a prestar
- **Fecha de devolución**: dentro del máximo configurado (por defecto 14 días)

Presionar **Confirmar préstamo**.

### Validaciones automáticas

El sistema rechaza el préstamo si:
- El socio está inactivo (dado de baja)
- El socio ya tiene 3 préstamos activos simultáneos
- El socio ya tiene un préstamo activo del mismo material
- La fecha de devolución supera el máximo configurado
- No hay ejemplares disponibles del material

---

## Listado de Préstamos

**Ruta:** `/prestamos`

### Filtros de estado

Botones en la parte superior para filtrar por: **Todos · Activos · Pendiente · Atrasados · Devueltos**

### Tabla de préstamos

**Columnas:** Socio, Material, Ejemplar (código físico), Fecha de préstamo, Fecha de devolución, Estado, Acciones.

Los préstamos atrasados muestran la fecha de devolución en **rojo negrita**.

**Badges de estado:**
- Azul: Activo
- Amarillo: Pendiente
- Rojo: Atrasado
- Verde: Devuelto

### Acciones por préstamo

**Devolver** (ícono de flecha): registra la devolución, incrementa el stock del material y actualiza el estado a "devuelto". Requiere confirmación.

**WhatsApp** (ícono verde): genera un enlace directo para enviar un recordatorio por WhatsApp al número de teléfono del socio. El mensaje incluye el nombre del material y la fecha de vencimiento. Solo disponible si el socio tiene teléfono registrado.

**Extender** (ícono de calendario): amplía la fecha de devolución. Ingresar la cantidad de días (1–30) y confirmar.

---

## Solicitudes de Reserva

**Ruta:** `/prestamos/solicitudes`

Lista de reservas realizadas por alumnos desde el catálogo que esperan aprobación. La barra superior muestra el conteo de solicitudes pendientes (badge naranja).

**Columnas:** Alumno, Material, Ejemplar reservado, Fecha de solicitud, Estado.

### Aprobar una solicitud

Presionar **Aprobar**: el sistema crea un préstamo automáticamente a nombre del alumno con la fecha de devolución predeterminada (14 días) y cambia el estado del ejemplar a "prestado".

Se envía una notificación al alumno informando que su reserva fue aprobada.

### Rechazar una solicitud

Presionar **Rechazar**: ingresar opcionalmente el motivo. El sistema libera el ejemplar reservado y marca la reserva como rechazada. El alumno puede volver a reservar otro ejemplar del mismo material si hay stock.

---

## Multas

**Ruta:** `/multas`

Gestión de multas por préstamos atrasados.

### Generación automática

El sistema puede generar multas automáticamente cuando el proceso diario marca préstamos como atrasados. El monto se configura directamente en la base de datos (no hay UI para configurarlo en esta versión). Si el monto está en cero, no se generan multas automáticas.

### Registrar una multa manualmente

Desde **Nueva multa**:
1. Buscar y seleccionar el socio
2. Ingresar monto y motivo
3. Guardar

### Acciones sobre multas existentes

- **Cobrar** (botón verde): marca la multa como pagada.
- **Perdonar** (botón amarillo): cierra la multa sin cobro, registrando una observación. Internamente también queda como "pagada" en la base de datos.

### Tabla de multas

**Columnas:** Socio, Monto, Motivo, Estado (**Pendiente** / **Pagada**), Fecha.

> La acción "perdonar" cierra la multa igual que "cobrar" — la distinción queda en las observaciones, no en un estado separado.

---

## Gestión de Materiales

**Ruta:** `/materiales`

### Cargar un material nuevo

1. Ir a **Materiales → Nuevo material** (o desde `/materiales/create`)
2. Completar los campos obligatorios:
   - **Título** y **Autor**
   - **Año de publicación** y **Editorial**
   - **Área** (clasificación Dewey)
   - **Categoría** (tipo de publicación: Libro, Revista, Atlas, etc.)
   - **Código**: se puede generar automáticamente presionando el botón de generación (formato `{dewey}-{secuencia}`, ej: `130-002`)
   - **Clasificación física** (signatura): pasillo, tipo, estante y nivel de ubicación física
   - **Disponibilidad**: cantidad de ejemplares físicos

3. Guardar. El sistema crea automáticamente los registros de ejemplares (`MaterialEjemplar`) según la cantidad indicada.

### Editar un material

Desde el ícono de lápiz en el listado. Si se reduce la disponibilidad, el sistema da de baja automáticamente los ejemplares sobrantes. Si se aumenta, crea los ejemplares nuevos.

### Código QR

Desde el listado o la edición, el botón **QR** genera e imprime un código QR con la información del material. El QR apunta a la ficha pública (`/materiales/{id}/ficha`) que es accesible sin login.

### Gestión de ejemplares físicos

Desde el botón **Ver ejemplares** en la edición de un material:

**Columnas:** Código de ejemplar (formato `{codigo-material}-E{num}`, ej: `130-002-E01`), Estado, Fecha de creación.

**Estados de ejemplar:**

| Estado | Significado |
|--------|-------------|
| **disponible** | En el estante, listo para prestar |
| **prestado** | En manos de un socio |
| **reservado** | Apartado, pendiente de retiro |
| **baja** | Dado de baja (pérdida, deterioro, robo) |

**Dar de baja un ejemplar individualmente**: hacer clic en el botón de baja del ejemplar con estado **disponible**. Ingresar opcionalmente una nota sobre el motivo. No se puede dar de baja un ejemplar **prestado** o **reservado** — primero debe devolverse o cancelarse la reserva.

---

## Gestión de Áreas (Dewey)

**Ruta:** `/areas`

Las áreas agrupan los materiales por clasificación temática según el sistema Dewey.

**Campos de un área:**
- **Código Dewey**: número de clasificación (ej: `130`)
- **Nombre**: descripción completa (ej: "Psicología")
- **Abreviado**: sigla para la signatura física (ej: `PSI`)

Los materiales se asocian a un área al momento de cargarse. El código y la clasificación física del material se generan a partir de los datos del área.

---

## Categorías Físicas

**Ruta:** `/categorias`

Complementan la clasificación Dewey. Permiten agrupar materiales por tipo de publicación: Libro, Revista, Atlas, Material Audiovisual, etc.

Se asignan al crear o editar un material.

---

## Noticias

**Ruta:** `/noticias`

Publicaciones visibles en el panel del alumno y en la landing pública.

- **Crear**: título, cuerpo del artículo e imagen opcional (JPG/PNG)
- **Editar** y **Eliminar**: disponibles desde el listado

Las noticias se muestran ordenadas por fecha de publicación, las más recientes primero.

---

## Anotaciones

**Ruta:** `/anotaciones`

Notas internas visibles solo para el personal de la biblioteca. Útiles para registrar observaciones, recordatorios o comunicaciones internas.

Cada anotación tiene un cuerpo de texto libre. Se pueden crear nuevas desde el botón **Nueva anotación**.

---

## Alertas

**Ruta:** `/alertas`

El sistema genera alertas automáticas en los siguientes eventos:
- Un préstamo está próximo a vencer (dentro de los días configurados)
- Un préstamo venció (estado "atrasado")
- Un alumno realizó una solicitud de reserva

El ícono de campana en la barra superior muestra el conteo de alertas no leídas (badge rojo).

**Marcar como leída:** hacer clic en el ícono de check de cada alerta, o usar el botón **Marcar todas como leídas** para limpiar todas de una vez.

**Baja de material**: si una alerta está vinculada a un ejemplar dado de baja, el botón **Registrar baja** documenta la situación y cierra la alerta.

---

## Gestión de Usuarios

**Ruta:** `/usuarios`

El bibliotecario puede ver y aprobar solicitudes de cuentas de **alumnos**. Las cuentas de bibliotecarios las aprueba el administrador.

### Solicitudes pendientes de aprobación

Panel amarillo en la parte superior con las cuentas de alumnos que esperan activación.

**Aprobar desde el listado**: botón verde **Aprobar** en la fila. El sistema activa la cuenta y crea el registro de Socio automáticamente.

**Aprobar desde la edición**:
1. Hacer clic en el ícono de lápiz
2. En la sección **"Alta en el sistema"** (card verde), hacer clic en **Dar de Alta**
3. El sistema redirige al formulario del Socio para completar apellido, año y división

### Estado del socio vinculado

En la columna **Socio vinculado** del listado:
- **Badge azul** con ID: el alumno ya tiene socio activo
- **"Pendiente de Alta"**: el alumno aún no fue aprobado

---

## Exportaciones

Disponible desde el menú de cada módulo (ícono de descarga).

| Módulo | Formatos |
|--------|---------|
| Socios | CSV · PDF |
| Materiales | CSV · PDF |
| Préstamos | CSV · PDF |
| Multas | CSV · PDF |

Los archivos exportados contienen todos los registros del listado actualmente filtrado.

---

## Mi Perfil

**Ruta:** `/perfil` · Acceso desde el menú desplegable del usuario (esquina superior derecha)

- Actualizar email, apellido
- Cambiar foto de perfil
- Cambiar imagen de portada (banner del dashboard)
- Cambiar contraseña

---

## Preguntas frecuentes

**¿Cuántos préstamos puede tener activos un socio?**
El límite es 3 préstamos simultáneos por defecto. El administrador puede modificarlo en `/admin/configuracion`.

**¿Qué pasa si un alumno no devuelve en plazo?**
El scheduler del sistema marca automáticamente el préstamo como "atrasado" cada día. Aparece una alerta en el panel, la fila del préstamo se pone en rojo y se genera una multa si el administrador configuró un monto diario.

**¿Se puede prestar sin que el alumno tenga cuenta?**
Sí. El bibliotecario puede crear socios manualmente desde **Socios → Nuevo socio** sin necesidad de que el alumno tenga usuario en el sistema.

**¿Qué pasa si el alumno olvida su contraseña?**
Puede usar el enlace **"¿Olvidaste tu contraseña?"** en la pantalla de login.

**¿Cómo sé si una reserva fue aprobada o no?**
Las reservas pendientes aparecen en `/prestamos/solicitudes` y en el badge naranja de la barra superior. Al aprobar, la reserva desaparece de la cola y se crea el préstamo automáticamente.
