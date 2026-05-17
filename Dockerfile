FROM php:8.2-apache

RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

RUN a2enmod rewrite

RUN echo '<FilesMatch "^\\.">' \
    '\nRequire all denied' \
    '\n</FilesMatch>' \
    > /etc/apache2/conf-available/security.conf \
    && a2enconf security

WORKDIR /var/www/html

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html