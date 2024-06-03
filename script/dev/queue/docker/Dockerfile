# FROM php:8.0-fpm-alpine3.13
FROM public.ecr.aws/docker/library/php:8.2-fpm-alpine3.16

# To be used for locale
ENV MUSL_LOCALE_DEPS cmake make musl-dev gcc gettext-dev libintl
ENV MUSL_LOCPATH /usr/share/i18n/locales/musl


# Install dependencies
RUN apk update \
  && apk --no-cache --update add build-base \
  libzip-dev \
  libpng-dev \
  libjpeg-turbo-dev \
  freetype-dev \
  zip \
  jpegoptim optipng pngquant gifsicle \
  vim \
  git \
  curl \
  gnupg \
#   $PHPIZE_DEPS \
  $MUSL_LOCALE_DEPS \
  && wget https://gitlab.com/rilian-la-te/musl-locales/-/archive/master/musl-locales-master.zip \
  && unzip musl-locales-master.zip \
      && cd musl-locales-master \
      && cmake -DLOCALE_PROFILE=OFF -DCMAKE_INSTALL_PREFIX=/usr . && make && make install \
      && cd .. && rm -r musl-locales-master

# Install extensions
RUN docker-php-ext-install pdo_mysql zip exif pcntl bcmath
RUN docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/
RUN NUMPROC=$(grep -c ^processor /proc/cpuinfo 2>/dev/null || 1) \
    && docker-php-ext-install -j${NUMPROC} gd
RUN docker-php-ext-install gd

# Changing workdir
WORKDIR /var/www/html
# Copy all files to above worlk dir
COPY . .
COPY ./docker/queue/php.ini /usr/local/etc/php/conf.d/local.ini

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install

# Configuring Cronjob time
RUN echo "* * * * * /bin/sh /usr/local/bin/scheduler.sh" >> /var/spool/cron/crontabs/root

COPY ./docker/queue/start.sh /usr/local/bin/start

COPY ./docker/scheduler/start.sh /usr/local/bin/scheduler.sh

RUN chmod u+x /usr/local/bin/scheduler.sh

RUN chmod u+x /usr/local/bin/start

COPY ./docker/queue/forground_horizan_proc.sh /usr/local/bin/forground_horizan_proc.sh
RUN chmod u+x /usr/local/bin/forground_horizan_proc.sh

COPY ./docker/scheduler/forground_schdeuler_proc.sh /usr/local/bin/forground_schdeuler_proc.sh
RUN chmod u+x /usr/local/bin/forground_schdeuler_proc.sh

ENTRYPOINT [ "sh" ]

CMD ["/usr/local/bin/start"]
