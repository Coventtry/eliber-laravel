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

```bash
git clone <repo> e-liber
cd e-liber

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Configurar `.env`:
```
DB_DATABASE=biblioteca
DB_USERNAME=root
DB_PASSWORD=
```

Importar la base de datos existente:
```bash
mysql -u root biblioteca < SQL/biblioteca.sql
```

Finalizar setup:
```bash
php artisan migrate
php artisan storage:link
```

Levantar el proyecto (dos terminales):
```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev
```

Acceder en `http://localhost:8000`

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

---

## Base de datos

La BD `biblioteca` contiene 12 tablas. Las migraciones del proyecto usan `Schema::table` — **no destruyen datos existentes**. Son seguras de correr sobre una instalación con datos en producción.

Tablas principales: `materiales`, `socios`, `prestamos`, `areas`, `bibliotecarios`, `noticias`, `anotaciones`, `historial_socios`.

---

## Contacto

Consultas: rodrigogarciafaud@gmail.com
