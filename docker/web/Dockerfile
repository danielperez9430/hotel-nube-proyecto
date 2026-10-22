# Imagen de la web de Hotel Nube: PHP 8.4 + Apache sirviendo public/
FROM composer:2.10.3 AS composer

FROM php:8.4.27-apache

# Extensión de MySQL/MariaDB y unzip para Composer
RUN apt-get update \
    && apt-get install -y --no-install-recommends unzip \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo_mysql \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Solo public/ es accesible desde el navegador
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Primero las dependencias, para aprovechar la caché de capas
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --optimize-autoloader

COPY . .

# Aplica las migraciones pendientes antes de arrancar Apache, también sobre
# una base de datos que ya existía
CMD ["sh", "-c", "php scripts/migrar.php && exec apache2-foreground"]
