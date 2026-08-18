FROM node:22-alpine

# Wayfinder's Vite plugin shells out to `php artisan wayfinder:generate`
# during dev-server startup and builds, so the Node image needs a PHP CLI
# binary with the extensions Laravel's bootstrap requires.
RUN apk add --no-cache \
    php83 \
    php83-tokenizer \
    php83-mbstring \
    php83-xml \
    php83-ctype \
    php83-pdo \
    php83-pdo_pgsql \
    php83-phar \
    php83-openssl \
    php83-dom \
    php83-fileinfo \
    php83-session \
    && ln -sf /usr/bin/php83 /usr/bin/php

WORKDIR /var/www/html
