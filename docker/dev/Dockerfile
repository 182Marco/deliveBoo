# Immagine PHP 8.4 per lo sviluppo locale (docker-compose.yml, servizio "app").
# Laravel 13 richiede PHP >= 8.3. Non è quella di produzione: quella è il Dockerfile nella radice.
FROM php:8.4-cli

RUN apt-get update \
    && apt-get install -y git unzip zip libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
