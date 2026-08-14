# Droguerie — Installation Guide

## Prerequisites

Make sure the following software is installed before proceeding.

| Tool | Minimum Version | Recommended | Download |
|------|----------------|-------------|----------|
| PHP | 8.2 | 8.3 | https://www.php.net/downloads |
| Composer | 2.x | Latest | https://getcomposer.org/download |
| Node.js | 20.x LTS | 22.x LTS | https://nodejs.org |
| npm | 10.x | Latest | Bundled with Node.js |
| SQLite | 3.x | Latest | Bundled with PHP |
| MySQL *(optional)* | 8.0 | 8.4 | https://dev.mysql.com/downloads |
| Git | 2.x | Latest | https://git-scm.com |

> **Note:** The project uses **SQLite by default**, so no database server is required for a local setup.

---

## Docker + PostgreSQL Setup (Recommended for this project)

Use this flow if you want the full environment in containers.

### A. Prepare environment file

```bash
cp .env.docker.example .env        # Linux / macOS
copy .env.docker.example .env      # Windows CMD
```

Generate app key once:

```bash
docker compose run --rm techshope_app_v2 php artisan key:generate
```

### B. Build and start containers

```bash
docker compose up -d --build
```

This starts:
- `techshope_web_v2` (Nginx) at `http://localhost:18080`
- `techshope_app_v2` (PHP-FPM Laravel runtime)
- `techshope_queue_v2` (Laravel queue worker)
- `techshope_pgsql_v2` (PostgreSQL 16)

### C. Run migrations and seeders

```bash
docker compose exec techshope_app_v2 php artisan migrate --seed
```

### D. Optional: run Vite in container

```bash
docker compose --profile frontend up -d techshope_vite_v2
```

Vite will be available at `http://localhost:15173`.

### E. Useful commands

```bash
docker compose logs -f techshope_app_v2
docker compose logs -f techshope_pgsql_v2
docker compose exec techshope_app_v2 php artisan test
docker compose down
```

If you want to remove database data too:

```bash
docker compose down -v
```

---

## Required PHP Extensions

Verify these PHP extensions are enabled in your `php.ini`:

```
extension=pdo
extension=pdo_sqlite      ; or pdo_mysql if using MySQL
extension=sqlite3         ; or remove if using MySQL
extension=mbstring
extension=openssl
extension=tokenizer
extension=xml
extension=ctype
extension=json
extension=bcmath
extension=fileinfo
extension=gd              ; for barcode/PDF generation
extension=zip
```

Check your enabled extensions with:
```bash
php -m
```

---

## 1. Clone the Repository

```bash
git clone https://github.com/Twamof/-droguerie_s.git droguerie
cd droguerie
```

---

## 2. Install PHP Dependencies

```bash
composer install
```

> For production, use:
> ```bash
> composer install --optimize-autoloader --no-dev
> ```

---

## 3. Configure the Environment

Copy the example environment file:

```bash
cp .env.example .env        # Linux / macOS
copy .env.example .env      # Windows CMD
```

Generate the application key:

```bash
php artisan key:generate
```

Open `.env` and adjust the values below at minimum:

```dotenv
APP_NAME="Droguerie"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:18080

# --- Database (SQLite — default, no server needed) ---
DB_CONNECTION=sqlite
# The SQLite file is created automatically at database/database.sqlite

# --- Database (MySQL — optional alternative) ---
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=droguerie
# DB_USERNAME=root
# DB_PASSWORD=

# --- Mail (log driver = emails go to storage/logs — good for local dev) ---
MAIL_MAILER=log
```

---

## 4. Create the SQLite Database File *(SQLite only)*

```bash
# Linux / macOS
touch database/database.sqlite

# Windows CMD
type nul > database\database.sqlite

# Windows PowerShell
New-Item database\database.sqlite -ItemType File
```

---

## 5. Run Migrations & Seed the Database

```bash
php artisan migrate --seed
```

This will:
- Create all tables (users, categories, products, customers, suppliers, sales, purchases, receipts, audit logs, settings, licenses, roles, permissions…)
- Seed default roles, permissions, an admin user, and sample data

> **Default admin credentials** (set by the seeder):
> - **Email:** `admin@droguerie.com`
> - **Password:** `password`
>
> Change these immediately after first login.

---

## 6. Install JavaScript Dependencies

```bash
npm install
```

---

## 7. Build Frontend Assets

**Development (with hot-reload):**
```bash
npm run dev
```

**Production (optimised build):**
```bash
npm run build
```

---

## 8. Start the Development Server

You can start all services with one command:

```bash
composer run dev
```

This runs concurrently:
- PHP built-in server on `http://localhost:8000`
- Laravel queue worker
- Vite dev server (HMR)

Or start them individually:

```bash
# Terminal 1 — PHP server
php artisan serve

# Terminal 2 — Vite
npm run dev

# Terminal 3 — Queue worker (required for background jobs)
php artisan queue:listen --tries=1
```

Open your browser at: **http://localhost:8000**

---

## 9. Storage Link *(for file uploads)*

```bash
php artisan storage:link
```

---

## 10. (Optional) Production Optimisations

Run these extra steps when deploying to a production server:

```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```

Set the correct permissions on Linux/macOS:
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## Quick Reference — All Steps in One Go

```bash
git clone https://github.com/Twamof/-droguerie_s.git droguerie
cd droguerie
composer install
cp .env.example .env
php artisan key:generate
# SQLite: create the file first
New-Item database\database.sqlite -ItemType File   # PowerShell
php artisan migrate --seed
npm install
npm run build
php artisan storage:link
composer run dev
```

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| `php: command not found` | Add PHP to your system `PATH` |
| `SQLSTATE[HY000]: unable to open database file` | Create `database/database.sqlite` (Step 4) |
| `Class "App\Models\..." not found` | Run `composer dump-autoload` |
| `Vite manifest not found` | Run `npm run build` or start `npm run dev` |
| `419 Page Expired` | Check `SESSION_DRIVER` in `.env`; run `php artisan migrate` |
| Port 8000 already in use | Use `php artisan serve --port=8080` |
| npm install fails on Windows | Run PowerShell as Administrator |

---

## Tech Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| Backend | PHP / Laravel | 8.3 / 12.x |
| Frontend | React + TypeScript | 19.x / 5.7 |
| Routing (SPA) | Inertia.js | 2.x |
| Styling | Tailwind CSS | 4.x |
| Build tool | Vite | 7.x |
| UI components | Radix UI + shadcn/ui | latest |
| Authentication | Laravel Fortify | latest |
| PDF / Barcodes | DomPDF + picqer/barcode | latest |
| Database | SQLite (dev) / MySQL 8 (prod) | — |
