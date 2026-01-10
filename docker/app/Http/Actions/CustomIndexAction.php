<?php

namespace App\Http\Actions;

use App\Services\TelegramNotifier\CustomCommandService;
use CSlant\LaravelTelegramGitNotifier\Http\Actions\IndexAction;
use CSlant\LaravelTelegramGitNotifier\Services\CallbackService;
use CSlant\LaravelTelegramGitNotifier\Services\NotificationService;
use CSlant\TelegramGitNotifier\Exceptions\BotException;
use CSlant\TelegramGitNotifier\Exceptions\CallbackException;
use CSlant\TelegramGitNotifier\Exceptions\EntryNotFoundException;
use CSlant\TelegramGitNotifier\Exceptions\InvalidViewTemplateException;
use CSlant\TelegramGitNotifier\Exceptions\MessageIsEmptyException;
use CSlant\TelegramGitNotifier\Exceptions\SendNotificationException;

class CustomIndexAction extends IndexAction
{
    /**
     * @throws InvalidViewTemplateException
     * @throws MessageIsEmptyException
     * @throws SendNotificationException
     * @throws BotException
     * @throws CallbackException
     * @throws EntryNotFoundException
     */
    public function __invoke(): void
    {
        if ($this->bot->isCallback()) {
            $callbackAction = new CallbackService($this->bot);
            $callbackAction->handle();

            return;
        }

        if ($this->bot->isMessage() && $this->bot->isOwner()) {
            $commandAction = new CustomCommandService($this->bot);
            $commandAction->handle();

            return;
        }

        $sendNotification = new NotificationService(
            $this->notifier,
            $this->bot->setting
        );
        $sendNotification->handle();
    }
}
