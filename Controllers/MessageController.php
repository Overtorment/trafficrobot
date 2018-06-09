<?php

namespace Controllers;

/**
 * Class MessageController
 *
 * Handles incomming emails from queue
 *
 * @package Controllers
 */
class MessageController
{

    /**
     * @var \Models\Storage
     */
    protected $storage;

    /**
     * @var \Telegram\Bot\Api
     */
    protected $telegram;

    /**
     * @var \Models\Connector
     */
    protected $connector;

    /**
     * @var \Models\EmailConnector
     */
    protected $emailConnector;

    /**
     * @var \Models\GhostEmailConnector
     */
    protected $ghostEmailConnector;

    /**
     * @var string Url to website
     */
    protected $domain;

    public function __construct(
        $storage,
        $telegram,
        $connector,
        $emailConnector,
        $ghostEmailConnector,
        $domain = 'http://localhost'
    ) {
        $this->storage             = $storage;
        $this->telegram            = $telegram;
        $this->connector           = $connector;
        $this->emailConnector      = $emailConnector;
        $this->ghostEmailConnector = $ghostEmailConnector;
        $this->domain              = $domain;
    }

    public function run($message)
    {
        if (empty($message['key'])) {
            throw new \Exception('No key present in message');
        }

        if (stripos($message['key'], '.') !== false) { // allowing email like key.ANYTHING@domain
            $message['key'] = explode('.', $message['key']);
            $message['key'] = $message['key'][0];
        }

        $this->connector->setKey($message['key']);
        $userid = $this->connector->getUseridByKey();
        if ($userid) {
            $this->telegramSendMessage(array(
                'chat_id' => $userid,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true
            ), $message['data']);
            $this->connector->saveLastData($message['data']);
        } else { // regular Connector not found, lets try emailConnector
            $this->emailConnector->setKey($message['key']);
            $userid = $this->emailConnector->getUseridByKey();
            if ($userid) {
                $this->telegramSendMessage(array(
                    'chat_id' => $userid,
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true
                ), $message['data']);
                $this->emailConnector->saveLastData($message['data']);
            } else { // trying last time, maybe its a GhostConnector
                $this->ghostEmailConnector->setKey($message['key']);
                $userid = $this->ghostEmailConnector->getUseridByKey();
                if ($userid) { // success ! its a ghost email connector
                    $this->telegramSendMessage(array(
                        'chat_id' => $userid,
                        'parse_mode' => 'HTML',
                        'disable_web_page_preview' => true
                    ), $message['data']);

                    // now, promoting this ghostConnector to regular emailConnector
                    $this->ghostEmailConnector->setUserid($userid);
                    $name = $this->ghostEmailConnector->getConnectorNameByKey();
                    $this->ghostEmailConnector->deleteConnector();
                    $this->emailConnector->setUserid($userid);
                    $this->emailConnector->newKey($message['key']);
                    $this->emailConnector->saveConnectorNameByKey($name);
                    $this->emailConnector->saveLastData($message['data']);
                    // done
                } else { // boomer
                    trigger_error("not found userid for connector {$message['key']}");
                    return false;
                }
            }
        }

        return true;
    }


    /// UGLY AND BUGGY CODE BELOW
    ///

    protected function telegramSendMessage($msg, $data)
    {
        if (!empty($data['html'])) { // trying to do purification ourselves (building $tail as a result)
            $config = \HTMLPurifier_Config::createDefault();
            $config->set('CSS.AllowedProperties', []);
            $purifier = new \HTMLPurifier($config);
            $randomKey = md5(microtime().rand());
            $this->storage->set($randomKey, $data['html']) ; // saved original message
            $this->storage->expire($randomKey, 3600*12);
            $msg['text'] = $purifier->purify($data['html']); // REPLACING text to be sent here
            $msg['text'] = preg_replace("/<br\W*?\/>/", "\n", $msg['text']);
            $msg['text'] = strip_tags($msg['text'], "<b><a><i><pre><code>");
            $url = $this->domain."/email/{$randomKey}";
            $tail        = "\n...........................................................\n<b>Display in browser</b>: <a href='$url'>{$url}</a> \n(will self-destruct in 12 hours)";
        }

        $original_text = $msg['text'];


        if ($data) {
            $header =
                '<b>' . $data['subject'] . "</b>\n" ;
        }

        if ($data) {
            $footer =
                "\n...........................................................\n" .
                '<b>From</b>: ' . $data['from'][0]['address'] . "\n" .
                '<b>To</b>: ' . $data['to'][0]['address'] ;
        }

        $msg['text'] = (isset($header)?$header:'').$original_text.(isset($footer)?$footer:'').(isset($tail)?$tail:'');


        if (strlen($msg['text']) > 4096) {
            $msg['text'] = substr(
                    (isset($header)?$header:'').$original_text.(isset($footer)?$footer:''),0,4096-1 - strlen($tail)
                ).
                (isset($tail)?$tail:'');
        }

        for ($c=0; $c<5; $c++) {
            $msg['text'] = str_ireplace("  ", " ", $msg['text']);
            $msg['text'] = str_ireplace("  ", " ", $msg['text']);
            $msg['text'] = str_ireplace("  ", " ", $msg['text']);
            $msg['text'] = str_ireplace("  ", " ", $msg['text']);
            $msg['text'] = str_ireplace("  ", " ", $msg['text']);
            $msg['text'] = str_ireplace("\t\t", "\t", $msg['text']);
            $msg['text'] = str_ireplace("\t\t", "\t", $msg['text']);
            $msg['text'] = str_ireplace("\t\t", "\t", $msg['text']);
            $msg['text'] = str_ireplace("\t\t", "\t", $msg['text']);
            $msg['text'] = str_ireplace("\n \n", "\n", $msg['text']);
            $msg['text'] = str_ireplace("\n \n", "\n", $msg['text']);
            $msg['text'] = str_ireplace("\n \n", "\n", $msg['text']);
            $msg['text'] = str_ireplace("\n \n", "\n", $msg['text']);
            $msg['text'] = str_ireplace("\n\t\n", "\n", $msg['text']);
            $msg['text'] = str_ireplace("\n\t\n", "\n", $msg['text']);
            $msg['text'] = str_ireplace("\n\t\n", "\n", $msg['text']);
            $msg['text'] = str_ireplace(PHP_EOL . PHP_EOL . PHP_EOL, PHP_EOL . PHP_EOL, $msg['text']);
            $msg['text'] = str_ireplace(PHP_EOL . PHP_EOL . PHP_EOL, PHP_EOL . PHP_EOL, $msg['text']);
        }

        try {
            return $this->telegram->sendMessage($msg);
        } catch (\Exception $e) {
            trigger_error($e->getCode().':'.$e->getMessage()."\ntrying again without HTML...\n".$msg['text'].PHP_EOL.PHP_EOL);
            $header = strip_tags($header);
            $footer = strip_tags($footer);
            $tail = strip_tags($tail);
            $msg['text'] = strip_tags($msg['text']);
            $msg['text'] = (utf8_encode($msg['text']));
            $msg['text'] = $header.substr($msg['text'], 0, 4096-1 -strlen($tail) - strlen($header) - strlen($footer)) .$footer . $tail;
            unset($msg['parse_mode']);
            try {
                return $this->telegram->sendMessage($msg);
            } catch (\Exception $ee) {
                trigger_error("giving up: ".$ee->getMessage());
                return false;
            }
        }
    }
}
