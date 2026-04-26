# e-LibeR — Sistema de Gestión Bibliotecaria

Laravel 12 + Vue 3 + Inertia.js. Gestión de materiales, socios, préstamos y reservas para instituciones educativas.

---

## Requisitos previos

| Herramienta | Versión mínima | Descarga |
|-------------|---------------|---------|
| PHP | 8.2 | https://www.php.net/downloads |
| Composer | 2.x | https://getcomposer.org |
| Node.js | 18 LTS | https://nodejs.org |
| MySQL / MariaDB | 8.0 / 10.6 | https://dev.mysql.com/downloads |
| Git | cualquiera | https://git-scm.com |

> **XAMPP** ya incluye PHP, MySQL y Apache. Si lo usás, verificá que estén activas las extensiones `pdo_mysql`, `mbstring`, `openssl`, `fileinfo` y `zip` en `php.ini`.

---

## Instalación en desarrollo

### 1. Clonar el repositorio

```bash
git clone <url-del-repositorio> eliber-laravel
cd eliber-laravel
```

### 2. Crear la base de datos

En MySQL/MariaDB (phpMyAdmin, DBeaver o consola):

```sql
CREATE DATABASE biblioteca CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Configurar el entorno

> **⚠ Importante:** hacé este paso **antes** de ejecutar cualquier comando `php artisan`. Si corrés artisan sin `.env`, Laravel cachea la configuración con valores vacíos y después aparecen errores como `APP_URL: undefined` o fallas de base de datos. Si ya ejecutaste algo antes de copiar el `.env`, corré `php artisan config:clear` para limpiar la caché.

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

# Institución y usuario admin que se crean al hacer el seed
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

Este comando hace en orden: instala dependencias PHP → copia `.env` si no existe → genera `APP_KEY` → migra la base de datos con seeders → instala dependencias Node → compila los assets.

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

> ⚠ Esto **borra y recrea** toda la base de datos.

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

# Limpiar caché de configuración/rutas (si algo falla)
php artisan optimize:clear

# Compilar assets para producción
npm run build
```

---

## Flujo principal del sistema

```
1. Alumno se registra en /login (pestaña "Registrarse")
         ↓
2. Bibliotecario aprueba en /usuarios  →  Socio creado automáticamente
         ↓
3. Bibliotecario completa datos en /socios (apellido, año, división)
         ↓
4. Bibliotecario registra préstamo desde /socios (botón "Nuevo préstamo")
         o desde /prestamos/create  (Terminal de Préstamos)
         ↓
5. Seguimiento, prórrogas y deudas desde el panel expandible en /socios
```

---

## Roles del sistema

| Rol | Acceso |
|-----|--------|
| `admin` | Panel de administración, usuarios, configuración, analítica, contenido |
| `bibliotecario` | Socios, materiales, préstamos, áreas, noticias, alertas |
| `alumno` | Catálogo público, mis reservas, perfil |

---

## Notas para XAMPP en Windows

- Asegurate de que los servicios **Apache** y **MySQL** estén corriendo en el panel de XAMPP.
- Colocá el proyecto en `C:\xampp\htdocs\eliber-laravel\`.
- Usá la terminal de Git Bash o PowerShell para correr los comandos de Composer y Artisan.
- Si `php` no se reconoce en la terminal, agregá `C:\xampp\php` al PATH del sistema.

---

## Despliegue en producción

Ver la sección **Production Deployment** en `CLAUDE.md` y el archivo `nginx/eliber.conf` como plantilla de vhost Nginx.
