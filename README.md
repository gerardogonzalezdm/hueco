# Hueco

App web SaaS multi-tenant para gestión de reservas de espacios (coworks, salas de reuniones, salas de música, despachos por horas, etc.). Cada empresa cliente crea su cuenta y opera con un panel admin + portal de usuarios.

## Stack

- **Backend**: Laravel 13 + Eloquent ORM
- **Frontend**: Inertia.js + Vue 3 + Tailwind CSS 3 (vía Laravel Breeze)
- **Base de datos**: MySQL 8 / MariaDB
- **Auth**: Breeze (login, registro, password reset, profile, dark mode)
- **Email transaccional**: Resend (en Fase 4)
- **Multi-tenancy**: pendiente de instalar (`stancl/tenancy` o `spatie/laravel-multitenancy`) en Fase 2

## Equipo

- **Gerardo** — Backend (Laravel, BD, infraestructura)
- **Alex** — Frontend (Vue, UI/UX, Inertia)
- **Juanjo** — Deploy a producción (recibe el entregable empaquetado y lo sube al cPanel del cliente)

## Requisitos en tu máquina

| Herramienta | Versión mínima | Cómo instalar |
|---|---|---|
| **PHP** | 8.2+ (ideal 8.3) | Vía MAMP, Laragon, Herd o instalación directa |
| **Composer** | 2.x | https://getcomposer.org |
| **Node.js** | 20+ (LTS) | https://nodejs.org |
| **MySQL** | 8.x (o MariaDB ≥10.3) | Incluido en MAMP |
| **Git** | 2.x | https://git-scm.com |

### Extensiones PHP requeridas

Asegúrate de que tu `php.ini` tenga habilitadas:

```
extension=curl
extension=fileinfo
extension=gd
extension=intl
extension=mbstring
extension=exif
extension=pdo_mysql
extension=sodium
extension=openssl
```

## Setup local (paso a paso)

### 1. Clonar el repo

```bash
git clone https://github.com/gerardogonzalezdm/hueco.git
cd hueco
```

### 2. Instalar dependencias

```bash
composer install
npm install
```

### 3. Configurar el `.env`

```bash
cp .env.example .env
php artisan key:generate
```

Edita `.env` y ajusta la BD a tu MAMP/MySQL local:

```env
APP_NAME=Hueco
APP_LOCALE=es
APP_FAKER_LOCALE=es_ES

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306         # en MAMP Windows. En MAMP Mac normalmente 8889
DB_DATABASE=hueco
DB_USERNAME=root
DB_PASSWORD=root     # ajusta según tu MAMP
```

### 4. Crear la base de datos

Arranca MAMP y crea la BD `hueco`:

```sql
CREATE DATABASE hueco CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

O desde phpMyAdmin de MAMP (`http://localhost/phpmyadmin`).

### 5. Migrar la BD

```bash
php artisan migrate
```

### 6. Arrancar el proyecto

En dos terminales separadas:

**Terminal 1 (backend Laravel)**:
```bash
php artisan serve
```
→ App en http://localhost:8000

**Terminal 2 (frontend Vite con hot-reload)**:
```bash
npm run dev
```

Abre http://localhost:8000 en el navegador. Regístrate y entra al dashboard.

## Estructura del proyecto

```
hueco/
├── app/
│   ├── Http/Controllers/      # Controllers (backend → aquí trabaja Gerardo)
│   └── Models/                # Modelos Eloquent
├── database/
│   ├── migrations/            # Esquema de BD versionado
│   └── seeders/               # Datos de prueba
├── resources/
│   ├── js/
│   │   ├── Pages/             # Componentes Inertia (frontend → aquí trabaja Alex)
│   │   ├── Components/        # Componentes Vue reutilizables
│   │   ├── Layouts/           # Layouts (AuthenticatedLayout, GuestLayout)
│   │   └── app.js             # Punto de entrada Vue + Inertia
│   └── css/
│       └── app.css            # Tailwind + estilos globales
└── routes/
    └── web.php                # Definición de rutas
```

## Flujo de trabajo con Git

1. **Rama por feature** (nunca commitear directo a `main`):
   ```bash
   git checkout -b feat/calendar-admin
   ```
2. Commits frecuentes con mensajes claros en imperativo:
   - `add calendar component`, `fix booking conflict bug`, `update space model`
3. Pull Request a `main` con revisión cruzada del otro dev.
4. **Sync de migrations**: cuando hagas `git pull` y vengan migrations nuevas, ejecuta:
   ```bash
   php artisan migrate
   ```

## Comandos útiles

| Comando | Qué hace |
|---|---|
| `php artisan serve` | Levanta el server PHP en :8000 |
| `npm run dev` | Vite en modo dev (hot-reload de Vue) |
| `npm run build` | Compila assets para producción |
| `php artisan migrate` | Ejecuta migrations pendientes |
| `php artisan migrate:fresh --seed` | Resetea BD y ejecuta seeders |
| `php artisan tinker` | REPL interactivo de Laravel |
| `php artisan route:list` | Lista todas las rutas |
| `php artisan make:model Booking -mc` | Crea modelo + migration + controller |

## Recursos

- [Documentación Laravel](https://laravel.com/docs)
- [Documentación Inertia](https://inertiajs.com)
- [Documentación Vue 3](https://vuejs.org/guide/)
- [Documentación Tailwind](https://tailwindcss.com/docs)
- Página del proyecto en Notion (Wolfango → Aplicacion web reservas)
