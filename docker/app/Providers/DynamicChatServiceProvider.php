<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\TelegramNotifier\ChatManager;

class DynamicChatServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ChatManager::class, function ($app) {
            return new ChatManager();
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\ManageNotifyChats::class,
            ]);
        }

        $manager = $this->app->make(ChatManager::class);
        $chats = $manager->getNotifyChats();

        if (!empty($chats)) {
            $formatted = $manager->formatForEnv();
            putenv("TELEGRAM_NOTIFY_CHAT_IDS={$formatted}");
            $_ENV['TELEGRAM_NOTIFY_CHAT_IDS'] = $formatted;
            $_SERVER['TELEGRAM_NOTIFY_CHAT_IDS'] = $formatted;
        }
    }
}
