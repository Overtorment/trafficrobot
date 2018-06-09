<?php

namespace Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class FlatNewconnectorCommand extends NewconnectorCommand
{
    /** @var string Command Name */
    protected $name = "newconnector";
    protected $visible = false;
}
