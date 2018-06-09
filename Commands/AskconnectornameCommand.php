<?php

namespace Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class AskconnectornameCommand extends SetconnectornameCommand
{
    /**
     * @var string Command Name
     */
    protected $name = "askconnectorname";

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
        //we are asking user to set a name for a specific connector
        $reply_markup = $this->getTelegram()->forceReply();
        $this->replyWithMessage([
            'text' => $this->prompt1 . $arguments . $this->prompt2,
            'reply_markup' => $reply_markup
        ]);
    }
}
