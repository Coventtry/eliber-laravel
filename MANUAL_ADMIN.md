# Manual de Usuario — e-LibeR

Sistema de Gestión Bibliotecaria para instituciones educativas.

---

## Tabla de contenidos

1. [Roles del sistema](#roles-del-sistema)
2. [Ingreso al sistema](#ingreso-al-sistema)
3. [Flujo principal de trabajo](#flujo-principal-de-trabajo)
4. [Gestión de Usuarios](#gestión-de-usuarios)
5. [Panel de Socios](#panel-de-socios)
6. [Terminal de Préstamos](#terminal-de-préstamos)
7. [Gestión de Materiales](#gestión-de-materiales)
8. [Gestión de Áreas (Dewey)](#gestión-de-áreas-dewey)
9. [Noticias y Comunicados](#noticias-y-comunicados)
10. [Alertas y Vencimientos](#alertas-y-vencimientos)
11. [Panel de Administración](#panel-de-administración)
12. [Mi Perfil](#mi-perfil)

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

- **FAQs**: preguntas frecuentes que aparecen en la página pública `/faqs`
- **Footer links**: enlaces del pie de página
- **Anuncio**: banner informativo que aparece en la barra superior del sistema

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
