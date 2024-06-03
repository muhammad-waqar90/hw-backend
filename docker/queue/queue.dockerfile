FROM php:8.2-fpm-alpine

# Install dependencies
RUN apk add libzip-dev zip unzip

# Install extensions
RUN docker-php-ext-install pdo_mysql zip pcntl

COPY ./docker/wait-for-it-shell.sh /usr/local/bin/wait-for-it-shell
RUN chmod u+x /usr/local/bin/wait-for-it-shell
