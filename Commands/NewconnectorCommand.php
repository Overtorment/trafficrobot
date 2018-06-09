<?php

namespace Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class NewconnectorCommand extends DefaultKeyboardCommand
{
    /**
     * @var string Command Name
     */
    protected $name = "newConnector";

    const first_time = "first_time";


    public $postName = " \xF0\x9F\x94\x8C";

    /**
     * @var string Command Description
     */
    protected $description = "Create new connector";


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
        $this->connector = new \Models\Connector($s);
        $key = $this->connector->setUserid($this->userid)->newKey();

        $url = \Models\App::getConfig()->baseUrl.'/'.$key;
        $email = $key."@".\Models\App::getConfig()->mailDomain;
        $this->replyWithDefaultKeyboard([
            'text' => 'Now anyone can text you anonymously via unique url '.$url.PHP_EOL.'or via unique email '.$email
        ]);

        if ($arguments != NewconnectorCommand::first_time) { // asking user to name a command
            $this->triggerCommand('askconnectorname', $key);
        }
    }
}
