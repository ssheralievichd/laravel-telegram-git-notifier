# Laravel Telegram Git Notifier - Dependencies & Architecture

## Core Architecture

This package bridges Git platforms (GitHub/GitLab) with Telegram notifications through webhook processing and bot commands.

### Service Layer Structure

```
Entry Point: IndexAction
├── CallbackService (Telegram button interactions)
├── CommandService (Telegram bot commands)
└── NotificationService (Git webhook processing)
```

**IndexAction** routes requests to appropriate services:
- Telegram callbacks → CallbackService
- Telegram commands (owner only) → CommandService
- Git webhooks → NotificationService

**NotificationService** processes Git events:
1. Parse webhook payload from GitHub/GitLab
2. Validate event access via external Validator
3. Format notification using view templates
4. Send to multiple Telegram chats/threads

**CommandService** handles bot commands:
- `/start`, `/menu`, `/token`, `/id`, `/usage`, `/server`
- `/settings` - Open settings UI
- `/set_menu` - Register bot commands with Telegram

**CallbackService** handles button interactions:
- Settings toggles
- Navigation between menus
- Custom event configuration

## Critical Configuration

### Required Environment Variables

**TELEGRAM_BOT_TOKEN** (Required)
- Telegram bot authentication token
- Get from @BotFather
- Without: Complete system failure

**APP_URL / TGN_APP_URL** (Required for webhooks)
- Public webhook URL
- Format: `https://domain.com` (no trailing slash)
- Must be accessible from GitHub/GitLab
- Without: No webhook notifications

**TELEGRAM_NOTIFY_CHAT_IDS** (Required for notifications)
- Format: `chat_id1;chat_id2:thread_id;chat_id3:thread_id1,thread_id2`
- Semicolon separates chats
- Colon separates chat from thread
- Comma separates multiple threads
- Without: No notifications sent

**TGN_CONFIG_FILE_STORAGE_FOLDER** (Required)
- Default: `storage/app/vendor/tg-notifier/jsons`
- Must be writable by web server
- Stores: settings, event configurations
- Without: Settings not persisted

### Optional Configuration

**TGN_DEFAULT_ROUTE_PREFIX**
- Default: `telegram-git-notifier`
- Webhook endpoint: `{APP_URL}/{prefix}/`
- Must match webhook URL in Git platform

**TGN_REQUEST_VERIFY**
- Default: `false`
- Enable webhook signature verification

**TIMEZONE**
- Default: `UTC`
- Used for timestamp formatting

## Storage Dependencies

### File System Requirements

**Path:** `storage/app/vendor/tg-notifier/jsons/`

Files:
- `tgn-settings.json` - Bot settings state
- `github-events.json` - GitHub event definitions
- `gitlab-events.json` - GitLab event definitions

Permissions:
- Owner: `www-data` (or web server user)
- Mode: `775` minimum
- Fix: `php artisan config-json:change-owner www-data www-data`

### Docker Volumes

`./storage/app/vendor/tg-notifier/jsons` → `/var/www/html/storage/app/vendor/tg-notifier/jsons`
- Persists settings across container restarts
- Must be writable by container user

## External Dependencies

### Composer Packages

**cslant/telegram-git-notifier ^v1.4** (Core library)
- Provides: Bot, Notifier, Webhook, Validator, Setting
- Repository: https://packagist.org/packages/cslant/telegram-git-notifier

**Telegram Bot API Wrapper**
- HTTP client for Telegram API communication
- Methods: sendMessage, editMessage, setWebhook, etc.

### External APIs

**Telegram Bot API**
- Endpoint: `https://api.telegram.org/bot{token}/`
- Authentication: Token-based
- Used for: Message sending, webhook registration, command management

**GitHub Webhooks**
- Sends events to: `{APP_URL}/telegram-git-notifier/`
- Headers: `X-GitHub-Event`, `X-Hub-Signature`
- Events: Push, PR, Issues, etc.
- **Important**: Package expects `application/x-www-form-urlencoded` with JSON in `payload` parameter
  - Modern GitHub sends `application/json` by default, which causes initialization errors
  - Configure webhook Content-Type as `application/x-www-form-urlencoded` in GitHub settings
  - This is a package limitation in v1.5.0

**GitLab Webhooks**
- Sends events to: `{APP_URL}/telegram-git-notifier/`
- Headers: `X-Gitlab-Token`, `X-Gitlab-Event`
- Events: Push, MR, Issues, etc.
- Uses `application/json` Content-Type (works correctly)

### Redis

**Not used by this package**. The package uses file-based JSON storage for all configuration and settings. Redis was initially included but is unnecessary.

## Route Structure

**Base URL:** `{APP_URL}/{route_prefix}/`

Routes:
- `POST /` - Main webhook endpoint (all Telegram updates + Git webhooks)
- `GET /webhook/set` - Register webhook with Telegram
- `GET /webhook/delete` - Unregister webhook
- `GET /webhook/info` - Get webhook status
- `GET /webhook/updates` - Fetch pending updates

## Command Structure

### Console Commands

**tg-notifier:webhook:set**
- Register webhook URL with Telegram API
- Dependencies: WebhookService, Telegram API
- Required: TELEGRAM_BOT_TOKEN, TGN_APP_URL

**config-json:change-owner {user?} {group?}**
- Fix storage folder permissions
- Linux only
- Auto-detects web server user

### Telegram Bot Commands

Commands (sent via Telegram chat):
- `/start` - Welcome message
- `/menu` - Main menu with buttons
- `/token` - Show bot token info
- `/id` - Show user/chat ID
- `/usage` - Usage information
- `/server` - Server information
- `/settings` - Configuration UI
- `/set_menu` - Register commands with Telegram

## Data Flow

### Webhook Processing Flow

