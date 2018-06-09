<?php

namespace Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class DeleteconnectorCommand extends DefaultKeyboardCommand
{
    /**
     * @var string Command Name
     */
    protected $name = "deleteConnector";

    public $postName = " \xF0\x9F\x94\x95";

    /**
     * @var string Command Description
     */
    protected $description = "Delete connectors";


    /**
     * @var \Models\Connector
     */
    protected $connector;

    /**
     * @var \Models\EmailConnector
     */
    protected $emailConnector;

    protected $userid;

    /**
     * @inheritdoc
     */
    public function handle($arguments)
    {
        $s = \Models\App::getStorage();
        $this->userid = $this->getReplyToId();
        $this->connector = new \Models\Connector($s);
        $this->emailConnector = new \Models\EmailConnector($s);
        $this->connector->setUserid($this->userid);
        $this->emailConnector->setUserid($this->userid);

        if (!empty($arguments) && trim($arguments) != trim($this->postName)){
            $this->deleteConnector($arguments);
        } else {
            $this->listConnectorsForDeletion();
        }
    }



    public function listConnectorsForDeletion(){
        $keys  = $this->connector->getKeysByUserid();
        $keys2 = $this->emailConnector->getKeysByUserid();
        $keys = array_merge($keys, $keys2);

        if (empty($keys)){
            $this->replyWithDefaultKeyboard([
                'text' => 'No connectors left'
            ]);
            return;
        }

        $keys2 = array();
        foreach ($keys as $key){
            $name = $this->connector->setKey($key)->getConnectorNameByKey();
            $name2 = $this->emailConnector->setKey($key)->getConnectorNameByKey();
            $keys2[$key] = $name ? $name : $name2;
        }

        $keyboard = [];
        foreach ($keys2 as $key => $name) {
            if ($name) $name = ' ('.preg_replace('/[^A-Za-z0-9\-\.]/', '', $name).')';
            // special chars break whole  Telegram response
            $keyboard[] = ['/'.DeleteCommand::name().' '.$key.$name];
        }
        $keyboard[] = ['/'.CancelCommand::name()];

        $reply_markup = $this->getTelegram()->replyKeyboardMarkup([
            'keyboard' => $keyboard,
            'force_reply' => true,
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ]);

        $this->replyWithMessage([
            'text' => 'Which one you want to delete?',
            'reply_markup' => $reply_markup
        ]);
    }


    public function deleteConnector($arguments){
        $arguments = explode(' ', $arguments);
        $arguments = $arguments[0]; // other part of argument might be comment
        $this->connector->setKey($arguments)->deleteConnector();
        $this->emailConnector->setKey($arguments)->deleteConnector();


        $this->replyWithDefaultKeyboard([
            'text' => 'Deleted '.$arguments
        ]);

        return;

    }
}
