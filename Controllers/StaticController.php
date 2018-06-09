<?php

namespace Controllers;

Class StaticController {

    public function run($args, $response) {
        $extension = substr($args['filepath'], -3);
        if (!file_exists('static/'.$args['filepath'])){
            $response->writeHead(404, array('Content-Type' => 'text/html'));
            return $response->end('Not found');
        }
        $contents =  file_get_contents('static/'.$args['filepath']);
        $response->writeHead(200, array('Content-Type' => ($extension=='css' || $extension=='txt') ? 'text/css' : 'image/png'));
        return $response->end($contents);
    }

}