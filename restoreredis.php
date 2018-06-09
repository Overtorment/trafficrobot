<?php

require __DIR__ . '/vendor/autoload.php' ;

\Models\App::init();

$file = @$argv[1] or die("No argument\n");

$file = @file($file) or die("Error opening file\n");


foreach ($file as $line) {
    list ($key, $value) = explode('=',$line) ;
    if (empty($value) || stripos($key, "_") === false ) continue;
    print $key.PHP_EOL;
    \Models\App::getStorage()->set($key, json_decode($value));
}

die();