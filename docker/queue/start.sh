#!/bin/bash

php /var/www/html/artisan horizon &
crond -f -L /dev/stdout


# parallel --line-buffer ::: ./forground_horizan_proc.sh ./forground_schdeuler_proc.sh
