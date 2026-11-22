# Immagine PHP 8.0 per lo sviluppo locale (docker-compose.yml, servizio "app").
# Non è quella di produzione: quella è il Dockerfile nella radice (Apache + PHP 8.2).
FROM php:8.0-cli

# Debian bullseye è archiviata: i mirror normali non rispondono più.
RUN printf '%s\n' \
        'deb http://archive.debian.org/debian bullseye main' \
        'deb http://archive.debian.org/debian bullseye-updates main' \
        > /etc/apt/sources.list \
    && printf 'Acquire::Check-Valid-Until "false";\n' > /etc/apt/apt.conf.d/99no-check-valid-until \
    && apt-get update \
    && apt-get install -y git unzip zip libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
