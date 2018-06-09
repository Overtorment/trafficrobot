<?php



namespace Controllers;


class DisplayEmailController {

    /**
     * @var \Models\Storage
     */
    protected $storage;

    public function __construct($storage){
        $this->storage   = $storage;
    }

    public function run($args, $response){
        $html = $this->storage->get($args['key']);
        if ($html) {
            $html = '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body>' . $html. '</body></html>';
            $response->writeHead(200, array('Content-Type' => 'text/html'));
            return $response->end($html);
        } else {
            $response->writeHead(404, array('Content-Type' => 'text/html'));
            return $response->end("Not found\n");
        }
    }

}

