<?php

namespace Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class SetconnectornameCommand extends DefaultKeyboardCommand
{
    /**
     * @var string Command Name
     */
    protected $name = "setconnectorname";

    /**
     * @var string Command Description
     */
    protected $description = "Set human-readable comment for your connector";

    protected $prompt1 = 'Okay, now give connector ';
    protected $prompt2 = ' a name:';

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
        if (stripos($arguments, $this->prompt1) !== false) {
            // making sure that this is a reply to our prompt
            // means that we received request to set a name for connector

            $arguments = str_ireplace($this->prompt1, '', $arguments);
            $arguments = str_ireplace($this->prompt2, '__OLOLO__', $arguments);
            $arguments = explode('__OLOLO__', $arguments);

            $storage = new \Models\Storage();
            $connector = new \Models\Connector($storage);
            $connector->setKey($arguments[0]);
            $connector->saveConnectorNameByKey($arguments[1]);


            $reply_markup = $this->getTelegram()->replyKeyboardHide();
            $this->replyWithDefaultKeyboard([
                'text' => 'Connector '.$arguments[0].' now has a name \''.$arguments[1].'\'',
                'reply_markup' => $reply_markup
            ]);

            return;
        }
    }
}
