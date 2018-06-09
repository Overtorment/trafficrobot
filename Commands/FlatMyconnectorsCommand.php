<?php

namespace Commands;

use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

class FlatMyconnectorsCommand extends MyconnectorsCommand
{
    /** @var string Command Name */
    protected $name = "myconnectors";
    protected $visible = false;
}
