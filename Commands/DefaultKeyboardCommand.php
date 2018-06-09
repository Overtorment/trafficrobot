<?php

namespace Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

abstract class DefaultKeyboardCommand extends Command
{

    protected $visible = true;

    public function isVisible(){
        return $this->visible;
    }

    public static function name(){
        $obj = new static();
        return $obj->name;
    }

    public function replyWithDefaultKeyboard($arguments){
        $commands = $this->getTelegram()->getCommands();
        $keyboard = [];
        $c = 1;
        foreach ($commands as $name => $command) {
            if(!$command->isVisible()) continue;
            if ($c%2 == 0){
                $postname='';
                if (!empty($command->postName)) $postname = $command->postName;
                $keyboard[count($keyboard)-1][] = '/' . $name . $postname;
            } else {
                $postname='';
                if (!empty($command->postName)) $postname = $command->postName;
                $keyboard[] = ['/' . $name . $postname];
            }
            $c++;
        }

        $reply_markup = $this->getTelegram()->replyKeyboardMarkup([
            'keyboard' => $keyboard,
            'force_reply' => true,
            'resize_keyboard' => true,
            'one_time_keyboard' => false
        ]);

        $arguments['reply_markup'] = $reply_markup;

        $this->replyWithMessage($arguments);
    }



	/**
	* overloads to catch exceptions
	*/
	public function replyWithMessage($arr){
		try {
			$return = parent::replyWithMessage($arr);
		} catch (\Exception $e){
            trigger_error($e->getMessage());
			return false;
		}
		return $return;
	}

    /**
     * @return int The id where the answer should go to. Thats chatid in groups and userid in private chats.
     */
	public function getReplyToId(){
        return $this->getUpdate()->getMessage()->getChat()->getId();
    }
}
