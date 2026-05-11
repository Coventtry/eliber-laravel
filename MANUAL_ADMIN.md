# Manual de Usuario — e-LibeR

Sistema de Gestión Bibliotecaria para instituciones educativas.

---

## Tabla de contenidos

1. [Roles del sistema](#roles-del-sistema)
2. [Ingreso al sistema](#ingreso-al-sistema)
3. [Página pública](#página-pública)
4. [Flujo principal de trabajo](#flujo-principal-de-trabajo)
5. [Gestión de Usuarios](#gestión-de-usuarios)
6. [Panel de Socios](#panel-de-socios)
7. [Terminal de Préstamos](#terminal-de-préstamos)
8. [Gestión de Materiales](#gestión-de-materiales)
9. [Gestión de Áreas (Dewey)](#gestión-de-áreas-dewey)
10. [Categorías Físicas](#categorías-físicas)
11. [Gestión de Ejemplares](#gestión-de-ejemplares)
12. [Noticias y Comunicados](#noticias-y-comunicados)
13. [Alertas y Vencimientos](#alertas-y-vencimientos)
14. [Panel de Administración](#panel-de-administración)
15. [Mi Perfil](#mi-perfil)

---

## Roles del sistema

| Rol | Quién es | Qué puede hacer |
|-----|----------|-----------------|
| **Administrador** | Director, rector o encargado del sistema | Todo: configuración, usuarios, analítica, contenido |
| **Bibliotecario** | Personal de la biblioteca | Socios, materiales, préstamos, áreas, noticias |
| **Alumno** | Estudiante registrado | Ver catálogo, hacer y cancelar reservas, ver su historial |

---

## Ingreso al sistema

1. Ingresar a `http://[url-del-sistema]/login`
2. Escribir el **usuario** (no el email) y la **contraseña**
3. El sistema redirige automáticamente al panel según el rol

### Registro de nuevos usuarios

Los alumnos y bibliotecarios se pueden registrar desde la pestaña **"Registrarse"** en la pantalla de login. Las cuentas nuevas quedan **en espera de aprobación** — no pueden ingresar hasta que un responsable las active.

### Recuperación de contraseña

En la pantalla de login, el enlace **"¿Olvidaste tu contraseña?"** permite al usuario reestablecer su contraseña. Para eso debe:
1. Ingresar su **nombre de usuario** (no el email)
2. Ingresar su **contraseña actual**
3. Ingresar la **nueva contraseña** (mínimo 8 caracteres)

> Si el usuario no recuerda su contraseña actual, un administrador puede reestablecerla desde la edición del usuario.

---

## Página pública

El sistema tiene una página de inicio pública visible sin necesidad de iniciar sesión:

**Landing (`/`)**: Muestra las últimas noticias publicadas. Es la puerta de entrada al sistema.

**FAQs (`/faqs`)**: Preguntas frecuentes configuradas desde el panel de administración. Solo se muestran las que están marcadas como **activas**.

**Ficha de material (`/materiales/{id}/ficha`)**: Información pública de un material (título, autor, área, clasificación). Útil para compartir por QR o WhatsApp sin requerir autenticación.

---

## Flujo principal de trabajo

El circuito completo sigue este orden:

```
[1] Alumno se registra
        ↓
[2] Bibliotecario aprueba en Usuarios  →  se crea el Socio automáticamente
        ↓
[3] Bibliotecario completa los datos del Socio (apellido, año, división)
        ↓
[4] Bibliotecario registra el préstamo desde el Panel de Socios o la Terminal
        ↓
[5] Seguimiento de vencimientos y prórrogas desde el Panel de Socios
        ↓
[6] Alumno devuelve el material  →  Bibliotecario registra la devolución
```

---

## Gestión de Usuarios

**Ruta:** `/usuarios` (bibliotecario) · `/admin/usuarios` (administrador)

### Solicitudes pendientes de aprobación

Al ingresar a la vista de Usuarios aparece un panel amarillo con todas las cuentas que esperan aprobación. Para cada solicitud se muestra: nombre, email, usuario y fecha de registro.

**Aprobar desde el listado:**
- Hacer clic en el botón verde **Aprobar** en la fila correspondiente.
- El sistema activa la cuenta y crea automáticamente el registro de Socio.

**Aprobar desde la edición:**
1. Hacer clic en el ícono de lápiz (editar) sobre el usuario.
2. En la sección **"Alta en el sistema"** (card verde), hacer clic en **Dar de Alta**.
3. El sistema redirige al formulario del Socio para completar apellido, año y división.

### Estado del socio vinculado

En la columna **"Socio vinculado"** del listado:
- **Badge azul** con ID: el alumno ya tiene socio activo
- **"Pendiente de Alta"**: el alumno aún no fue aprobado

### Búsqueda de usuarios

Usar el campo de búsqueda para filtrar por nombre, email o nombre de usuario.

---

## Panel de Socios

**Ruta:** `/socios`

Vista central de gestión. Desde acá el bibliotecario tiene acceso completo al historial de cada alumno.

### Filtros disponibles

| Filtro | Descripción |
|--------|-------------|
| Texto | Busca por nombre, apellido o email |
| Estado | Activos / Inactivos / Todos |
| Año | Filtra por año escolar (1 al 6) |
| División | Filtra por división (1 al 6) |
| Solo morosos | Muestra únicamente socios con préstamos atrasados |

### Panel de detalle expandible

Al hacer clic en el ícono de ojo ( 👁 ) de cualquier fila, se despliega un panel con:

**Circulación activa**
- Lista de préstamos vigentes con estado y fecha de vencimiento
- Las filas en rojo indican préstamos **atrasados**
- Botón **Extender**: permite ampliar la fecha de devolución directamente desde acá (ingresar cantidad de días → confirmar con ✓)

**Historial reciente**
- Últimas 10 devoluciones registradas del socio

**Alerta de deudas**
- Banner rojo si el socio tiene préstamos atrasados sin devolver

**Botón "Nuevo préstamo"**
- Abre la Terminal de Préstamos con el socio ya pre-seleccionado

### Editar un socio

Hacer clic en el ícono de lápiz para editar datos personales (nombre, apellido, email, año, división, teléfono, dirección).

Las acciones **Dar de baja** y **Reactivar** están disponibles en la pantalla de edición.

---

## Terminal de Préstamos

**Ruta:** `/prestamos/create` · También desde el menú **Préstamos → Terminal de préstamos**

Formulario en dos pasos para registrar la salida de material.

### Paso 1 — Identificar al socio

- Escribir nombre, apellido o email en el campo de búsqueda
- El sistema muestra sugerencias en tiempo real
- Hacer clic en el socio correcto para seleccionarlo
- Si se llegó desde el Panel de Socios, el socio ya viene pre-cargado

### Paso 2 — Seleccionar el material

- Escribir título o código de barras en el buscador de material
- El sistema muestra resultados con stock disponible en tiempo real
- Hacer clic en el material para seleccionarlo
- Completar cantidad y fecha de devolución (máximo 14 días)
- Hacer clic en **Confirmar préstamo**

### Listado de préstamos activos

**Ruta:** `/prestamos`

Muestra todos los préstamos ordenados por urgencia: atrasados primero, luego activos por fecha de vencimiento.

**Acciones disponibles:**
- **Devolver**: registra la devolución e incrementa el stock del material
- **Extender**: amplía la fecha de devolución (1 a 30 días)
- **WhatsApp**: genera un enlace directo para recordar al socio por mensaje

---

## Gestión de Materiales

**Ruta:** `/materiales`

### Cargar un nuevo material

1. Ir a **Materiales → Nuevo material** (o desde `/materiales/create`)
2. Completar: título, autor, área (clasificación Dewey), categoría, año de publicación, editorial, cantidad disponible
3. El **código** se puede generar automáticamente según el área
4. La **clasificación física** (signatura) indica pasillo, tipo, estante y nivel donde está ubicado
5. Guardar

### Generar código QR

Desde el listado o la edición de un material, el botón **QR** genera e imprime un código QR con la información del ejemplar.

### Código de barras / código de clasificación

Formato automático: `{codigo_dewey}-{secuencia}` (ej: `1300-002`). Se puede sobrescribir manualmente.

---

## Gestión de Áreas (Dewey)

**Ruta:** `/areas`

Las áreas agrupan los materiales por clasificación temática (sistema Dewey).

Cada área tiene:
- **Código Dewey**: número de clasificación (ej: `130`)
- **Nombre**: descripción completa (ej: "Psicología")
- **Abreviado**: sigla para la signatura física (ej: `PSI`)

Los materiales se asocian a un área al momento de cargarse.

---

## Categorías Físicas

**Ruta:** `/categorias`

Las categorías físicas complementan la clasificación Dewey y permiten agrupar materiales por tipo de publicación (libro, revista, mapa, DVD, etc.).

Cada categoría tiene:
- **Nombre**: ej: "Libro", "Revista", "Atlas", "Material Audiovisual"
- Se asigna al crear o editar un material

---

## Gestión de Ejemplares

Cada material puede tener múltiples **ejemplares** físicos. El sistema los gestiona automáticamente al crear un material (tantos ejemplares como `disponibilidad` se indique) y al ajustar el stock.

### Estados de un ejemplar

| Estado | Significado |
|--------|-------------|
| **disponible** | Está en el estante, listo para prestar |
| **prestado** | Está en manos de un socio |
| **reservado** | Alguien lo apartó, pero aún no lo retiró |
| **baja** | Dado de baja (robo, pérdida, deterioro) |

### Dar de baja un ejemplar

Desde el detalle de un material (`/materiales/{id}`):
1. Hacer clic en **Ver ejemplares**
2. Localizar el ejemplar con estado **disponible**
3. Hacer clic en el botón de **baja**
4. Opcional: ingresar una nota sobre el motivo

> No se puede dar de baja un ejemplar que esté **prestado** o **reservado** — primero debe devolverse.

Cuando se reduce la **disponibilidad** de un material desde la edición, el sistema da de baja automáticamente los ejemplares sobrantes (los últimos de la lista).

### Código de ejemplar

Cada ejemplar tiene un código único con formato `{codigo-material}-E{seq}` (ej: `1300-002-E01`). Se usa para identificar el ejemplar físico en la terminal de préstamos.

---

## Noticias y Comunicados

**Ruta:** `/noticias`

Publicaciones visibles en el panel del alumno y en la landing pública.

- Crear con título, cuerpo y opcionalmente una imagen
- Se muestran ordenadas por fecha de publicación
- Se pueden editar o eliminar en cualquier momento

---

## Alertas y Vencimientos

**Ruta:** `/alertas`

El sistema genera alertas automáticas cuando un préstamo está próximo a vencer o ya venció.

- El ícono en la barra superior muestra el conteo de alertas no leídas
- Desde `/alertas` se pueden marcar como leídas individualmente o todas a la vez
- Los préstamos atrasados también aparecen como aviso en la barra superior (badge naranja)

---

## Panel de Administración

**Ruta:** `/admin/dashboard` · Solo accesible con rol **Administrador**

### Dashboard

Métricas generales: total de socios, materiales, préstamos activos, préstamos atrasados y actividad reciente.

### Usuarios (`/admin/usuarios`)

- Ver todos los usuarios de la institución filtrados por rol
- Aprobar solicitudes pendientes de **bibliotecarios** (los alumnos los aprueba el bibliotecario)
- Crear, editar, activar/desactivar y eliminar usuarios
- Asignar permisos individuales a bibliotecarios

### Configuración (`/admin/configuracion`)

- Nombre e institución
- Logo institucional
- Parámetros del sistema (días de alerta de vencimiento, límite de préstamos por socio, días máximos de préstamo)

### Contenido (`/admin/contenido`)

- **FAQs**: preguntas frecuentes que aparecen en la página pública `/faqs`. Cada FAQ puede activarse o desactivarse individualmente con el toggle **Activa**. Las desactivadas no se muestran al público.
- **Footer links**: enlaces del pie de página visibles en todo el sistema y en la landing pública.
- **Anuncio**: banner informativo que aparece en la barra superior del sistema. Configurable con texto, estilo (info/warning/danger/success) y toggle de activación.

### Analítica (`/admin/analitica`)

Reportes de uso: materiales más prestados, socios más activos, evolución de préstamos por período.

### Feedback (`/admin/feedback`)

Tablero Kanban interno para registrar ideas, mejoras y bugs del sistema. Las tarjetas se pueden mover entre columnas (Por hacer / En progreso / Hecho).

---

## Mi Perfil

**Ruta:** `/perfil`

Disponible para todos los roles desde el menú desplegable del usuario (esquina superior derecha).

- Actualizar email
- Actualizar apellido
- Actualizar año y división *(solo alumnos)*
- Cambiar foto de perfil
- Cambiar imagen de portada (banner)
- Cambiar contraseña

---

## Preguntas frecuentes

**¿Qué pasa si un alumno olvida su contraseña?**
Puede usar el enlace **"¿Olvidaste tu contraseña?"** en la pantalla de login.

**¿Se puede hacer un préstamo sin que el alumno tenga cuenta?**
Sí. El bibliotecario puede crear socios manualmente desde `/socios` → *Dar de alta desde Usuarios*, o bien crear directamente el usuario desde `/usuarios`.

**¿Cuántos préstamos puede tener activos un socio?**
El máximo está configurado en `/admin/configuracion`. Por defecto son 3 préstamos simultáneos.

**¿Qué pasa si se supera la fecha de devolución?**
El sistema marca el préstamo como **atrasado** automáticamente y genera una alerta. El bibliotecario puede extender el plazo o registrar la devolución.

**¿Cómo se aprueba a un nuevo bibliotecario?**
Las solicitudes de bibliotecarios las aprueba el **Administrador** desde `/admin/usuarios` (panel amarillo de solicitudes pendientes).
