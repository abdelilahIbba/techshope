#!/usr/bin/env sh
set -e

cd /var/www/html

if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi

if [ ! -d vendor ]; then
    composer install --no-interaction --prefer-dist
fi

if [ -f .env ] && grep -q '^APP_KEY=$' .env; then
    php artisan key:generate --force
fi

if [ "${DB_CONNECTION}" = "pgsql" ]; then
    echo "Waiting for PostgreSQL ${DB_HOST}:${DB_PORT}..."
    until php -r "try { new PDO('pgsql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); } catch (Throwable $e) { exit(1); }"; do
        sleep 2
    done
fi

if [ "${RUN_MIGRATIONS}" = "true" ]; then
    php artisan migrate --force
fi

php artisan config:clear >/dev/null 2>&1 || true

exec "$@"
