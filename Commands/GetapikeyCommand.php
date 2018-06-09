<?php

namespace Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class GetapikeyCommand extends DefaultKeyboardCommand
{
    /**
     * @var string Command Name
     */
    protected $name = "getapikey";
    protected $visible = false;

    /**
     * @var string Command Description
     */
    protected $description = "";


    /**
     * @inheritdoc
     */
    public function handle($arguments)
    {
        $s = \Models\App::getStorage();
        $userApi = new \Models\UserApi($s);
        $userApi->setUserid($this->getReplyToId());
        $apikey = $userApi->getApikeyByUserid();

        $this->replyWithDefaultKeyboard([
            'text' => "Your API key is {$apikey}",
            'disable_web_page_preview' => true
        ]);
    }
}
