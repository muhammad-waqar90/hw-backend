FROM php:8.2-fpm

## Copy deps / composer.lock and composer.json
# COPY composer.lock composer.json package.json /var/www/

## Set working directory
WORKDIR /var/www/html

## Install dependencies
RUN apt-get update && apt-get install -y \
  build-essential \
  libzip-dev\
  libpng-dev \
  libjpeg62-turbo-dev \
  libfreetype6-dev \
  locales \
  zip \
  jpegoptim optipng pngquant gifsicle \
  vim \
  unzip \
  git \
  curl \
  gnupg

## Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

## Install extensions
RUN docker-php-ext-install pdo_mysql zip exif pcntl
RUN docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/
RUN docker-php-ext-install gd

## Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

## Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

## Add user for laravel application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

## Create build directory
RUN mkdir /build
WORKDIR /build

## Give permissions to www for public folder
# RUN chown www:www -R /var/www/html/public
RUN chown www:www /build
RUN chown www:www -R /build
RUN chown www:www -R /var/www/html

## Copy everything
COPY --chown=www:www . .
RUN ls -l

# Copy startup script
COPY --chown=www:www ./docker/app/start.sh /usr/local/bin/start
COPY --chown=www:www ./docker/wait-for-it-bash.sh /usr/local/bin/wait-for-it-bash
RUN chmod u+x /usr/local/bin/wait-for-it-bash
RUN chmod u+x /usr/local/bin/start

# Change current user to www
USER www

# Expose port 9000
EXPOSE 9000

# Start php-fpm service
CMD ["php-fpm"]

## Run startup script
# ENTRYPOINT [ "sh" ]
# ENTRYPOINT [ "bash" ]

# CMD ["/usr/local/bin/wait-for-it-bash", "db:3306", "--", "/usr/local/bin/start"]
# CMD ["/usr/local/bin/start"]
