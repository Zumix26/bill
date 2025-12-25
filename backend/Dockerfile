FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock* ./

RUN if [ -f composer.lock ]; then composer install --no-dev --optimize-autoloader --no-interaction; else composer install --no-dev --optimize-autoloader --no-interaction || true; fi

COPY . .

RUN mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 8000

CMD php -S 0.0.0.0:8000 -t public public/index.php
