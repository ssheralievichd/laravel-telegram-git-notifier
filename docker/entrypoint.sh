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

if [ -f "/var/www/html/database/database.sqlite" ]; then
    chown www-data:www-data /var/www/html/database/database.sqlite
    chmod 664 /var/www/html/database/database.sqlite
fi

if [ ! -d "/var/www/html/database" ]; then
    mkdir -p /var/www/html/database
fi
chown -R www-data:www-data /var/www/html/database
chmod -R 775 /var/www/html/database

if [ -n "${TELEGRAM_BOT_TOKEN}" ] && [ -n "${APP_URL}" ]; then
    echo "Setting Telegram webhook..."
    php artisan tg-notifier:webhook:set || echo "Warning: Failed to set webhook. Check bot token and APP_URL accessibility."
fi

exec "$@"
