#!/bin/bash
#if [ ! -d "/var/www/html/vendor/" ] 
#then
composer install
#fi

if [ -z "$HAS_APP_KEY"  ] 
then
  php /var/www/html/artisan key:generate
fi

php /var/www/html/artisan cache:clear
php /var/www/html/artisan config:clear
php /var/www/html/artisan route:clear
php /var/www/html/artisan migrate
php /var/www/html/artisan db:seed
php /var/www/html/artisan queue:restart

php-fpm