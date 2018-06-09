<?php

namespace Models;

class App
{

    /**
     * @var \Models\Storage
     */
    protected static $storage;

    /**
     * @var \Telegram\Bot\Api
     */
    protected static $telegram;

    /**
     * @var array
     */
    protected static $config;

    protected function __construct()
    {
    }

    public static function init()
    {
        self::$config = json_decode(file_get_contents("config.json"), false);
        if (\Models\App::getConfig()->listenPort != 80) {
            \Models\App::getConfig()->baseUrl .= ':80';
        }

        self::$storage = new \Models\Storage();
        self::$telegram = new \Telegram\Bot\Api(\Models\App::getConfig()->telegramKey);
        self::getTelegram() ->addCommand(\Commands\StartCommand::class);
        self::getTelegram() ->addCommand(\Commands\MyconnectorsCommand::class);
        self::getTelegram() ->addCommand(\Commands\DeleteconnectorCommand::class);
        self::getTelegram() ->addCommand(\Commands\NewconnectorCommand::class);
        self::getTelegram() ->addCommand(\Commands\SetconnectornameCommand::class);
        self::getTelegram() ->addCommand(\Commands\CancelCommand::class);
        self::getTelegram() ->addCommand(\Commands\NewemailconnectorCommand::class);
        self::getTelegram() ->addCommand(\Commands\DeleteCommand::class);
        self::getTelegram() ->addCommand(\Commands\AboutCommand::class);
        self::getTelegram() ->addCommand(\Commands\GetapikeyCommand::class);
        self::getTelegram() ->addCommand(\Commands\FlatDeleteconnectorCommand::class);
        self::getTelegram() ->addCommand(\Commands\FlatMyconnectorsCommand::class);
        self::getTelegram() ->addCommand(\Commands\FlatNewconnectorCommand::class);
        self::getTelegram() ->addCommand(\Commands\AskconnectornameCommand::class);
        self::getTelegram() ->addCommand(\Commands\Debug1337::class);

        set_error_handler(function($errno, $errstr, $errfile, $errline){
            if (!(error_reporting() & $errno)) {
                // This error code is not included in error_reporting, so let it fall
                // through to the standard PHP error handler
                return false;
            }

            print "Cought {$errstr} on {$errfile}:{$errline}".PHP_EOL."Call stack:".PHP_EOL;
            if(function_exists('debug_backtrace')){
                $backtrace = debug_backtrace();
                array_shift($backtrace);
                foreach($backtrace as $i=>$l){
                    if(!empty($l['file'])) print " in {$l['file']}";
                    if(!empty($l['line'])) print " on line {$l['line']}";
                    print "\n";
                }
            }
        });
    }


    /**
     * @return mixed Config array
     */
    public static function getConfig()
    {
        return self::$config;
    }


    /**
     * @return \Models\Storage
     */
    public static function getStorage()
    {
        return self::$storage;
    }

    /**
     * @return \Telegram\Bot\Api
     */
    public static function getTelegram()
    {
        return self::$telegram;
    }

    /**
     * Processes Inbound Commands
     * Copied & simplified from from \Telegram\Bot\Api
     * because we need long polling (not enabled by default)
     *
     * @return \Telegram\Bot\Objects\Update|\Telegram\Bot\Objects\Update[]
     */
    public static function commandsHandler()
    {
        $updates = \Models\App::getTelegram()->getUpdates(['timeout' => 10, 'limit' => 10]);
        $highestId = -1;

        foreach ($updates as $update) {
            $highestId = $update->getUpdateId();
            $message = $update->getMessage();
            if ($message !== null && $message->has('text')) {
                \Models\App::getTelegram()->getCommandBus()->handler($message->getText(), $update);
            }
        }

        //An update is considered confirmed as soon as getUpdates is called with an offset higher than its update_id.
        if ($highestId != -1) {
            $params = [];
            $params['offset'] = $highestId + 1;
            $params['limit'] = 1;
            \Models\App::getTelegram()->getUpdates($params);
        }

        return $updates;
    }

    public static function statsd($key, $value = 1, $type = 'c')
    {
        $host = \Models\App::getConfig()->statsd->host;
        if (!$host) {
            return false;
        }
        $fp = fsockopen("udp://$host", 8125);
        if (!$fp) {
            return false;
        }

        fwrite($fp, $m =  "trafficrobot.{$key}:{$value}|$type");
        fclose($fp);
        return true;
    }
}
