<?php

require __DIR__ . '/vendor/autoload.php' ;

\Models\App::init();


$app = function ($request, $response) use ($dispatcher) {

    @list($route, $handler, $vars) = $dispatcher->dispatch($request->getMethod(), $request->getPath());

    switch ($route) {

        case FastRoute\Dispatcher::NOT_FOUND:
            $response->writeHead(404, array('Content-Type' => 'text/html'));
            return $response->end("Not found\n");
            break;

        case FastRoute\Dispatcher::FOUND:
            if ($request->getMethod() == 'POST') { // handling POST data - reading it from stream and parsing it
                $requestBody   = '';
                $headers       = $request->getHeaders();
                $contentLength = (int)$headers['Content-Length'];
                $receivedData  = 0;
                $request->on('data', function($data) use ($request, $response, &$requestBody, &$receivedData, $contentLength, $handler, &$vars) {
                    $requestBody  .= $data;
                    $receivedData += strlen($data);
                    if ($receivedData >= $contentLength) {
                        parse_str($requestBody, $requestData);
                        if (implode('', array_values($requestData)) == '') { // fix for malformed POST
                            $requestData = array('data' => $requestBody);
                        }
                        $vars = array_merge($vars, $requestData);
                        $request->close();
                        $handler($vars, $response);
                    }
                });
            } else { // GET
                $handler($vars, $response);
            }
            break;



        case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $response->writeHead(405, array('Content-Type' => 'text/html'));
            return $response->end();
    }
};


$loop = React\EventLoop\Factory::create();
$socket = new React\Socket\Server($loop);
$http = new React\Http\Server($socket, $loop);


if (in_array('--website', $argv)) {
    $http->on('request', $app);
    $socket->listen($port = \Models\App::getConfig()->listenPort, $interface = '0.0.0.0');
    echo "Listening on {$interface}:{$port}\n";
}


if (in_array('--bot', $argv)) {
    echo \Models\App::getTelegram()->getMe()->getUsername()." on duty.\n";
    $loop->addPeriodicTimer(1, function () {
        $updates = \Models\App::commandsHandler();
        foreach ($updates as $update) {
            \Models\App::statsd('message');
            if (!empty($update) && ($message = $update->getMessage()) && $message->getReplyToMessage()) {
                // this is a special case when user replies to bot, so bot has to give connector a name
                // in group chats every message to bot is a reply to bot, so we will filter it out in command handler
                \Models\App::getTelegram()->getCommandBus()->execute(
                    \Commands\SetconnectornameCommand::name(),
                    $update->getMessage()->getReplyToMessage()->getText() . $update->getMessage()->getText(),
                    $update
                );
            }
        }
    });
}


if (in_array('--email', $argv)) {
    echo "Email queue is being processed.\n";
    $loop->addPeriodicTimer(1, function () {
        $storage = \Models\App::getStorage();
        $telegram = \Models\App::getTelegram();
        $connector = new \Models\Connector($storage);
        $emailConnector = new \Models\EmailConnector($storage);
        $ghostEmailConnector = new \Models\GhostEmailConnector($storage);
        $controller = new \Controllers\MessageController($storage, $telegram, $connector, $emailConnector, $ghostEmailConnector, \Models\App::getConfig()->baseUrl);
        $message = \Models\App::getStorage()->rpop("queue");
        if ($message) {
            if ($controller->run($message)) {
                \Models\App::statsd('email');
            }
        } else {
            print '.';
        }
    });
}






$loop->run();
