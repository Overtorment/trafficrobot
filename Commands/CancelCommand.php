<?php

namespace Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class CancelCommand extends DefaultKeyboardCommand
{
    /**
     * @var string Command Name
     */
    protected $name = "cancel";

    /**
     * @var string Command Description
     */
    protected $description = "Cancels current action";

    protected $visible = false;


    /**
     * @var \Models\Connector
     */
    protected $connector;
    protected $userid;

    /**
     * @inheritdoc
     */
    public function handle($arguments)
    {
        $reply_markup = $this->getTelegram()->forceReply();
        $this->replyWithDefaultKeyboard([
            'text' => 'Cancelled',
            'reply_markup' => $reply_markup
        ]);

    }


}