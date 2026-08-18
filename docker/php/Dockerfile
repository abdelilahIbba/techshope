FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    bash \
    curl \
    git \
    icu-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    postgresql-dev \
    unzip \
    $PHPIZE_DEPS

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        exif \
        gd \
        intl \
        pcntl \
        pdo_pgsql \
        pgsql \
        zip \
    && docker-php-ext-enable opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-techshope-v2.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/php/entrypoint.sh /usr/local/bin/techshope-v2-entrypoint
RUN chmod +x /usr/local/bin/techshope-v2-entrypoint

# Bake composer.json/lock and run install at build time so the `vendor/`
# volume is pre-seeded with a warm autoloader instead of being empty and
# forcing a slow `composer install` over the Windows bind mount on first run.
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-scripts --prefer-dist --optimize-autoloader

ENTRYPOINT ["techshope-v2-entrypoint"]
CMD ["php-fpm"]
