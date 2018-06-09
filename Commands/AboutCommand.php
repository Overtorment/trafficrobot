<?php

namespace Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class AboutCommand extends DefaultKeyboardCommand
{
    /**
     * @var string Command Name
     */
    protected $name = "about";

    public $postName = " \xF0\x9F\x98\x80";

    /**
     * @var string Command Description
     */
    protected $description = "About this bot";


    /**
     * @inheritdoc
     */
    public function handle($arguments)
    {
        $this->replyWithDefaultKeyboard([
            'text' => "TrafficRobot also has Chrome browser extension that puts random emails in every email inputs on every web page:\n".
                      "https://trafficrobot.tk/".
                      "\nSo you can always register on websites and receive emails in your telegram!".
                      "\n\nPlease rate TrafficRobot via this link: https://telegram.me/storebot?start=trafficRobot".
                      "\n\nYou can also email author directly at tcb253@trafficrobot.tk".
                      "\n\nOfficial website is https://trafficrobot.tk/".
                      "\nNews related to this bot are published to https://twitter.com/trafficrobotbot",
            'disable_web_page_preview' => true
        ]);
    }
}
