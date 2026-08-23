#!/usr/bin/env sh
set -e

cd /var/www/html

# This entrypoint only ever runs inside the PHP container, so we're always in
# the Docker/Postgres context here — prefer the Docker-specific env template
# (correct APP_URL port, pgsql connection) over the generic SQLite-oriented
# .env.example, so a fresh clone works without a manual `cp` step first.
if [ ! -f .env ]; then
    if [ -f .env.docker.example ]; then
        cp .env.docker.example .env
        echo "Created .env from .env.docker.example"
    elif [ -f .env.example ]; then
        cp .env.example .env
        echo "Created .env from .env.example (no .env.docker.example found)"
    fi
fi

if [ ! -d vendor ]; then
    composer install --no-interaction --prefer-dist
fi

if [ -f .env ] && grep -q '^APP_KEY=$' .env; then
    php artisan key:generate --force
fi

if [ "${DB_CONNECTION}" = "pgsql" ]; then
    echo "Waiting for PostgreSQL ${DB_HOST}:${DB_PORT}..."
    until timeout 5 php -r "try { new PDO('pgsql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); } catch (Throwable $e) { exit(1); }" 2>/dev/null; do
        sleep 2
    done
fi

if [ "${RUN_MIGRATIONS}" = "true" ]; then
    timeout 120 php artisan migrate --force || echo "Migration timeout or error - continuing anyway"

    if [ "${RUN_SEEDERS:-true}" = "true" ]; then
        # DatabaseSeeder is fully idempotent (updateOrCreate/firstOrCreate
        # throughout), so re-running it on every container start is safe —
        # it applies newly-added seed data after a pull without a manual step.
        timeout 120 php artisan db:seed --force || echo "Seeding timeout or error - continuing anyway"
    fi
fi

mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs storage/app/public bootstrap/cache
chmod -R 777 storage bootstrap/cache 2>/dev/null || true

# public/storage must point at storage/app/public or every uploaded/served
# file (product images, etc.) 404s. Only (re)create it when missing so we
# don't spam "link already exists" on every restart.
if [ ! -L public/storage ]; then
    php artisan storage:link >/dev/null 2>&1 || true
fi

# Cache Laravel bootstrap artefacts so they are read from bootstrap/cache/
# (one file, warmed by OPcache) instead of parsing every config / route / view
# from the slow bind-mounted filesystem on each request.
php artisan config:cache  >/dev/null 2>&1 || true
php artisan route:cache   >/dev/null 2>&1 || true
php artisan view:cache    >/dev/null 2>&1 || true

exec "$@"
