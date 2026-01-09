# Docker Setup for Laravel Telegram Git Notifier

This Docker setup creates a test Laravel application with the package pre-installed for testing purposes.

## Architecture

The Dockerfile uses multi-stage builds:

### Stage 1: Base (base)
- PHP 8.2-FPM Alpine base image
- System dependencies (nginx, supervisor, PHP extensions)
- Composer installation

### Stage 2: Test Application (test-app)
- Fresh Laravel installation
- Package installed via Composer
- Pre-configured with all required services

## Quick Start

1. Copy `.env.example` to `.env` and configure:
   ```bash
   cp .env.example .env
   ```

2. Set required environment variables:
   ```env
   TELEGRAM_BOT_TOKEN=your_bot_token_here
   TELEGRAM_NOTIFY_CHAT_IDS=your_chat_ids_here
   APP_URL=http://localhost:8080
   ```

3. Build and start containers:
   ```bash
   docker-compose up -d --build
   ```

4. Wait for the application to be ready (check logs):
   ```bash
   docker-compose logs -f app
   ```

5. Access the application:
   - Main endpoint: `http://localhost:8080`
   - Webhook endpoint: `http://localhost:8080/telegram-git-notifier/`
   - Webhook info: `http://localhost:8080/telegram-git-notifier/webhook/info`

## Container Structure

### Services

**app** (telegram-git-notifier)
- Runs nginx + PHP-FPM via Supervisor
- Fresh Laravel app with package installed
- Port 8080 (configurable via APP_PORT)
- Volume for persistent JSON storage

**redis** (telegram-notifier-redis)
- Cache and session backend
- Port 6379
- Persistent data via volume

### Volumes

- `app-storage` - Stores configuration JSON files
- `redis-data` - Redis persistence

## Commands

### Container Management

```bash
# Build and start
docker-compose up -d --build

# View logs
docker-compose logs -f app

# Stop containers
docker-compose down

# Remove volumes
docker-compose down -v

# Rebuild from scratch
docker-compose down -v && docker-compose up -d --build
```

### Exec into Container

```bash
# Bash shell
docker exec -it telegram-git-notifier sh

# Run artisan commands
docker exec telegram-git-notifier php artisan tg-notifier:webhook:set

# Check storage permissions
docker exec telegram-git-notifier ls -la storage/app/vendor/tg-notifier/jsons
```

### Testing Webhooks

```bash
# Check webhook info
curl http://localhost:8080/telegram-git-notifier/webhook/info

# Test main endpoint
curl -X POST http://localhost:8080/telegram-git-notifier/ \
  -H "Content-Type: application/json" \
  -d '{"test": "data"}'
```

## Configuration Files

- `docker/nginx/default.conf` - Nginx server configuration
- `docker/supervisor/supervisord.conf` - Process manager configuration
- `docker/php/php.ini` - PHP custom settings
- `docker/entrypoint.sh` - Container initialization script

## Troubleshooting

### Container won't start
Check logs: `docker-compose logs -f app`

### Permission errors
The entrypoint script should handle this automatically. If issues persist:
```bash
docker exec telegram-git-notifier chown -R www-data:www-data storage
docker exec telegram-git-notifier chmod -R 775 storage
```

### Package not loading
Verify package is installed:
```bash
docker exec telegram-git-notifier php artisan package:discover
docker exec telegram-git-notifier composer show cslant/laravel-telegram-git-notifier
```

### Webhook not working
1. Verify TELEGRAM_BOT_TOKEN is set
2. Verify APP_URL is correct
3. Register webhook: `docker exec telegram-git-notifier php artisan tg-notifier:webhook:set`
4. Check webhook info: `curl http://localhost:8080/telegram-git-notifier/webhook/info`

## Production Deployment

This Docker setup is intended for testing. For production:

1. Use a proper Laravel application (not auto-generated)
2. Set up proper environment variables
3. Use production-grade database (MySQL/PostgreSQL)
4. Configure proper SSL/TLS certificates
5. Set APP_ENV=production and APP_DEBUG=false
6. Generate proper APP_KEY: `php artisan key:generate`
7. Use secrets management for sensitive data
8. Configure proper logging and monitoring

## Notes

- The default APP_KEY in docker-compose.yml is for testing only
- JSON storage is persisted in Docker volume
- Package is installed from Packagist (not local files)
- For local development of the package, mount it as a volume
