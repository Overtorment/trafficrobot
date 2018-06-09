<?php

namespace Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

/**
 * Class DeleteCommand
 *
 * Asks for a confirmation to delete a connector
 *
 * @package Commands
 */
class DeleteCommand extends DefaultKeyboardCommand
{
    /**
     * @var string Command Name
     */
    protected $name = "delete";
    protected $visible = false;

    public function handle($arguments)
    {
        $key = trim($arguments);

        $keyboard = [];
        $keyboard[] = ['/'.DeleteconnectorCommand::name().' '.$key];
        $keyboard[] = ['/'.CancelCommand::name()];

        $reply_markup = $this->getTelegram()->replyKeyboardMarkup([
            'keyboard' => $keyboard,
            'force_reply' => true,
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ]);

        $this->replyWithMessage([
            'text' => "Are you sure you want to delete {$key}?",
            'reply_markup' => $reply_markup
        ]);
    }

}