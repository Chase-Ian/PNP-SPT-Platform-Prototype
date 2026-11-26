# Stage 1: Install PHP dependencies
FROM composer:2 AS composer-build

WORKDIR /app

COPY composer.json composer.lock ./

# GD is only needed when the app runs, and Stage 3 installs it. This stage
# used to compile GD with apk just to satisfy Composer's platform check, which
# broke when Alpine's python3 packages clashed in the composer image
# (python3-ndbm vs python3). Skipping that one check removes the apk step.
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts --ignore-platform-req=ext-gd

COPY . .
RUN composer dump-autoload --optimize

# Stage 2: Build frontend assets
FROM node:20-alpine AS node-build

WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .
COPY --from=composer-build /app/vendor ./vendor
RUN npm run build

# Stage 3: PHP app runtime
FROM php:8.2.35-fpm-alpine
RUN php -v

RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    ca-certificates \
    zip \
    unzip \
    git \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    && update-ca-certificates \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache

# PHP defaults (2 MB upload / 8 MB POST) are too small for image uploads (5 MB) and PPTX import (20 MB)
RUN printf "upload_max_filesize=50M\npost_max_size=55M\n" > /usr/local/etc/php/conf.d/uploads.ini

# Download the standalone ISRG Root X1 CA certificate for TiDB Cloud
RUN mkdir -p /etc/ssl/certs/tidb \
    && curl -fsSL https://letsencrypt.org/certs/isrgrootx1.pem -o /etc/ssl/certs/tidb/isrgrootx1.pem

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

# Copy vendor dependencies from Stage 1
COPY --from=composer-build /app/vendor ./vendor

# Copy compiled assets from Stage 2
COPY --from=node-build /app/public/build ./public/build

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache \
    && chmod -R 755 /var/www/html/public

COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80

CMD ["/start.sh"]