#!/usr/bin/env sh
set -e

cd /var/www/html

if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi

if [ ! -f vendor/autoload.php ]; then
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
fi

mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chmod -R 777 storage bootstrap/cache 2>/dev/null || true

# Cache Laravel bootstrap artefacts so they are read from bootstrap/cache/
# (one file, warmed by OPcache) instead of parsing every config / route / view
# from the slow bind-mounted filesystem on each request.
php artisan config:cache  >/dev/null 2>&1 || true
php artisan route:cache   >/dev/null 2>&1 || true
php artisan view:cache    >/dev/null 2>&1 || true

exec "$@"
