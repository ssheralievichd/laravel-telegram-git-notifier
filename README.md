# Welcome to Telegram GitHub/GitLab Notifier 👋


<p align="center">
<a href="#"><img src="https://img.shields.io/github/license/cslant/laravel-telegram-git-notifier.svg?style=flat-square" alt="License"></a>
<a href="https://github.com/cslant/laravel-telegram-git-notifier/releases"><img src="https://img.shields.io/github/release/cslant/laravel-telegram-git-notifier.svg?style=flat-square" alt="Latest Version"></a>
<a href="https://packagist.org/packages/cslant/laravel-telegram-git-notifier"><img src="https://img.shields.io/packagist/dt/cslant/laravel-telegram-git-notifier.svg?style=flat-square" alt="Total Downloads"></a>
<a href="https://github.com/cslant/laravel-telegram-git-notifier/actions/workflows/setup_test.yml"><img src="https://img.shields.io/github/actions/workflow/status/cslant/laravel-telegram-git-notifier/setup_test.yml?label=tests&branch=main" alt="Test Status"></a>
<a href="https://github.com/cslant/laravel-telegram-git-notifier/actions/workflows/php-cs-fixer.yml"><img src="https://img.shields.io/github/actions/workflow/status/cslant/laravel-telegram-git-notifier/php-cs-fixer.yml?label=code%20style&branch=main" alt="Code Style Status"></a>
<a href="https://scrutinizer-ci.com/g/cslant/laravel-telegram-git-notifier"><img src="https://img.shields.io/scrutinizer/g/cslant/laravel-telegram-git-notifier.svg?style=flat-square" alt="Quality Score"></a>
<a href="https://codeclimate.com/github/cslant/laravel-telegram-git-notifier/maintainability"><img src="https://api.codeclimate.com/v1/badges/a4f72c7bdd4200cf3dda/maintainability" alt="Maintainability"></a>
</p>

## 📝 Introduction

Laravel Telegram Git Notifier is a Laravel package that allows you to create a Telegram bot to receive notifications from GitHub or GitLab events and manage customization through messages and buttons on Telegram.

