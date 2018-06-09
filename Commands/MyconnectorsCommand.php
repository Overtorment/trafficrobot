<?php

namespace Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class MyconnectorsCommand extends DefaultKeyboardCommand
{
    /**
     * @var string Command Name
     */
    protected $name = "myConnectors";

    public $postName = " \xF0\x9F\x9A\xA6";

    /**
     * @var string Command Description
     */
    protected $description = "List my connectors";

    /**
     * @inheritdoc
     */
    public function handle($arguments)
    {
        $s = \Models\App::getStorage();
        $userid = $this->getReplyToId();

        $connector = new \Models\Connector($s);
        $keys = $connector->setUserid($userid)->getKeysByUserid();

        $emailConnector = new \Models\EmailConnector($s);
        $emailKeys = $emailConnector->setUserid($userid)->getKeysByUserid();

        $text = 'You have following connectors:'.PHP_EOL.PHP_EOL;
        $url = \Models\App::getConfig()->baseUrl;
        $domain = \Models\App::getConfig()->mailDomain;


        $this->replyWithDefaultKeyboard(['text' => $text, 'parse_mode' => 'Markdown']);
        sleep(1);
        $c = 0; // batching
        $text = '';
        foreach ($keys as $key) {
            $name = $connector->setKey($key)->getConnectorNameByKey();
            if ($name) {
                $name = ' (_'.$name.'_)';
            }
            $text .= "*{$key}*:$name\n`               `{$url}/{$key}\n`               `{$key}@{$domain}".PHP_EOL;
            $text .= PHP_EOL;
            if (++$c % 8 == 0) {
                $this->replyWithDefaultKeyboard(['text' => $text, 'parse_mode' => 'Markdown']);
                $text = '';
		        sleep(1);
            }
        }
        if ($text) {
            $this->replyWithDefaultKeyboard(['text' => $text, 'parse_mode' => 'Markdown']);
            sleep(1);
        }



        $c = 0; // batching
        $text = '';
        foreach ($emailKeys as $key) {
            $name = $emailConnector->setKey($key)->getConnectorNameByKey();
            if ($name) {
                $name = ' (_'.$name.'_)';
            }
            $text .= "*{$key}*:$name\n`               `{$key}@{$domain}".PHP_EOL;
            $text .= PHP_EOL;
            if (++$c % 8 == 0) {
                $this->replyWithDefaultKeyboard(['text' => $text, 'parse_mode' => 'Markdown']);
                $text = '';
		        sleep(1);
            }
        }
        if ($text) {
            $this->replyWithDefaultKeyboard(['text' => $text, 'parse_mode' => 'Markdown']);
            sleep(1);
        }
    }
}
