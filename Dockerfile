# Immagine di produzione (Render): asset compilati con Node, poi PHP 8.4 + Apache.

# 1) asset front-end con Vite (richiede Node >= 20.19)
FROM node:22-bookworm-slim AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js ./
COPY resources ./resources
# local packages imported by the front end (alias in vite.config.js), e.g. svg-avatar
COPY packages ./packages
RUN npm run build

# 2) applicazione Laravel 13 (PHP >= 8.3)
FROM php:8.4-apache

RUN apt-get update \
    && apt-get install -y git unzip libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && rm -rf /var/lib/apt/lists/*

# Apache serve public/ (prima la DocumentRoot era la radice del progetto) con mod_rewrite per index.php
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && a2enmod rewrite

WORKDIR /var/www/html
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer install --optimize-autoloader --no-dev --no-interaction \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80
CMD ["apache2-foreground"]
