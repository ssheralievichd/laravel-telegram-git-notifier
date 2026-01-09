<?php

namespace App\Services\TelegramNotifier;

class ChatManager
{
    private string $settingsFile;
    private array $settings;

    public function __construct()
    {
        $this->settingsFile = storage_path('app/vendor/tg-notifier/jsons/tgn-settings.json');
        $this->loadSettings();
    }

    private function loadSettings(): void
    {
        if (file_exists($this->settingsFile)) {
            $content = file_get_contents($this->settingsFile);
            $this->settings = json_decode($content, true) ?? [];
        } else {
            $this->settings = [];
        }
    }

    private function saveSettings(): void
    {
        file_put_contents(
            $this->settingsFile,
            json_encode($this->settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    public function getNotifyChats(): array
    {
        return $this->settings['notify_chats'] ?? [];
    }

    public function addChat(string $chatId, array $threadIds = []): bool
    {
        if (!isset($this->settings['notify_chats'])) {
            $this->settings['notify_chats'] = [];
        }

        $this->settings['notify_chats'][$chatId] = $threadIds;
        $this->saveSettings();

        return true;
    }

    public function removeChat(string $chatId): bool
    {
        if (isset($this->settings['notify_chats'][$chatId])) {
            unset($this->settings['notify_chats'][$chatId]);
            $this->saveSettings();
            return true;
        }

        return false;
    }

    public function addThread(string $chatId, string $threadId): bool
    {
        if (!isset($this->settings['notify_chats'][$chatId])) {
            $this->settings['notify_chats'][$chatId] = [];
        }

        if (!in_array($threadId, $this->settings['notify_chats'][$chatId])) {
            $this->settings['notify_chats'][$chatId][] = $threadId;
            $this->saveSettings();
            return true;
        }

        return false;
    }

    public function removeThread(string $chatId, string $threadId): bool
    {
        if (isset($this->settings['notify_chats'][$chatId])) {
            $key = array_search($threadId, $this->settings['notify_chats'][$chatId]);
            if ($key !== false) {
                unset($this->settings['notify_chats'][$chatId][$key]);
                $this->settings['notify_chats'][$chatId] = array_values($this->settings['notify_chats'][$chatId]);
                $this->saveSettings();
                return true;
            }
        }

        return false;
    }

    public function formatForEnv(): string
    {
        $chats = $this->getNotifyChats();
        $parts = [];

        foreach ($chats as $chatId => $threadIds) {
            if (empty($threadIds)) {
                $parts[] = $chatId;
            } else {
                $parts[] = $chatId . ':' . implode(',', $threadIds);
            }
        }

        return implode(';', $parts);
    }

    public function syncFromEnv(string $envValue): void
    {
        $this->settings['notify_chats'] = [];

        if (empty($envValue)) {
            $this->saveSettings();
            return;
        }

        $chatPairs = explode(';', $envValue);

        foreach ($chatPairs as $pair) {
            if (empty($pair)) {
                continue;
            }

            if (strpos($pair, ':') !== false) {
                [$chatId, $threads] = explode(':', $pair, 2);
                $threadIds = explode(',', $threads);
            } else {
                $chatId = $pair;
                $threadIds = [];
            }

            $this->settings['notify_chats'][$chatId] = $threadIds;
        }

        $this->saveSettings();
    }
}