- Send notifications of your GitHub/GitLab repositories to Telegram Bots, Groups, Super Groups (Multiple Topics), and Channels.
- The bot must be created using the [BotFather](https://core.telegram.org/bots#6-botfather)

## 🎉 Features

1. **GitHub/GitLab Notifications to Telegram**: Configure a Telegram bot to receive notifications from various GitHub/GitLab events, including **commits, pull requests, issues, releases, and many more**.

<p align="center">
  <img alt="GitHub/GitLab Notifications to Telegram" src="https://github.com/cslant/telegram-git-notifier-app/assets/35853002/462f330f-11d3-43ef-89cf-c70ade57b654" />
</p>

2. **Customize Notifications**: Customize the types of notifications you want to receive through options on Telegram.

3. **Interactive Buttons**: Create interactive buttons on Telegram to perform actions such as enabling or disabling notifications.

4. **Event Management**: Manage specific events that you want to receive notifications for, allowing you to focus on what's most important for your projects.
   - Support for multiple platforms: GitHub and GitLab
   - Manage event notifications separately between platforms

<p align="center">
  <img alt="Event Management to Telegram" src="https://github.com/cslant/telegram-git-notifier-app/assets/35853002/e217a2ad-49b5-4936-a2cd-fe4af66e2bfb" />
</p>

5. **Easy Integration**: Provides an API and user-friendly functions to create a Telegram bot and link it to your GitHub/GitLab account.

6. **Support for Multiple Chats**: Add multiple chat IDs to receive notifications in different groups, channels, or user chats. You can also add the bot's own chat ID to receive notifications.

7. **For Premium Users**:
   - **Support for Multiple Topics**: Add multiple topics for supergroups to organize notifications by topic/thread.

## 📋 Requirements

- PHP ^8.1
- [Composer](https://getcomposer.org/)
- Docker & Docker Compose (for Docker installation method)
- Core: [Telegram Git Notifier](https://github.com/cslant/telegram-git-notifier.git)

## 🔧 Installation

This package can be installed in an existing Laravel application or run standalone using Docker.

### For Existing Laravel Applications

Install via Composer:

```bash
composer require cslant/laravel-telegram-git-notifier
```

### Standalone Installation with Docker

This repository includes a Docker setup that creates a test Laravel application with the package pre-installed.

#### 1. Clone and Setup

```bash
git clone https://github.com/cslant/laravel-telegram-git-notifier.git
cd laravel-telegram-git-notifier
cp .env.example .env
```

#### 2. Configure Environment Variables

Edit `.env` and set:

```dotenv
APP_PORT=8085
APP_URL=http://localhost:8085

TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_NOTIFY_CHAT_IDS=your_chat_ids_here

TGN_APP_NAME=Laravel Telegram Git Notifier
TGN_APP_URL=http://localhost:8085
TGN_DEFAULT_ROUTE_PREFIX=telegram-git-notifier
```

#### 3. Build and Start Containers

```bash
docker-compose up -d --build
```

#### 4. Verify Installation

Check container status:
```bash
docker-compose ps
```

Access endpoints:
- Application: `http://localhost:8085`
- Webhook info: `http://localhost:8085/telegram-git-notifier/webhook/info`

## 🚀 Configuration

### Step 1: Create a Telegram Bot

1. Open a chat with [BotFather](https://telegram.me/botfather)
2. Send `/newbot` command
3. Enter a friendly name for your bot
4. Enter a unique username ending in `bot` (e.g., `MyProjectBot`)
5. Copy the HTTP API token provided
6. Add the token to your `.env` file:

```dotenv
TELEGRAM_BOT_TOKEN=123456789:ABCDEFGHIJKLMNOPQRSTUVWXYZ
```

### Step 2: Set Up Domain and SSL (Required for Production)

For production, you need HTTPS. For local testing, you can use ngrok:

1. Start your Docker container:
   ```bash
   docker-compose up -d
   ```

2. Install and run [ngrok](https://ngrok.com/download):
   ```bash
   ngrok http 8085
   ```

3. Copy the HTTPS URL provided by ngrok and update `.env`:
   ```dotenv
   TGN_APP_URL=https://your-ngrok-url.ngrok-free.app
   APP_URL=https://your-ngrok-url.ngrok-free.app
   ```

4. Restart the container:
   ```bash
   docker-compose restart
   ```

### Step 3: Get Your Chat ID

1. Open a chat with your bot
2. Send any message to the bot
3. Visit: `http://your-domain/telegram-git-notifier/webhook/updates`
4. Find the `"chat":{"id":` field and copy the number
5. Add to `.env`:

```dotenv
TELEGRAM_NOTIFY_CHAT_IDS=123456789
```

### Step 4: Set the Webhook

#### Option A: Using the Package Endpoint

Visit: `http://your-domain/telegram-git-notifier/webhook/set`

You should see:
```json
{"ok":true,"result":true,"description":"Webhook was set"}
```

#### Option B: Using Artisan Command

```bash
docker exec telegram-git-notifier php artisan tg-notifier:webhook:set
```

#### Option C: Manual Telegram API Call

```
https://api.telegram.org/bot<YourBotToken>/setWebhook?url=<TGN_APP_URL>/telegram-git-notifier/
```

### Step 5: Configure Notification Recipients

#### Single Chat

```dotenv
TELEGRAM_NOTIFY_CHAT_IDS=123456789
```

#### Multiple Chats

Use semicolons to separate chat IDs:
```dotenv
TELEGRAM_NOTIFY_CHAT_IDS=123456789;987654321
```

#### With Supergroup Topics (Premium)

Use colons to specify thread IDs:
```dotenv
TELEGRAM_NOTIFY_CHAT_IDS="-1001234567:2;-1009876543:5,10"
```

Format explanation:
- `;` separates different chats
- `:` separates chat ID from thread ID
- `,` separates multiple threads in the same chat

Example:
```dotenv
TELEGRAM_NOTIFY_CHAT_IDS="-978339113;-1001933979183:2,13;6872320129"
```

This sends notifications to:
- Chat `-978339113` (no specific thread)
- Chat `-1001933979183` threads `2` and `13`
- Chat `6872320129` (no specific thread)

## 🎮 Usage

### Dynamic Chat/Thread Management

Manage notification chats and threads without restarting the container:

```bash
# List current chats and threads
docker exec telegram-git-notifier php artisan tgn:chats list

# Add a chat (all threads)
docker exec telegram-git-notifier php artisan tgn:chats add <chat_id>

# Add a specific thread to a chat
docker exec telegram-git-notifier php artisan tgn:chats add -- <chat_id> <thread_id>

# Remove a thread from a chat
docker exec telegram-git-notifier php artisan tgn:chats remove -- <chat_id> <thread_id>

# Remove entire chat
docker exec telegram-git-notifier php artisan tgn:chats remove <chat_id>

# Sync from TELEGRAM_NOTIFY_CHAT_IDS env variable
docker exec telegram-git-notifier php artisan tgn:chats sync
```

**Example:**
```bash
# Add supergroup with thread
docker exec telegram-git-notifier php artisan tgn:chats add -- "-1001933979183" "2"

# Add another thread to same chat
docker exec telegram-git-notifier php artisan tgn:chats add -- "-1001933979183" "13"

# List configuration
docker exec telegram-git-notifier php artisan tgn:chats list
```

**Note:** Use `--` before negative chat IDs to prevent them being interpreted as options.

Changes take effect immediately without container restart.

### Bot Commands

Send commands to your bot on Telegram:

```
/start    - Initialize bot and show welcome message
/menu     - Display main menu
/id       - Show your chat ID
/token    - Display bot token info
/usage    - Show usage instructions
/server   - Display server information
/settings - Open notification settings
/set_menu - Register bot commands menu
```

### Configure GitHub Webhook

1. Go to your GitHub repository
2. Navigate to **Settings** → **Webhooks** → **Add webhook**
3. Set Payload URL: `https://your-domain/telegram-git-notifier/`
4. Content type: **`application/x-www-form-urlencoded`** ⚠️ Important!
   - The package expects form-encoded data, not JSON
   - Using `application/json` will cause 500 errors
5. Select events you want to receive
6. Click **Add webhook**

### Configure GitLab Webhook

1. Go to your GitLab project
2. Navigate to **Settings** → **Webhooks**
3. Set URL: `https://your-domain/telegram-git-notifier/`
4. Select trigger events
5. Click **Add webhook**

### Customize Notifications

1. Send `/settings` to your bot
2. Use interactive buttons to:
   - Enable/disable specific event types
   - Configure GitHub events
   - Configure GitLab events
   - Manage custom events

## ✨ Supported Events

### GitHub Events Available

- [x] Push
- [x] Issues
- [x] Issue Comment
- [x] Pull Request
- [x] Pull Request Review
- [x] Fork
- [x] Commit Comment
- [x] Deployment
- [x] Deployment Status
- [x] Workflow Job
- [x] Workflow Run
- [x] Watch (Stars)
- [x] Label
- [x] Branch Protection Rule
- [x] Deploy Key
- [x] Meta
- [x] Ping
- [x] Team
- [x] Team Add

[See all GitHub events](https://docs.cslant.com/telegram-git-notifier/prologue/event-availability/github)

### GitLab Events Available

- [x] Push
- [x] Tag Push
- [x] Issue
- [x] Merge Request
- [x] Note (Comments)
- [x] Pipeline
- [x] Wiki Page
- [x] Job (Build)
- [x] Deployment
- [x] Release
- [x] Feature Flag

[See all GitLab events](https://docs.cslant.com/telegram-git-notifier/prologue/event-availability/gitlab)

## 🐳 Docker Usage

### Container Management

```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# View logs
docker-compose logs -f app

# Restart containers
docker-compose restart

# Rebuild containers
docker-compose up -d --build
```

### Execute Commands Inside Container

```bash
# Run artisan commands
docker exec telegram-git-notifier php artisan tg-notifier:webhook:set

# Check routes
docker exec telegram-git-notifier php artisan route:list | grep telegram

# Check storage permissions
docker exec telegram-git-notifier ls -la storage/app/vendor/tg-notifier/jsons

# Fix permissions if needed
docker exec telegram-git-notifier chown -R www-data:www-data storage
```

### Access Container Shell

```bash
docker exec -it telegram-git-notifier sh
```

## 📖 Official Documentation

For detailed documentation, please visit:
- [Telegram Git Notifier Documentation](https://docs.cslant.com/telegram-git-notifier)
- [Usage Guide](https://docs.cslant.com/telegram-git-notifier/usage/first_test)
- [DEPS.md](DEPS.md) - Complete dependency and architecture documentation

## 🔧 Troubleshooting

### Webhook Not Working

1. Check webhook status:
   ```bash
   curl http://localhost:8085/telegram-git-notifier/webhook/info
   ```

2. Verify bot token is correct in `.env`

3. Ensure `TGN_APP_URL` is publicly accessible (use ngrok for testing)

4. Re-register webhook:
   ```bash
   docker exec telegram-git-notifier php artisan tg-notifier:webhook:set
   ```

### No Notifications Received

1. **Verify GitHub webhook Content-Type is `application/x-www-form-urlencoded`**
   - Common issue: Using `application/json` causes 500 errors
   - Check webhook settings in GitHub repository

2. Verify `TELEGRAM_NOTIFY_CHAT_IDS` is set correctly

3. Check bot is a member of the target group/channel

4. Ensure events are enabled in bot settings (send `/settings` to bot)

5. Check container logs:
   ```bash
   docker-compose logs -f app
   ```

### GitHub Webhook 500 Errors

If you see "Server Error" when GitHub sends webhooks:

1. Check the Content-Type in GitHub webhook settings
2. It must be `application/x-www-form-urlencoded`, not `application/json`
3. Re-configure the webhook with correct Content-Type
4. Test with a ping event to verify it works

### Permission Errors

Fix storage permissions:
```bash
docker exec telegram-git-notifier chown -R www-data:www-data storage
docker exec telegram-git-notifier chmod -R 775 storage
```

### Port Already in Use

Change `APP_PORT` in `.env` to an available port:
```dotenv
APP_PORT=8086
```

Then restart:
```bash
docker-compose down
docker-compose up -d
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📝 License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## 🔗 Links

- [Package Repository](https://github.com/cslant/laravel-telegram-git-notifier)
- [Core Library](https://github.com/cslant/telegram-git-notifier)
- [Packagist](https://packagist.org/packages/cslant/laravel-telegram-git-notifier)
- [Documentation](https://docs.cslant.com/telegram-git-notifier)
- [Issues](https://github.com/cslant/laravel-telegram-git-notifier/issues)

## 📧 Support

If you have any questions or need help, please:
- Open an [issue](https://github.com/cslant/laravel-telegram-git-notifier/issues)
- Visit our [discussions](https://github.com/cslant/laravel-telegram-git-notifier/discussions)
- Check the [documentation](https://docs.cslant.com/telegram-git-notifier)
