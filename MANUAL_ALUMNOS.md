# Manual del Alumno — e-LibeR

Sistema de Gestión Bibliotecaria de tu institución educativa.

---

## Tabla de contenidos

1. [Crear una cuenta](#crear-una-cuenta)
2. [Ingresar al sistema](#ingresar-al-sistema)
3. [Dashboard (inicio)](#dashboard-inicio)
4. [Catálogo de materiales](#catálogo-de-materiales)
5. [Mis Reservas](#mis-reservas)
6. [Mis Préstamos](#mis-préstamos)
7. [Mi Perfil](#mi-perfil)
8. [Modo oscuro](#modo-oscuro)
9. [Preguntas frecuentes](#preguntas-frecuentes)

---

## Crear una cuenta

1. Ingresar a `http://[url-del-sistema]/login`
2. Hacer clic en la pestaña **"Registrarse"**
3. Completar el formulario:

| Campo | Descripción |
|-------|-------------|
| **Nombre** | Tu nombre propio |
| **Apellido** | Tu apellido |
| **Email** | Dirección de correo electrónico |
| **Usuario** | Nombre de usuario único (sin espacios, solo letras, números, puntos y guiones) |
| **Contraseña** | Mínimo 8 caracteres |
| **Confirmar contraseña** | Debe coincidir con la contraseña |
| **Tipo de cuenta** | Seleccionar **Alumno** |
| **Año** | Año escolar (1 al 6) |
| **División** | División (1 al 6) |
| **Institución** | Seleccionar tu institución de la lista |

4. Hacer clic en **Crear cuenta**

Una vez registrado, el sistema muestra el mensaje:

> *"Tu cuenta fue creada. Un bibliotecario revisará tu solicitud y te habilitará el acceso."*

**La cuenta no está activa de inmediato.** Un bibliotecario o administrador debe aprobarla. Una vez aprobada, podrás ingresar normalmente.

---

## Ingresar al sistema

1. Ingresar a `http://[url-del-sistema]/login`
2. Pestaña **"Ingresar"**
3. Escribir tu **usuario** (no el email) y tu **contraseña**
4. Opcional: marcar **"Recordarme"** para mantener la sesión activa
5. Hacer clic en **Ingresar**

El sistema te redirige automáticamente a tu panel de inicio.

### Cambiar contraseña

Una vez que ingresaste al sistema, podés cambiar tu contraseña desde `/reset-password` o desde **Mi Perfil**. El formulario requiere ingresar tu **contraseña actual** más la nueva. Si olvidaste tu contraseña y no podés ingresar, contactá al bibliotecario para que te la reestablezca.

### Cuenta pendiente de aprobación

Si al intentar ingresar aparece el mensaje *"Tu cuenta está pendiente de aprobación"*, significa que todavía no fue activada. Esperá a que un bibliotecario apruebe tu solicitud. No es necesario registrarse nuevamente.

---

## Dashboard (inicio)

**Ruta:** `/alumno/dashboard`

Al ingresar verás tu panel personal con acceso rápido a las secciones principales:

- **Catálogo de materiales** → explorar libros y recursos disponibles
- **Mis préstamos** → ver los materiales que tenés actualmente
- **Mis reservas** → ver el estado de tus solicitudes
- **Mi perfil** → editar tus datos personales

### Reservas recientes

Si tenés reservas registradas, el panel muestra una tabla con las últimas:

| Columna | Descripción |
|---------|-------------|
| Material | Título del libro o recurso |
| Estado | Estado actual de la reserva (ver abajo) |
| Fecha | Fecha en que realizaste la reserva |

El botón **Ver todas** lleva a la página completa de Mis Reservas.

### Cuenta sin socio vinculado

Si aparece el aviso *"Tu usuario todavía no está asociado a un registro de socio"*, significa que tu cuenta fue aprobada pero el bibliotecario aún no completó tu alta como socio. En ese estado podés explorar el catálogo, pero no realizar reservas. Contactá al bibliotecario para que complete el proceso.

---

## Catálogo de materiales

**Ruta:** `/alumno/catalogo`

Aquí podés explorar todos los materiales disponibles en la biblioteca de tu institución.

### Buscar materiales

- **Campo de búsqueda**: escribí el título o el nombre del autor
- **Filtro por categoría**: desplegable para filtrar por área temática (Filosofía, Historia, Ciencias, etc.)
- Presionar **Buscar** para ver los resultados
- Presionar **Limpiar** para quitar todos los filtros

El encabezado muestra cuántos materiales hay en total (ej: *"45 materiales"*).

### Tarjetas de materiales

Cada material muestra:
- Título y autor
- Área temática (badge de color)
- Año de publicación
- Categoría (Libro, Revista, Atlas, etc.)
- **Disponibilidad**: cantidad de ejemplares en estante (badge verde, ej: *"3 disp."*)

### Reservar un material

Si hay ejemplares disponibles y tenés tu cuenta de socio activa, aparece el botón **Reservar** en la tarjeta. Al hacer clic:

1. Se reserva automáticamente un ejemplar para vos
2. El estado queda como **Pendiente** hasta que el bibliotecario lo apruebe
3. Cuando sea aprobado, recibís una notificación y se genera tu préstamo
4. La reserva tiene una vigencia de 2 días — si no es procesada antes, expira automáticamente

> Si el botón no aparece y ves *"Sin socio vinculado"*, necesitás que el bibliotecario complete tu alta como socio.

### Paginación

Si hay muchos materiales, podés navegar entre páginas con los botones **Anterior** y **Siguiente**. El indicador muestra en qué página estás (ej: *"2 / 5"*).

---

## Mis Reservas

**Ruta:** `/alumno/mis-reservas`

Lista de todas tus reservas con su estado actual.

### Estados de una reserva

| Estado | Badge | Significado |
|--------|-------|-------------|
| **Pendiente** | Amarillo | Solicitada, esperando que el bibliotecario la apruebe |
| **Aprobada** | Verde | El bibliotecario la aprobó y generó el préstamo |
| **Rechazada** | Rojo | El bibliotecario no pudo aprobarla (el material ya no estaba disponible, etc.) |
| **Expirada** | Gris | Pasaron más de 2 días sin ser procesada |

### Cancelar una reserva

Si la reserva está en estado **Pendiente** o **Aprobada**, aparece el botón **Cancelar** (rojo) a la derecha. Al hacer clic:
- El sistema pide confirmación *"¿Cancelar esta reserva?"*
- Al confirmar, el ejemplar queda libre para otro alumno

No se pueden cancelar reservas **rechazadas** ni **expiradas** ya que no tienen efecto sobre el stock.

### Paginación

Si tenés muchas reservas, podés navegar entre páginas con los botones **Anterior** y **Siguiente**.

---

## Mis Préstamos

**Ruta:** `/alumno/mis-prestamos`

Historial completo de tus préstamos activos y pasados.

### Préstamos activos

Tabla con los materiales que tenés actualmente:

| Columna | Descripción |
|---------|-------------|
| Material | Título del libro |
| Estado | **Activo** (azul) o **Atrasado** (rojo) |
| Inicio | Fecha en que empezó el préstamo |
| Vencimiento | Fecha en que debés devolver el material |

Si un préstamo aparece como **Atrasado** en rojo, ya pasó la fecha de devolución. Llevá el material a la biblioteca lo antes posible para regularizar.

### Historial

Préstamos ya devueltos, paginados. Muestra los mismos datos más la fecha de devolución efectiva.

---

## Mi Perfil

**Ruta:** `/perfil` · Acceso desde el menú desplegable del usuario (esquina superior derecha, hacé clic en tu nombre o foto)

Desde aquí podés actualizar tus datos personales:

- **Email**: dirección de correo electrónico
- **Apellido**: corregir si fue ingresado con error
- **Año y División**: si cambiaste de año escolar
- **Foto de perfil**: subir una imagen para personalizar tu cuenta
- **Imagen de portada**: banner decorativo que aparece en tu dashboard
- **Contraseña**: cambiarla si querés mayor seguridad

Hacer clic en **Guardar** para aplicar los cambios.

---

## Modo oscuro

En la barra superior, el ícono de sol/luna alterna entre el modo claro y oscuro. La preferencia queda guardada en tu navegador.

---

## Preguntas frecuentes

**¿Por qué no puedo reservar materiales?**
Hay dos razones posibles:
1. Tu cuenta todavía no está vinculada a un registro de socio — el bibliotecario debe completar tu alta.
2. No hay ejemplares disponibles del material que te interesa (la disponibilidad aparece en "0 disp.").

**¿Cuántas reservas puedo tener activas al mismo tiempo?**
Solo se permite una reserva por material. No podés reservar el mismo libro dos veces simultáneamente.

**¿Cuánto tiempo tengo para retirar un material reservado?**
La reserva vence automáticamente a los 2 días de haber sido solicitada si el bibliotecario no la procesa. Si fue aprobada y convertida en préstamo, ya tenés el plazo completo del préstamo.

**¿Cómo sé si mi reserva fue aprobada?**
Podés verificarlo en **Mis Reservas** — el estado cambia a "Aprobada" (badge verde). Además, el sistema envía una notificación al usuario cuando la reserva es procesada.

**¿Qué pasa si devuelvo tarde un material?**
El sistema puede generar una multa automáticamente por los días de retraso. El monto lo establece la institución. Consultá con el bibliotecario para regularizar la situación.

**¿Puedo ver materiales sin tener cuenta?**
Sí, la ficha pública de cada material está disponible sin login (por ejemplo, desde un código QR pegado en el libro). Sin embargo, para reservar necesitás tener una cuenta activa y vinculada.

**¿Cómo cancelo mi cuenta?**
Contactá al bibliotecario de tu institución para dar de baja tu cuenta de usuario.
