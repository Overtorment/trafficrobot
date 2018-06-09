<?php

namespace Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class FlatDeleteconnectorCommand extends DeleteconnectorCommand
{
    /** @var string Command Name */
    protected $name = "deleteconnector";
    protected $visible = false;
}
