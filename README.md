# e-LibeR — Sistema de Gestión Bibliotecaria

Sistema web para la administración de bibliotecas escolares e institucionales: gestión de materiales, socios, préstamos, áreas temáticas y noticias.

---

## A quién va dirigido

Bibliotecarios y personal administrativo de **bibliotecas escolares, universitarias o institucionales** de pequeño y mediano porte que necesiten:

- Registrar y catalogar su acervo bibliográfico con código Dewey
- Gestionar socios (alumnos, docentes, personal)
- Controlar préstamos con seguimiento de vencimientos
- Publicar novedades internas

No requiere conocimientos técnicos para operar el sistema. La interfaz está completamente en español.

---

## Estado del proyecto

| Módulo | Estado |
|--------|--------|
| Autenticación (login/logout) | Completo |
| Dashboard con alertas de vencimientos | Completo |
| CRUD Socios + baja/alta + historial | Completo |
| CRUD Materiales + código automático + QR | Completo |
| Préstamos: crear, devolver, validaciones | Completo |
| Áreas temáticas (Dewey) | Completo |
| Noticias con imagen | Completo |
| Anotaciones internas | Completo |
| Migraciones sobre BD existente (sin pérdida) | Completo |
| Configuración Nginx + SSL para producción | Completo |
| Deploy a servidor Ubuntu | Pendiente |

---

## Stack tecnológico

| Capa | Tecnología |
|------|-----------|
| Backend | Laravel 12, PHP 8.2 |
| Frontend | Vue 3 + Vite + Inertia.js |
| CSS | Bootstrap 4.6.2 (npm) + Bootstrap Icons |
| JS | jQuery 3.7.1, Popper.js 1.16.1 |
| Base de datos | MySQL 8+ / MariaDB 10.4+ |
| Servidor web | Nginx (producción) |
| QR Codes | simplesoftwareio/simple-qrcode |
| Rutas JS | tightenco/ziggy |

La arquitectura usa **Inertia.js**: Laravel maneja routing, autenticación y queries; Vue 3 renderiza la UI. Un solo proyecto, un solo `.env`, sin API REST separada ni CORS.

---

## Requisitos

### Desarrollo local
- PHP 8.2
- Composer
- Node.js 18+
- MySQL 8+ o MariaDB 10.4+

### Producción
- Ubuntu con PHP 8.2-fpm, Nginx, MySQL
- Certbot para SSL (opcional pero recomendado)

---

## Instalación local

Estándar local del proyecto:

- PHP nativo en `PATH`
- Composer nativo en `PATH`
- Node.js/npm nativos en `PATH`
- MySQL o MariaDB nativo
- Sin XAMPP
- Sin Docker

```bash
git clone <repo> e-liber
cd e-liber

composer run setup
php artisan storage:link
```

Configurar `.env`:
```
DB_DATABASE=biblioteca
DB_USERNAME=root
DB_PASSWORD=

DEFAULT_INSTITUCION_NOMBRE="Biblioteca Escolar N° 1"
DEFAULT_INSTITUCION_SLUG=biblioteca-escolar-1
DEFAULT_ADMIN_NOMBRE="Administrador"
DEFAULT_ADMIN_USUARIO=admin
DEFAULT_ADMIN_EMAIL=admin@example.com
DEFAULT_ADMIN_PASSWORD=password
SEED_SAMPLE_DATA=false
```

`composer run setup` crea el `.env` si no existe, genera la key, corre migraciones y seeders, instala dependencias frontend y compila assets. Ya no hace falta importar `biblioteca.sql`.

Si prefieres ejecutar el proceso manualmente:
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run build
```

Levantar el proyecto:
```bash
composer run dev
```

Si necesitas probar colas localmente:

```bash
composer run dev:queue
```

Acceder en `http://localhost:8000`

Credenciales iniciales:

- Usuario: `admin`
- Password: `password`

Puedes cambiarlas antes del setup ajustando `DEFAULT_ADMIN_*` en `.env`.

Si quieres cargar datos demo para explorar la app en local, activa:

- `SEED_SAMPLE_DATA=true`

Eso agrega:

- 2 socios de ejemplo
- 3 materiales de ejemplo
- 1 prestamo activo
- 1 reserva pendiente
- 1 noticia
- 1 anotacion

---

## Deploy a producción

```bash
cd /var/www/eliber
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan config:cache && php artisan route:cache && php artisan view:cache
sudo chown -R www-data:www-data storage bootstrap/cache
sudo systemctl reload php8.2-fpm
```

El archivo `nginx/eliber.conf` contiene la configuración de vhost lista para usar. Reemplazar `eliber.tuinstitucion.edu.ar` con el dominio real.

Checklist operativo completo:

- `docs/OPERACION.md`

---

## Base de datos

La BD `biblioteca` se puede inicializar completamente con migraciones y seeders. `biblioteca.sql` ya no es parte del flujo de instalación.

Las migraciones del proyecto usan `Schema::table` cuando trabajan sobre estructuras existentes y `Schema::create` para instalaciones nuevas. El objetivo es cubrir ambos escenarios sin depender de un dump manual.

Tablas principales: `materiales`, `socios`, `prestamos`, `areas`, `bibliotecarios`, `noticias`, `anotaciones`, `historial_socios`.

Seeders base:

- `InstitucionesSeeder`: crea la institución inicial
- `AreasSeeder`: carga las áreas Dewey base
- `RolesAndPermissionsSeeder`: crea roles y permisos
- `DefaultBibliotecarioSeeder`: crea el usuario administrador inicial
- `SampleDataSeeder`: carga datos demo cuando `SEED_SAMPLE_DATA=true`

---

## Contacto

Consultas: rodrigogarciafaud@gmail.com
