#!/bin/bash
# cp -r /build/vendor /var/www/html
# cp -r /build/public/mix-manifest.json /var/www/html/public/mix-manifest.json
# cp -r /build/public/js /var/www/html/public
# cp -r /build/public/css /var/www/html/public

php /var/www/html/artisan migrate --force
php /var/www/html/artisan db:seed --force
php /var/www/html/artisan optimize
php /var/www/html/artisan queue:restart

php-fpm
