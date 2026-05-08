# e-LibeR — Sistema de Gestión Bibliotecaria

Laravel 12 + Vue 3 + Inertia.js. Gestión de materiales, socios, préstamos y reservas para instituciones educativas pequeñas y medianas.

---

## Requisitos previos

| Herramienta | Versión mínima |
|-------------|---------------|
| PHP | 8.2 |
| Composer | 2.x |
| Node.js | 18 LTS |
| MySQL / MariaDB | 8.0 / 10.6 |
| Git | cualquiera |

> **XAMPP** ya incluye PHP, MySQL y Apache. Verificá que estén activas las extensiones `pdo_mysql`, `mbstring`, `openssl`, `fileinfo` y `zip` en `php.ini`.

---

## Instalación en desarrollo

### 1. Clonar el repositorio

```bash
git clone <url-del-repositorio> eliber-laravel
cd eliber-laravel
```

### 2. Crear la base de datos

```sql
CREATE DATABASE biblioteca CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Configurar el entorno

> **Importante:** realizá este paso **antes** de ejecutar cualquier comando `php artisan`. Si corrés artisan sin `.env`, Laravel cachea configuración vacía. Si ya ejecutaste algo antes de copiar el `.env`, corré `php artisan config:clear` para limpiar.

```bash
cp .env.example .env
```

Editá `.env` con los datos de tu entorno:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=biblioteca
DB_USERNAME=root
DB_PASSWORD=           # tu contraseña de MySQL

# Institución y usuario admin iniciales
DEFAULT_INSTITUCION_NOMBRE="Mi Escuela"
DEFAULT_INSTITUCION_SLUG=mi-escuela
DEFAULT_ADMIN_USUARIO=admin
DEFAULT_ADMIN_EMAIL=admin@ejemplo.com
DEFAULT_ADMIN_PASSWORD=CambiarEsta123
```

### 4. Instalar todo con un solo comando

```bash
composer run setup
```

Hace en orden: dependencias PHP → copia `.env` si no existe → genera `APP_KEY` → migra BD con seeders → dependencias Node → compila assets.

### 5. Enlazar el storage público

```bash
php artisan storage:link
```

### 6. Levantar el servidor de desarrollo

```bash
composer run dev
```

Levanta en paralelo:
- **PHP**: `http://localhost:8000`
- **Vite** (HMR): recarga automática al guardar archivos Vue/JS/CSS

---

## Credenciales iniciales

| Campo | Valor |
|-------|-------|
| Usuario | `admin` (o el valor de `DEFAULT_ADMIN_USUARIO`) |
| Contraseña | `password` (o el valor de `DEFAULT_ADMIN_PASSWORD`) |
| Rol | Administrador |

> Cambiá la contraseña desde **Mi perfil** luego del primer ingreso.

---

## Datos de prueba (opcional)

Para poblar la BD con socios, materiales y préstamos de ejemplo:

1. Activar en `.env`:
```env
SEED_SAMPLE_DATA=true
```

2. Recrear la base de datos:
```bash
php artisan migrate:fresh --seed
```

> Esto **borra y recrea** toda la base de datos.

---

## Comandos de uso frecuente

```bash
# Servidor + queue worker + Vite (para funciones con colas)
composer run dev:queue

# Ejecutar todos los tests
composer run test

# Correr un test específico
php artisan test --filter test_authenticated_bibliotecario_can_create_a_socio

# Formatear código PHP (Laravel Pint)
php artisan pint

# Limpiar caché de configuración/rutas
php artisan optimize:clear

# Compilar assets para producción
npm run build
```

---

## Flujo principal del sistema

```
1. Alumno se registra en /login (pestaña "Registrarse")
         ↓
2. Bibliotecario aprueba en /usuarios → Socio creado automáticamente
         ↓
3. Bibliotecario completa datos del socio en /socios (apellido, año, división)
         ↓
4. Bibliotecario registra préstamo desde /prestamos/create (Terminal de Préstamos)
         o desde el panel del socio en /socios
         ↓
5. Seguimiento, prórrogas y devoluciones desde /prestamos
```

---

## Roles del sistema

| Rol | Acceso |
|-----|--------|
| `admin` | Panel de administración, instituciones, usuarios, configuración, feedback, contenido |
| `bibliotecario` | Socios, materiales, préstamos, áreas, noticias, alertas |
| `alumno` | Catálogo público, mis reservas, perfil |

### Aprobación de cuentas

Las cuentas nuevas se crean con `activo = false` y requieren aprobación:
- **Alumnos** → aprobados por el bibliotecario en `/usuarios`
- **Bibliotecarios** → aprobados por el admin en `/admin/usuarios`

---

## Funcionalidades destacadas

### Gestión de materiales
- Clasificación Dewey por áreas con código generado automáticamente (`{dewey}-{seq:3}`).
- Ubicación física: pasillo, tipo (estante/mueble), número y nivel.
- Código QR por material con datos completos incluyendo tipo de préstamo.
- Tipos de préstamo: Solo consulta, Copia única, Transitorio.

### Terminal de Préstamos (`/prestamos/create`)
- Búsqueda predictiva de socio y material con debounce 300ms.
- Validaciones: socio activo, menos de 3 préstamos activos, sin duplicados, fecha máx. 14 días.
- Alertas de vencimientos próximos en el dashboard con enlace de WhatsApp.

### Reservas (API REST)
- Alumnos hacen reservas desde el catálogo público vía API Sanctum.
- Bibliotecario aprueba/rechaza desde el panel — la aprobación crea el préstamo automáticamente.

### Sistema de Feedback (`/admin/feedback`)
- Kanban 4 columnas: Backlog / En progreso / Completado / Publicado.
- Cualquier usuario autenticado puede dejar una nota desde el footer ("Dejar una nota").
- Rate limiting: 1 nota cada 5 segundos, máximo 3 por día por usuario.
- Las notas aparecen automáticamente en la columna Backlog con la institución del autor.

### Configuración institucional (`/admin/configuracion`)
- Logo, colores, nombre y parámetros por institución.
- El logo se muestra centrado en los dashboards de bibliotecario y alumno.
- Aplica según la institución seleccionada en el dropdown del panel admin.

### Multi-tenant
- Cada entidad tiene `institucion_id`. Todos los queries están scopeados.
- Helper `tenantId()`: retorna `session('admin_institucion_id')` para admin, sino `auth()->user()->institucion_id`.

---

## Notas para XAMPP en Windows

- Asegurate de que los servicios **Apache** y **MySQL** estén corriendo en el panel de XAMPP.
- Colocá el proyecto en `C:\xampp\htdocs\eliber-laravel\`.
- Usá Git Bash o PowerShell para correr comandos de Composer y Artisan.
- Si `php` no se reconoce en la terminal, agregá `C:\xampp\php` al PATH del sistema.

---

## Despliegue en producción

Ver la sección **Production Deployment** en `CLAUDE.md` y el archivo `nginx/eliber.conf` como plantilla de vhost Nginx.
