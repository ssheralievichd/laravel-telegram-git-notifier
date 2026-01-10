<?php

namespace App\Services\TelegramNotifier;

use CSlant\LaravelTelegramGitNotifier\Services\CommandService;
use CSlant\TelegramGitNotifier\Bot;
use CSlant\TelegramGitNotifier\Exceptions\EntryNotFoundException;
use CSlant\TelegramGitNotifier\Exceptions\MessageIsEmptyException;

class CustomCommandService extends CommandService
{
    protected Bot $botInstance;

    public function __construct(Bot $bot)
    {
        parent::__construct($bot);
        $this->botInstance = $bot;
    }

    public function sendStartMessage(Bot $bot): void
    {
        $reply = view(
            "$this->viewNamespace::tools.start",
            ['first_name' => $bot->telegram->FirstName()]
        );

        $bot->sendMessage($reply);
    }

    public function handle(): void
    {
        $text = $this->botInstance->telegram->Text();

        switch ($text) {
            case '/start':
                $this->sendStartMessage($this->botInstance);
                break;
            case '/menu':
                $this->botInstance->sendMessage(
                    view("$this->viewNamespace::tools.menu"),
                    ['reply_markup' => $this->menuMarkup($this->botInstance->telegram)]
                );
                break;
            case '/token':
            case '/id':
            case '/usage':
            case '/server':
                $this->botInstance->sendMessage(view("$this->viewNamespace::tools.".trim($text, '/')));
                break;
            case '/settings':
                $this->botInstance->settingHandle();
                break;
            case '/set_menu':
                $this->botInstance->setMyCommands(self::menuCommands());
                break;
            default:
                $this->botInstance->sendMessage('🤨 '.__('tg-notifier::app.invalid_request'));
        }
    }
}
