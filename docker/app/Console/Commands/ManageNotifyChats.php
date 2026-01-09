<?php

namespace App\Console\Commands;

use App\Services\TelegramNotifier\ChatManager;
use Illuminate\Console\Command;

class ManageNotifyChats extends Command
{
    protected $signature = 'tgn:chats {action} {chat_id?} {thread_id?}
                            {--sync : Sync from TELEGRAM_NOTIFY_CHAT_IDS env}';

    protected $description = 'Manage notification chats and threads dynamically';

    public function handle(): int
    {
        $manager = new ChatManager();
        $action = $this->argument('action');

        switch ($action) {
            case 'list':
                return $this->listChats($manager);

            case 'add':
                $chatId = $this->argument('chat_id');
                $threadId = $this->argument('thread_id');

                if (!$chatId) {
                    $this->error('Chat ID is required');
                    return 1;
                }

                if ($threadId) {
                    $manager->addThread($chatId, $threadId);
                    $this->info("Added thread {$threadId} to chat {$chatId}");
                } else {
                    $manager->addChat($chatId);
                    $this->info("Added chat {$chatId}");
                }

                $this->info("\nCurrent TELEGRAM_NOTIFY_CHAT_IDS format:");
                $this->line($manager->formatForEnv());

                return 0;

            case 'remove':
                $chatId = $this->argument('chat_id');
                $threadId = $this->argument('thread_id');

                if (!$chatId) {
                    $this->error('Chat ID is required');
                    return 1;
                }

                if ($threadId) {
                    if ($manager->removeThread($chatId, $threadId)) {
                        $this->info("Removed thread {$threadId} from chat {$chatId}");
                    } else {
                        $this->warn("Thread {$threadId} not found in chat {$chatId}");
                    }
                } else {
                    if ($manager->removeChat($chatId)) {
                        $this->info("Removed chat {$chatId}");
                    } else {
                        $this->warn("Chat {$chatId} not found");
                    }
                }

                $this->info("\nCurrent TELEGRAM_NOTIFY_CHAT_IDS format:");
                $this->line($manager->formatForEnv());

                return 0;

            case 'sync':
                $envValue = env('TELEGRAM_NOTIFY_CHAT_IDS', '');
                $manager->syncFromEnv($envValue);
                $this->info('Synced chats from TELEGRAM_NOTIFY_CHAT_IDS environment variable');
                return $this->listChats($manager);

            default:
                $this->error("Unknown action: {$action}");
                $this->info('Available actions: list, add, remove, sync');
                return 1;
        }
    }

    private function listChats(ChatManager $manager): int
    {
        $chats = $manager->getNotifyChats();

        if (empty($chats)) {
            $this->warn('No chats configured');
            $this->info("\nRun: php artisan tgn:chats sync");
            $this->info('Or: php artisan tgn:chats add <chat_id> [thread_id]');
            return 0;
        }

        $this->info('Configured notification chats:');
        $this->newLine();

        $rows = [];
        foreach ($chats as $chatId => $threadIds) {
            if (empty($threadIds)) {
                $rows[] = [$chatId, '-', 'All threads'];
            } else {
                foreach ($threadIds as $threadId) {
                    $rows[] = [$chatId, $threadId, ''];
                }
            }
        }

        $this->table(['Chat ID', 'Thread ID', 'Notes'], $rows);

        $this->newLine();
        $this->info('TELEGRAM_NOTIFY_CHAT_IDS format:');
        $this->line($manager->formatForEnv());

        return 0;
    }
}
