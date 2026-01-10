<?php

namespace App\Services\TelegramNotifier;

use CSlant\LaravelTelegramGitNotifier\Services\CommandService;
use CSlant\TelegramGitNotifier\Bot;
use CSlant\TelegramGitNotifier\Exceptions\EntryNotFoundException;
use CSlant\TelegramGitNotifier\Exceptions\MessageIsEmptyException;

class CustomCommandService extends CommandService
{
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
        $text = $this->bot->telegram->Text();

        switch ($text) {
            case '/start':
                $this->sendStartMessage($this->bot);
                break;
            case '/menu':
                $this->bot->sendMessage(
                    view("$this->viewNamespace::tools.menu"),
                    ['reply_markup' => $this->menuMarkup($this->bot->telegram)]
                );
                break;
            case '/token':
            case '/id':
            case '/usage':
            case '/server':
                $this->bot->sendMessage(view("$this->viewNamespace::tools.".trim($text, '/')));
                break;
            case '/settings':
                $this->bot->settingHandle();
                break;
            case '/set_menu':
                $this->bot->setMyCommands(self::menuCommands());
                break;
            default:
                $this->bot->sendMessage('🤨 '.__('tg-notifier::app.invalid_request'));
        }
    }
}
