FROM php:8.0-fpm

# Copy composer.lock and composer.json
#COPY composer.lock composer.json package.json /var/www/

# Set working directory
WORKDIR /var/www/html

# Install dependencies
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

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install extensions
RUN docker-php-ext-install pdo_mysql zip exif pcntl
RUN docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/
RUN docker-php-ext-install gd

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Add user for laravel application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Change current user to www

# Copy dependencies to build directoy
RUN mkdir /build-dir
WORKDIR /build-dir
COPY composer.lock composer.json /build-dir/
RUN chown -R www:www /build-dir

# Copy startup script
COPY --chown=www:www ./docker/api/start.sh /usr/local/bin/start
COPY --chown=www:www ./docker/wait-for-it-bash.sh /usr/local/bin/wait-for-it-bash
RUN chmod u+x /usr/local/bin/wait-for-it-bash
RUN chmod u+x /usr/local/bin/start

USER www
#Install dependencies
#RUN composer install

#Switch back to project root
WORKDIR /var/www/html

# Expose port 9000
EXPOSE 9000

#run startup script
#ENTRYPOINT [ "bash" ]
#CMD ["/usr/local/bin/wait-for-it-bash", "db:3306", "--", "/usr/local/bin/start"]

#CMD ["php-fpm"]
