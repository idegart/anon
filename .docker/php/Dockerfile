FROM php:fpm-alpine

WORKDIR /var/www/

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY ./ /var/www/

RUN composer install

RUN docker-php-ext-install pdo pdo_mysql
RUN docker-php-ext-enable pdo_mysql
