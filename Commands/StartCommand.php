<?php

namespace Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class StartCommand extends DefaultKeyboardCommand
{
    /**
     * @var string Command Name
     */
    protected $name = "start";

    /**
     * @var string Command Description
     */
    protected $description = "Press START button";

    protected $userid;

    /**
     * @inheritdoc
     */
    public function handle($arguments)
    {
        $this->userid = $this->getReplyToId();

        if (strpos($arguments, 'AUTH_') !== false) {
            return $this->browserConnectionAuth($arguments);
        }

        $this->replyWithMessage(['text' => 'Hello! TrafficRobot on duty.']);

        $s = \Models\App::getStorage();
        $connector = new \Models\Connector($s);
        $connector->setUserid($this->userid);
        $keys = $connector->getKeysByUserid();
        if (empty($keys)) {
            $this->triggerCommand(NewconnectorCommand::name(), NewconnectorCommand::first_time);
            $this->replyWithMessage(['text' => 'You are anonymous to sender as well!']);
        } else {
            $this->triggerCommand(MyconnectorsCommand::name());
        }

        $this->replyWithDefaultKeyboard([
            'text' => 'You can delete (or create new) connectors anytime.'.
                PHP_EOL.PHP_EOL.'More info at '.\Models\App::getConfig()->baseUrl
        ]);
    }

    protected function browserConnectionAuth($arguments)
    {
        $machineid  = str_replace('AUTH_', '', $arguments);
        $storage = \Models\App::getStorage();
        $bc = new \Models\BrowserConnection($storage);
        $bc->setMachineid($machineid)->setUserid($this->userid)->associate();
        $this->replyWithDefaultKeyboard([
            'text' => 'Browser authorized!'
        ]);
    }
}
