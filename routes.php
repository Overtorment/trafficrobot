<?php

global $dispatcher;

$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {

    $r->addRoute('GET', '/', function ($args, $response) {
        \Models\App::statsd('web.hit');
        \Models\App::statsd('requested.index');
        $args['baseUrl'] = \Models\App::getConfig()->baseUrl;
        ob_start();
        include('templates/index.php');
        $html = ob_get_clean();
        $response->writeHead(200, array('Content-Type' => 'text/html'));
        $response->end($html);
    });




    $r->addRoute('GET', '/static/{filepath}', function ($args, $response) {
        \Models\App::statsd('requested.static');
        \Models\App::statsd('web.hit');
        $controller = new \Controllers\StaticController();
        return $controller->run($args, $response);
    });


    $r->addRoute('GET', '/.well-known/acme-challenge/{filepath}', function ($args, $response) { // letsencrypt
        $contents =  file_get_contents('/usr/share/nginx/html/.well-known/acme-challenge/'.$args['filepath']);
        $response->writeHead(200, array('Content-Type' => 'text/css'));
        return $response->end($contents);
    });


    $r->addRoute('GET', '/{key}', function ($args, $response) {
        \Models\App::statsd('web.hit');
        \Models\App::statsd('requested.key');
        $args['action'] = '/'.$args['key'];
        $args['baseUrl'] = \Models\App::getConfig()->baseUrl;
        ob_start();
        include('templates/postForm.php');
        $html = ob_get_clean();
        $response->writeHead(200, array('Content-Type' => 'text/html'));
        $response->end($html);
    });



    $r->addRoute('GET', '/browser_extension_api/get_auth_status/{machineid}', function ($args, $response) {
        \Models\App::statsd('browserextensionapi.hit');
        $ghostEmailConnector = new \Models\GhostEmailConnector(\Models\App::getStorage());
        $controller = new \Controllers\BrowserExtensionApiController(\Models\App::getStorage(), $ghostEmailConnector);
        $controller->setDomain(\Models\App::getConfig()->mailDomain);
        return $controller->run(array_merge($args, ['action' => 'get_auth_status']), $response);
    });


    $r->addRoute('GET', '/browser_extension_api/get_email_for_domain/{domain}/{machineid}', function ($args, $respons) {
        \Models\App::statsd('browserextensionapi.hit');
        $ghostEmailConnector = new \Models\GhostEmailConnector(\Models\App::getStorage());
        $emailConnector = new \Models\EmailConnector(\Models\App::getStorage());
        $controller = new \Controllers\BrowserExtensionApiController(
            \Models\App::getStorage(),
            $ghostEmailConnector,
            $emailConnector
        );
        $controller->setDomain(\Models\App::getConfig()->mailDomain);
        return $controller->run(array_merge($args, ['action' => 'get_email_for_domain']), $respons);
    });


    $r->addRoute('GET', '/browser_extension_api/get_email/{machineid}', function ($args, $response) {
        \Models\App::statsd('browserextensionapi.hit');
        $ghostEmailConnector = new \Models\GhostEmailConnector(\Models\App::getStorage());
        $controller = new \Controllers\BrowserExtensionApiController(\Models\App::getStorage(), $ghostEmailConnector);
        $controller->setDomain(\Models\App::getConfig()->mailDomain);
        return $controller->run(array_merge($args, ['action' => 'get_email']), $response);
    });


    $r->addRoute('GET', '/email/{key}', function ($args, $response) {
        \Models\App::statsd('web.hit');
        $controller = new \Controllers\DisplayEmailController(\Models\App::getStorage());
        return $controller->run($args, $response);
    });


    $r->addRoute('POST', '/{key}', function ($args, $response) {
        \Models\App::statsd('requested.email');
        \Models\App::statsd('api.hit');
        $response->writeHead(200, array('Content-Type' => 'text/html'));

        if (empty($args['key'])) {
            $return = json_encode(array('error' => 'key is required'));
            return $response->end($return);
        }

        if (empty($args['data'])) {
            $return = json_encode(array('error' => 'data is required'));
            return $response->end($return);
        }

        $conenctor = new Models\Connector(\Models\App::getStorage());
        $userid = $conenctor->setKey($args['key'])->getUseridByKey();

        if (!$userid) {
            $return = json_encode(array('error' => 'no such user'));
            return $response->end($return);
        }

        try {
            $storage = new \Models\Storage();
            $connector = new \Models\Connector($storage);
            $connector->setKey($args['key']);
            $name = $connector->getConnectorNameByKey();
            if ($name) {
                $name = $name.' ';
            }

            $connector->saveLastData($args['data']);

            $telegramResponse = \Models\App::getTelegram()->sendMessage([
                'chat_id' => $userid,
                'disable_web_page_preview' => true,
                'text' => $args['data']." ({$name}[{$args['key']}])"
            ]);
        } catch (\Exception $e) {
                $return =  json_encode(array('error' => $e->getMessage()));
                return $response->end($return);
        }

        $messageId = $telegramResponse ->getMessageId();
        $return =  json_encode(array('ok' => $messageId));
        return $response->end($return);
    });

    $r->addRoute('GET', '/api/{apikey}/get_last/{connectorkey}', function ($args, $respons) {
        \Models\App::statsd('api.hit');
        $ghostEmailConnector = new \Models\GhostEmailConnector(\Models\App::getStorage());
        $emailConnector = new \Models\EmailConnector(\Models\App::getStorage());
        $connector = new \Models\Connector(\Models\App::getStorage());
        $userApi = new \Models\UserApi(\Models\App::getStorage());
        $controller = new \Controllers\UserApiController(
            \Models\App::getStorage(),
            $connector,
            $emailConnector,
            $ghostEmailConnector,
            $userApi
        );
        return $controller->run(array_merge($args, ['action' => 'get_last']), $respons);
    });
});
