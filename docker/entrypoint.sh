#!/bin/sh
set -e

if [ ! -d "/var/www/html/storage/app/vendor/tg-notifier/jsons" ]; then
    mkdir -p /var/www/html/storage/app/vendor/tg-notifier/jsons
fi

if [ ! -f "/var/www/html/storage/app/vendor/tg-notifier/jsons/tgn-settings.json" ]; then
    cp /var/www/html/vendor/cslant/telegram-git-notifier/config/jsons/tgn-settings.json \
       /var/www/html/storage/app/vendor/tg-notifier/jsons/
fi

if [ ! -f "/var/www/html/storage/app/vendor/tg-notifier/jsons/github-events.json" ]; then
    cp /var/www/html/vendor/cslant/telegram-git-notifier/config/jsons/github-events.json \
       /var/www/html/storage/app/vendor/tg-notifier/jsons/
fi

if [ ! -f "/var/www/html/storage/app/vendor/tg-notifier/jsons/gitlab-events.json" ]; then
    cp /var/www/html/vendor/cslant/telegram-git-notifier/config/jsons/gitlab-events.json \
       /var/www/html/storage/app/vendor/tg-notifier/jsons/
fi

chown -R www-data:www-data /var/www/html/storage/app/vendor/tg-notifier/jsons
chmod -R 775 /var/www/html/storage/app/vendor/tg-notifier/jsons

if [ ! -d "/var/www/html/bootstrap/cache" ]; then
    mkdir -p /var/www/html/bootstrap/cache
    chown -R www-data:www-data /var/www/html/bootstrap/cache
    chmod -R 775 /var/www/html/bootstrap/cache
fi

exec "$@"
