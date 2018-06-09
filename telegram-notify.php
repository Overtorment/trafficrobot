<?php

require __DIR__ . '/vendor/autoload.php' ;

\Models\App::init();




@assert(count($argv) == 3) or die ("Not enough arguments\n\nUsage:\n\n$ php {$argv[0]} 'some text' userid\n\n");

//print_r($argv);die();


try {
    \Models\App::getTelegram()->sendMessage(array(
        'chat_id' => $argv[2],
        'disable_web_page_preview' => true,
        'text' => $argv[1]
    ));
} catch (\Exception $e){
    print $e->getMessage().PHP_EOL;
}


print "Sent to ".$argv[2].PHP_EOL;