```
GitHub/GitLab → HTTP POST → IndexAction → NotificationService
                                        ↓
                              Validate event access
                                        ↓
                              Parse chat IDs/threads
                                        ↓
                              Format notification
                                        ↓
                              Telegram Bot API → User
```

### Command Processing Flow

```
Telegram User → /command → Telegram API → IndexAction → CommandService
                                                        ↓
                                              Load view template
                                                        ↓
                                              Generate markup
                                                        ↓
                                              Telegram API → User
```

### Callback Processing Flow

```
Telegram User → Click button → Telegram API → IndexAction → CallbackService
                                                           ↓
                                                  Parse callback data
                                                           ↓
                                                  Update settings JSON
                                                           ↓
                                                  Update message/buttons
                                                           ↓
                                                  Telegram API → User
```

## Critical Failure Points

### 1. Invalid Telegram Token
**Symptom:** All Telegram operations fail
**Check:** `curl https://api.telegram.org/bot{TOKEN}/getMe`
**Fix:** Set valid TELEGRAM_BOT_TOKEN in .env

### 2. Webhook Not Delivering
**Symptom:** No notifications from Git events
**Check:**
- APP_URL is publicly accessible
- Route prefix matches webhook URL
- Webhook registered: `GET /webhook/info`
**Fix:** `php artisan tg-notifier:webhook:set`

### 3. Settings Not Persisting
**Symptom:** Configuration changes lost on restart
**Check:**
- Storage folder permissions
- Disk space
- File owner matches web server user
**Fix:** `php artisan config-json:change-owner`

### 4. No Notifications Sent
**Symptom:** Webhooks received but no messages
**Check:**
- TELEGRAM_NOTIFY_CHAT_IDS is set
- Chat IDs are valid
- Bot is member of target chats/groups
**Fix:** Add bot to chats, verify chat IDs

## Docker Architecture

### Container Structure

**App Container (telegram-git-notifier)**
- Base: `php:8.2-fpm-alpine`
- Services: Nginx + PHP-FPM (via Supervisor)
- Port: `8080` (configurable via APP_PORT)
- Volumes: Application code, JSON storage

**Redis Container (telegram-notifier-redis)**
- Base: `redis:7-alpine`
- Port: `6379`
- Volume: `redis-data` for persistence

### Networking

Network: `telegram-notifier-network` (bridge driver)
- Isolation between containers
- Service discovery by name

### Initialization Flow

```
Docker Compose Up
      ↓
Build Dockerfile
      ↓
Run entrypoint.sh
      ↓
Create storage directories
      ↓
Set permissions
      ↓
Start Supervisor
      ↓
Start PHP-FPM + Nginx
      ↓
Application Ready
```

## Development Logic

### Service Instantiation Pattern

Services are instantiated inline in IndexAction, not via dependency injection:
- `new NotificationService()` - Creates with Request, Notifier, Setting
- `new CommandService($bot)` - Creates with Bot instance
- `new CallbackService($bot)` - Creates with Bot instance

### View Rendering Pattern

Views use namespace prefix from config:
- Namespace: `config('telegram-git-notifier.view.namespace')` → `tg-notifier`
- Render: `view('tg-notifier::tools.menu', [...])`
- Location: `resources/views/vendor/tg-notifier/`

### Translation Pattern

Translations use same namespace:
- Format: `__('tg-notifier::tools/menu.discussion')`
- Location: `lang/de/` (German default)
- Fallback: English if translation missing

### Exception Hierarchy

From external library:
- WebhookException - Webhook operations
- BotException - Bot operations
- CallbackException - Callback handling
- InvalidViewTemplateException - Missing templates
- MessageIsEmptyException - Empty messages
- SendNotificationException - Notification failures
- ConfigFileException - Config file errors
- EntryNotFoundException - Missing data

## Testing Strategy

### Unit Testing Focus

**NotificationService**
- Event validation logic
- Chat ID parsing (with threads)
- Exception handling

**CommandService**
- Command routing
- View rendering
- Markup generation

**CallbackService**
- Callback data parsing
- Settings updates
- Navigation flow

### Integration Testing Focus

**Webhook Flow**
- GitHub webhook → Notification
- GitLab webhook → Notification
- Multi-chat delivery
- Thread support

**Bot Command Flow**
- Command → Response
- Button → Callback → Update
- Settings → JSON persistence

### Docker Testing Steps

1. Build image: `docker-compose build`
2. Start services: `docker-compose up -d`
3. Check logs: `docker-compose logs -f app`
4. Test endpoint: `curl http://localhost:8080/telegram-git-notifier/`
5. Verify webhook: `curl http://localhost:8080/telegram-git-notifier/webhook/info`
6. Check permissions: `docker exec telegram-git-notifier ls -la storage/app/vendor/tg-notifier/jsons`

## Deployment Checklist

- [ ] Set TELEGRAM_BOT_TOKEN in .env
- [ ] Set APP_URL to public domain
- [ ] Set TELEGRAM_NOTIFY_CHAT_IDS
- [ ] Build Docker image
- [ ] Start containers
- [ ] Register webhook: `php artisan tg-notifier:webhook:set`
- [ ] Add webhook URL to GitHub/GitLab
- [ ] Test with sample event
- [ ] Verify bot commands work
- [ ] Check storage permissions
- [ ] Monitor logs for errors

## Maintenance Tasks

### Regular

- Monitor disk space in storage folder
- Check webhook delivery logs
- Verify Telegram API connectivity
- Review and clear old logs

### On Configuration Changes

- Restart containers: `docker-compose restart`
- Re-register webhook if URL changed
- Clear cache: `php artisan config:clear`

### On Updates

- Pull new code
- Rebuild image: `docker-compose build --no-cache`
- Restart containers: `docker-compose up -d`
- Verify webhook still registered
