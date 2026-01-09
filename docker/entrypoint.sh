#!/bin/sh
set -e

if [ ! -d "/var/www/html/storage/app/vendor/tg-notifier/jsons" ]; then
    mkdir -p /var/www/html/storage/app/vendor/tg-notifier/jsons
    chown -R www-data:www-data /var/www/html/storage/app/vendor/tg-notifier/jsons
    chmod -R 775 /var/www/html/storage/app/vendor/tg-notifier/jsons
fi

if [ ! -d "/var/www/html/bootstrap/cache" ]; then
    mkdir -p /var/www/html/bootstrap/cache
    chown -R www-data:www-data /var/www/html/bootstrap/cache
    chmod -R 775 /var/www/html/bootstrap/cache
fi

exec "$@"
