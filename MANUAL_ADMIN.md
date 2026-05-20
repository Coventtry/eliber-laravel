# Manual del Administrador — e-LibeR

Sistema de Gestión Bibliotecaria para instituciones educativas.

---

## Tabla de contenidos

1. [Acceso al sistema](#acceso-al-sistema)
2. [Panel de administración (Dashboard)](#panel-de-administración-dashboard)
3. [Gestión de Usuarios](#gestión-de-usuarios)
4. [Configuración del sistema](#configuración-del-sistema)
5. [Contenido público](#contenido-público)
6. [Analítica](#analítica)
7. [Tablero de Feedback](#tablero-de-feedback)
8. [Cambiar institución activa](#cambiar-institución-activa)
9. [Mi Perfil](#mi-perfil)

---

## Acceso al sistema

1. Ingresar a `http://[url-del-sistema]/login`
2. Escribir el **usuario** (no el email) y la **contraseña**
3. El sistema redirige automáticamente a `/admin/dashboard`

> El administrador tiene acceso completo a todo el sistema. Las funciones de operación diaria (socios, préstamos, materiales) están documentadas en el Manual del Bibliotecario.

---

## Panel de administración (Dashboard)

**Ruta:** `/admin/dashboard`

### Indicadores principales

Cuatro tarjetas en la parte superior muestran el estado actual de la institución:

| Indicador | Descripción |
|-----------|-------------|
| **Socios activos** | Cantidad de miembros con cuenta habilitada |
| **Préstamos del mes** | Préstamos registrados en el mes en curso |
| **Unidades en stock** | Total de ejemplares disponibles en estante |
| **Alertas sin leer** | Alertas del sistema pendientes de revisión |

### Panel derecho — Reservas pendientes

Lista las reservas solicitadas por alumnos que aún no fueron procesadas. Muestra: nombre del alumno, material y fecha de solicitud. El botón **Ver todas** lleva a la cola de solicitudes.

### Panel izquierdo — Próximos vencimientos

Muestra los préstamos activos o pendientes cuya fecha de devolución está entre **hoy** y **hoy + N días**, donde N es el valor de **Días de alerta previa al vencimiento** configurado en `/admin/configuracion` (default: 4 días).

La fecha de devolución la define el **bibliotecario al crear el préstamo**, dentro del límite de **Días máximos de préstamo** (default: 14 días desde hoy).

> **Ejemplo:** Hoy es 20/05. Con alerta = 4 días, se listan préstamos con vencimiento entre el 20/05 y el 24/05. Un préstamo creado el 20/05 con vencimiento 02/06 no aparece hasta el 29/05.

Muestra alumno, material y fecha de vencimiento resaltada. El botón **Ver todos** abre el listado de préstamos filtrado.

---

## Gestión de Usuarios

**Ruta:** `/admin/usuarios`

El administrador gestiona todos los usuarios del sistema, de todos los roles.

### Panel de solicitudes pendientes

Al ingresar aparece un panel amarillo con todas las cuentas que esperan aprobación. Muestra: nombre, email, usuario y fecha de registro.

- **Aprobar** (botón verde): activa la cuenta y, si el rol es alumno, crea el registro de Socio automáticamente.
- **Rechazar / Eliminar**: elimina la cuenta si fue creada por error.

> Los bibliotecarios los aprueba el administrador. Los alumnos los puede aprobar también el bibliotecario desde `/usuarios`.

### Listado de usuarios

Filtros disponibles:
- Búsqueda por nombre, email o usuario
- Filtro por rol (admin, bibliotecario, alumno)
- Filtro por estado (activos / inactivos)

**Columnas:** Nombre, Email, Usuario, Rol (badge), Estado (Activo/Inactivo), Socio vinculado (ID o "—"), Acciones.

**Acciones por fila:**
- **Editar** (lápiz): modifica nombre, email, contraseña, rol
- **Activar / Desactivar** (toggle): habilita o bloquea el acceso sin eliminar la cuenta
- **Eliminar** (tacho): elimina la cuenta permanentemente (requiere confirmación)

### Crear usuario manualmente

Desde el botón **Nuevo usuario**, el administrador puede crear cuentas directamente sin pasar por el proceso de registro, asignando el rol y la contraseña inicial.

### Editar usuario

Desde la pantalla de edición se puede:
- Cambiar nombre, apellido, email
- Cambiar contraseña
- Asignar o quitar **permisos individuales** (además de los del rol)
- Dar de alta como socio si el usuario tiene rol alumno y aún no tiene socio vinculado

---

## Configuración del sistema

**Ruta:** `/admin/configuracion`

### Datos de la institución

- **Nombre de la institución**: aparece en el encabezado y en documentos generados. Los cambios se reflejan inmediatamente.
- **Logo institucional**: imagen JPG, PNG, WebP o SVG, máximo 1 MB. Se muestra en el login, el dashboard y la landing pública. El logo se almacena en `storage/app/public/logos/`.

### Parámetros de préstamos

| Parámetro | Descripción | Valor por defecto |
|-----------|-------------|-------------------|
| **Días máximos de préstamo** | Límite máximo de días que puede tener un préstamo desde la fecha actual. El bibliotecario no puede seleccionar una fecha de devolución más allá de hoy + este valor. | 14 días |
| **Días de alerta previa al vencimiento** | Ventana de días hacia adelante para considerar un préstamo como "próximo a vencer". Determina qué préstamos aparecen en el panel del dashboard y cuándo se generan las alertas automáticas. | 4 días |

**Cómo funciona:**
1. Al crear un préstamo, el bibliotecario elige `fecha_devolucion` (entre hoy y hoy + `días_máximos`).
2. El dashboard ejecuta: `WHERE fecha_devolucion BETWEEN today AND today + días_alerta`.
3. Si `fecha_devolucion` cae dentro de esa ventana → aparece en "Próximos vencimientos" y se genera una alerta.
4. El scheduler (`prestamos:marcar-atrasados`) corre diariamente a la 01:00 para marcar los vencidos.

Estos valores se aplican en tiempo real desde la base de datos. El cambio afecta a los próximos préstamos y a las notificaciones del scheduler.

---

## Contenido público

**Ruta:** `/admin/contenido`

Gestión del contenido visible en la landing pública y en el sistema.

### FAQs

Preguntas frecuentes que aparecen en la página pública `/faqs`.

- **Agregar FAQ**: ingresar pregunta y respuesta, guardar.
- **Activar / Desactivar** (toggle **Activa**): las FAQs desactivadas no se muestran al público, pero quedan guardadas.
- **Editar** (lápiz): modifica pregunta y respuesta.
- **Eliminar** (tacho): elimina permanentemente (requiere confirmación).

> Las FAQs activas se muestran en orden de creación. Se pueden reordenar editándolas.

### Footer links

Enlaces que aparecen en el pie de página del sistema y de la landing pública.

- Cada enlace tiene un **Label** (texto visible) y una **URL**.
- Los cambios se reflejan en todos los usuarios sin recargar la página (se cachean por 1 hora).

### Anuncio (banner)

Banner informativo que aparece en la barra superior del sistema para todos los usuarios autenticados.

- **Texto**: mensaje a mostrar (máximo una línea).
- **Estilo**: Info (azul), Éxito (verde), Advertencia (amarillo), Peligro (rojo).
- **Activo**: toggle para mostrar u ocultar el banner sin borrar el mensaje.

---

## Analítica

**Ruta:** `/admin/analitica`

### Indicadores globales

| Indicador | Descripción |
|-----------|-------------|
| Préstamos totales | Total histórico acumulado |
| Préstamos activos | Con estado activo o atrasado actualmente |
| Préstamos vencidos | Con estado "atrasado" en este momento |
| Materiales | Total de registros en el catálogo |
| Socios | Total de miembros registrados |

### Gráficos

**Préstamos por mes (últimos 6 meses)** — Gráfico de barras. Permite identificar estacionalidad de uso.

**Socios: activos vs dados de baja** — Gráfico de dona. Proporciones de socios según estado.

**Materiales por categoría** — Gráfico de barras horizontal. Distribución del fondo bibliográfico por área.

**Top 5 materiales más prestados** — Lista rankeada con título, autor y cantidad de préstamos. Útil para compras y descarte.

---

## Tablero de Feedback

**Ruta:** `/admin/feedback`

Tablero Kanban interno para registrar ideas, mejoras y problemas del sistema. Solo visible para administradores.

### Columnas

| Columna | Uso |
|---------|-----|
| **Backlog** | Ideas o tareas pendientes de iniciar |
| **En progreso** | Tareas en curso |
| **Completado** | Tareas finalizadas internamente |
| **Publicado** | Cambios desplegados o comunicados |

### Tarjetas

Cada tarjeta tiene:
- **Título** (obligatorio)
- **Descripción** (opcional, hasta 2000 caracteres)
- **Prioridad**: Baja (verde) / Media (amarillo) / Alta (rojo) / Urgente (rojo oscuro)
- **Tags** opcionales: Bug, Mejora, Feature, Diseño, Seguridad, UX, Documentación, Performance

### Acciones

- **Nueva tarjeta** (botón +): ingresar título, descripción, prioridad y tags opcionales.
- **Mover**: arrastrar la tarjeta a otra columna (drag & drop) o usar los botones de columna.
- **Editar** (lápiz): modifica título, descripción, prioridad y tags.
- **Eliminar** (tacho): elimina permanentemente.

---

## Cambiar institución activa

Para instalaciones con múltiples instituciones, el administrador puede cambiar la institución activa desde el menú desplegable del usuario en la barra superior (opción **Cambiar institución**).

El cambio afecta solo la sesión actual: todas las vistas, listados y reportes mostrarán los datos de la institución seleccionada.

---

## Mi Perfil

**Ruta:** `/perfil`

Disponible desde el menú desplegable del usuario (esquina superior derecha).

- Actualizar nombre, apellido y email
- Cambiar foto de perfil
- Cambiar imagen de portada (banner del dashboard)
- Cambiar contraseña

> Para cambiar la contraseña, ir a `/reset-password` o a **Mi Perfil → Cambiar contraseña**. El formulario requiere la contraseña actual. Si se olvidó la contraseña y no es posible ingresar, otra cuenta de admin puede reestablecerla desde la edición del usuario.
