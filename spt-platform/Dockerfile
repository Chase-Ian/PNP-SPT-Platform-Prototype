# Stage 1: Install PHP dependencies
FROM composer:2 AS composer-build

WORKDIR /app

# Install system dependencies & compile GD extension for Composer's platform check
RUN apk add --no-cache \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

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
FROM php:8.2-fpm-alpine

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
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80

CMD ["/start.sh"]