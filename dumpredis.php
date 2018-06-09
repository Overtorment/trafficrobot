<?php

require __DIR__ . '/vendor/autoload.php' ;

\Models\App::init();

foreach(array_merge(range(97, 122), range(48, 57)/*, range(65, 90)*/) as $c){
    $keys = \Models\App::getStorage()->keys(chr($c)."*");
    foreach ($keys as $key){
        print $key.'='.json_encode(\Models\App::getStorage()->get($key)).PHP_EOL;
    }
}


