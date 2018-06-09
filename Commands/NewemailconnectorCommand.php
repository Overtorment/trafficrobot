<?php

namespace Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class NewemailconnectorCommand extends DefaultKeyboardCommand
{
    /**
     * @var string Command Name
     */
    protected $name = "newemailconnector";

    protected $visible = false;

    /**
     * @var string Command Description
     */
    protected $description = "Create new email connector";


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
        $s = \Models\App::getStorage();
        $this->userid = $this->getReplyToId();
        $this->connector = new \Models\EmailConnector($s);
        $key = $this->connector->setUserid($this->userid)->newKey();

        $email = $key."@".\Models\App::getConfig()->mailDomain;
        $this->replyWithDefaultKeyboard(['text' => 'Now anyone can text you via unique email '.$email]);
    }


}
