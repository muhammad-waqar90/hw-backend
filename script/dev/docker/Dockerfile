# Application
FROM public.ecr.aws/docker/library/php:8.2-fpm-bullseye

# ARG AWS_CF
# ENV AWS_CF=$AWS_CF
# RUN echo $AWS_CF

# RUN touch /private.pem

# RUN echo '"'$AWS_CF'"' >> /private.pem

# RUN cat /private.pem

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
  gnupg \
  nano
# RUN apt-key adv --recv-keys --keyserver keyserver.ubuntu.com 0E98404D386FA1D9 6ED0E7B82643E131 F8D2585B8783D481  

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install extensions
RUN docker-php-ext-install pdo_mysql zip exif pcntl bcmath
RUN docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/
RUN docker-php-ext-install gd

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Add user for laravel application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Copy startup script
COPY --chown=www:www ./script/dev/docker/start.sh /usr/local/bin/start
COPY --chown=www:www ./script/dev/wait-for-it-bash.sh /usr/local/bin/wait-for-it-bash
COPY --chown=www:www ./script/dev/docker/www.conf.default /usr/local/etc/php-fpm.d/www.conf.default
RUN chmod u+x /usr/local/bin/wait-for-it-bash
RUN chmod u+x /usr/local/bin/start
RUN chmod u+x /usr/local/etc/php-fpm.d/www.conf.default

RUN chown www:www -R /var/www/html
COPY --chown=www:www . .
COPY script/dev/php.ini /usr/local/etc/php/conf.d/

RUN composer install

RUN chown www:www -R /var/www/html
# Change current user to www
USER www

# Exposing port inside container
EXPOSE 9000

ENTRYPOINT [ "sh" ]
CMD ["/usr/local/bin/start"]
