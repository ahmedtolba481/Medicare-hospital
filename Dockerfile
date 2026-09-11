FROM node:22-alpine AS frontend

WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY resources ./resources
COPY vite.config.js .
RUN npm run build

FROM php:8.4-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
ENV PORT=10000

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libicu-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-install bcmath intl pdo_mysql zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --optimize-autoloader --no-scripts

COPY . .
COPY --from=frontend /app/public/build ./public/build
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint

RUN sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" \
        /etc/apache2/sites-available/*.conf \
        /etc/apache2/apache2.conf \
        /etc/apache2/conf-available/*.conf \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 10000

ENTRYPOINT ["docker-entrypoint"]
CMD ["apache2-foreground"]
