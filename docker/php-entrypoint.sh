#!/bin/sh
set -e

if [ "$1" = "php-fpm" ]; then
    php database/init.php
    php database/seed.php
fi

exec docker-php-entrypoint "$@"
