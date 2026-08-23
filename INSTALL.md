# techshope — Installation Guide

There are two supported ways to run this project:

- **Option A — Docker (recommended).** One command builds everything, waits
  for the database, runs migrations and seeders, sets up file storage, and
  starts the app. Nothing else to install on your machine except Docker.
- **Option B — Native (PHP + SQLite, no Docker).** For quick local testing
  without containers. More manual steps, but no Docker required.

Pick one. Don't mix them in the same checkout.

---

## Prerequisites

### Option A — Docker

| Tool | Version | Download |
|------|---------|----------|
| Docker Desktop | Latest | https://www.docker.com/products/docker-desktop |
| Git | 2.x | https://git-scm.com |

That's it — PHP, Composer, Node, and PostgreSQL all run inside containers.
You don't need any of them installed on the host.

### Option B — Native

| Tool | Minimum Version | Recommended | Download |
|------|----------------|-------------|----------|
| PHP | 8.2 | 8.3 | https://www.php.net/downloads |
| Composer | 2.x | Latest | https://getcomposer.org/download |
| Node.js | 20.x LTS | 22.x LTS | https://nodejs.org |
| npm | 10.x | Latest | Bundled with Node.js |
| Git | 2.x | Latest | https://git-scm.com |

SQLite itself needs no separate install — PHP's bundled `pdo_sqlite` /
`sqlite3` extensions are enough (see [Required PHP Extensions](#required-php-extensions-native-only)).

---

## Option A — Docker Setup

### 1. Clone the repository

```bash
git clone https://github.com/abdelilahIbba/techshope.git techshope
cd techshope
```

### 2. Create your environment file

```bash
cp .env.docker.example .env        # Linux / macOS
copy .env.docker.example .env      # Windows CMD
Copy-Item .env.docker.example .env # Windows PowerShell
```

> If you skip this step, the app container will create `.env` from
> `.env.docker.example` automatically on first boot — but doing it yourself
> first lets you review/change DB credentials or ports before anything starts.

### 3. Build and start everything

```bash
docker compose up -d --build
```

This single command:
- Builds the PHP, Nginx, and Postgres images.
- Starts `techshope_pgsql_v2` and waits for it to become healthy.
- Starts the `app` container, which **automatically**:
  - generates `APP_KEY` if missing,
  - waits for PostgreSQL to accept connections,
  - runs `php artisan migrate --force`,
  - runs `php artisan db:seed --force` (safe to re-run — the seeders are
    idempotent, so this never duplicates data on restarts),
  - creates the `public/storage` symlink (needed for uploaded product
    images and any other public file to be reachable),
  - sets permissive permissions on `storage/` and `bootstrap/cache/`,
  - warms the config/route/view caches.
- Starts `techshope_web_v2` (Nginx) at **http://localhost:18080**.
- Starts `techshope_queue_v2` (background queue worker).

No manual `key:generate`, `migrate`, `seed`, or `storage:link` step is
needed — watch it happen with:

```bash
docker compose logs -f app
```

### 4. Build the frontend assets (required)

The backend is up, but the page will load **completely unstyled** until the
React/CSS assets exist somewhere Laravel can find them. Pick one:

**Option 1 — one-off production build (recommended for just running the app):**

```bash
docker compose run --rm techshope_vite_v2 sh -c "npm install && npm run build"
```

This writes `public/build/` once. No container needs to stay running
afterward — Nginx serves those static files directly.

**Option 2 — Vite dev server with hot-reload (only if you're actively
editing frontend code):**

```bash
docker compose --profile frontend up -d techshope_vite_v2
```

While this container runs, Laravel serves assets live from
`http://localhost:15173` instead of `public/build/` (it detects this via a
`public/hot` file that Vite creates on start).

> ⚠️ **If you stop the dev server without doing a production build first,
> the app goes back to unstyled** — `public/hot` still points at a dev
> server that's no longer there. Either restart `techshope_vite_v2`, or fix
> it permanently with `rm public/hot` followed by Option 1's build command.

### 5. Open the app

**http://localhost:18080**

Log in with one of the [default accounts](#default-login-credentials) below.

### Useful commands

```bash
docker compose logs -f app                   # app logs
docker compose logs -f techshope_pgsql_v2    # database logs
docker compose exec app php artisan test     # run the test suite
docker compose exec app php artisan tinker   # interactive shell
docker compose down                          # stop everything
docker compose down -v                       # stop and wipe the database volume too
```

To re-run migrations/seeders manually at any point (e.g. after pulling new
seed data):

```bash
docker compose exec app php artisan migrate --seed
```

---

## Option B — Native Setup (PHP + SQLite)

### 1. Clone the repository

```bash
git clone https://github.com/abdelilahIbba/techshope.git techshope
cd techshope
```

### 2. Install PHP dependencies

```bash
composer install
```

> For production: `composer install --optimize-autoloader --no-dev`

### 3. Configure the environment

```bash
cp .env.example .env        # Linux / macOS
copy .env.example .env      # Windows CMD
Copy-Item .env.example .env # Windows PowerShell
```

```bash
php artisan key:generate
```

`.env.example` already defaults to SQLite and `APP_URL=http://localhost:8000`
(matching `php artisan serve`'s default port) — no manual edits needed unless
you want to change something.

### 4. Create the SQLite database file

```bash
# Linux / macOS
touch database/database.sqlite

# Windows CMD
type nul > database\database.sqlite

# Windows PowerShell
New-Item database\database.sqlite -ItemType File
```

### 5. Run migrations and seed the database

```bash
php artisan migrate --seed
```

Creates every table (products, categories, brands, suppliers, sales, roles,
permissions, licenses, ...) and seeds default roles/permissions, four
[default user accounts](#default-login-credentials), and a sample product
catalog (4 categories × 10 products, each with a brand).

### 6. Link storage (do this before starting the server)

```bash
php artisan storage:link
```

Skipping this — or doing it after the server is already serving traffic —
means uploaded product images and any other public file will 404 until you
run it.

### 7. Install JS dependencies and build assets

```bash
npm install
npm run build       # production build
# or
npm run dev          # dev build with hot-reload (keep running in its own terminal)
```

### 8. Start the app

All-in-one (PHP server + queue worker + Vite, concurrently):

```bash
composer run dev
```

Or individually, one per terminal:

```bash
php artisan serve                       # http://localhost:8000
npm run dev                             # Vite HMR
php artisan queue:listen --tries=1      # background jobs
```

Open **http://localhost:8000** and log in with a
[default account](#default-login-credentials).

### (Optional) Production optimisations

```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```

```bash
# Linux/macOS only
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## Required PHP Extensions (native only)

Docker's PHP image already has these built in — this list only matters for
Option B. Verify with `php -m`:

```
pdo
pdo_sqlite      ; SQLite (default)
sqlite3
mbstring
openssl
tokenizer
xml
ctype
json
bcmath
fileinfo
gd              ; barcode/PDF generation
zip
```

---

## Default Login Credentials

Seeded by `database/seeders/DatabaseSeeder.php` — **change these before any
real use.**

| Email | Password | Role | Notes |
|-------|----------|------|-------|
| `admin@local.pos` | `18082001` | Admin | Full access — the **only** role that bypasses the license check, so log in here first. |
| `superuser@local.pos` | `0099` | SuperUser | RBAC management, requires an active license. |
| `commercial@local.pos` | `5678` | Editor | Requires an active license. |
| `cashier@local.pos` | `1234` | Editor | Requires an active license. |

Every role except Admin is gated behind an active license (`license.activate`
route) — if a non-admin login redirects you to a license screen, that's
expected; either activate a license there or use the Admin account.

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| `docker compose up` hangs on "Waiting for PostgreSQL..." | The `techshope_pgsql_v2` container isn't healthy yet — check `docker compose logs techshope_pgsql_v2`; give it another 10–20s on first run. |
| Edited `docker/php/entrypoint.sh` (or any Dockerfile) but nothing changed | It's baked into the image at build time — run `docker compose build app` (or `up -d --build`) to pick it up, a plain `restart` isn't enough. |
| Uploaded images / other public files 404 | `public/storage` symlink is missing — Docker creates it automatically on boot; on native, run `php artisan storage:link`. |
| Login redirects to a license screen | Expected for every role except Admin — see [Default Login Credentials](#default-login-credentials). |
| `php: command not found` (native) | Add PHP to your system `PATH`. |
| `SQLSTATE[HY000]: unable to open database file` (native) | Create `database/database.sqlite` (Option B, step 4). |
| `Class "App\Models\..." not found` | Run `composer dump-autoload`. |
| Page loads but is completely unstyled | No built assets and no dev server — run [step 4's](#4-build-the-frontend-assets-required) one-off build (Docker) or `npm run build` (native). |
| Page *was* styled, now suddenly isn't (Docker) | `public/hot` is pointing at a Vite dev server that's no longer running (you stopped `techshope_vite_v2`). Either restart it, or `rm public/hot` and re-run the production build. |
| `419 Page Expired` | Check `SESSION_DRIVER` in `.env`; make sure migrations ran (`sessions` table must exist if using the database driver). |
| Port 8000 / 18080 already in use | Native: `php artisan serve --port=8080`. Docker: change the published port in `docker-compose.yml`. |
| npm install fails on Windows | Run PowerShell as Administrator. |

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
| Database | PostgreSQL 16 (Docker, recommended) / SQLite (native) | — |
| Containers | Docker Compose (PHP-FPM, Nginx, Postgres, queue worker, Vite) | — |
