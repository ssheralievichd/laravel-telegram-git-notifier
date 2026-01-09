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

if [ -f "/var/www/html/app/config/app-providers.php" ] && [ -f "/var/www/html/bootstrap/providers.php" ]; then
    if ! grep -q "DynamicChatServiceProvider" /var/www/html/bootstrap/providers.php 2>/dev/null; then
        echo "Registering custom service providers..."

        while IFS= read -r line; do
            if echo "$line" | grep -q "App\\\\"; then
                PROVIDER_CLASS=$(echo "$line" | grep -o "App\\\\[^,]*" | tr -d ',' | xargs)
                if [ -n "$PROVIDER_CLASS" ]; then
                    sed -i "/^];$/i\\    ${PROVIDER_CLASS}::class," /var/www/html/bootstrap/providers.php
                fi
            fi
        done < /var/www/html/app/config/app-providers.php
    fi
fi

if [ -z "${TGN_APP_URL}" ] && [ -n "${APP_URL}" ]; then
    export TGN_APP_URL="${APP_URL}"
    echo "TGN_APP_URL not set, using APP_URL: ${TGN_APP_URL}"
fi

if [ -n "${TELEGRAM_NOTIFY_CHAT_IDS}" ]; then
    echo "Syncing chats from environment..."
    php artisan tgn:chats sync 2>/dev/null || echo "Chat sync failed, will use env value"
fi

if [ -n "${TELEGRAM_BOT_TOKEN}" ] && [ -n "${TGN_APP_URL}" ]; then
    echo "Setting Telegram webhook to ${TGN_APP_URL}..."
    php artisan tg-notifier:webhook:set || echo "Warning: Failed to set webhook. Check bot token and TGN_APP_URL accessibility."
fi

exec "$@"
