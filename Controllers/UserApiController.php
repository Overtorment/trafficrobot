<?php


namespace Controllers;

class UserApiController
{

    /** @var  \Models\Storage */
    protected $storage;

    /** @var \Models\GhostEmailConnector */
    protected $ghostEmailConnector;

    /** @var \Models\EmailConnector */
    protected $emailConnector;

    /** @var \Models\Connector */
    protected $connector;

    public function __construct(
        \Models\Storage $storage,
        \Models\Connector $connector,
        \Models\EmailConnector $emailConnector = null,
        \Models\GhostEmailConnector $ghostEmailConnector = null,
        \Models\UserApi $userApi = null
    ) {
        $this->storage   = $storage;
        $this->connector = $connector;
        $this->emailConnector = $emailConnector;
        $this->ghostEmailConnector = $ghostEmailConnector;
        $this->userApi = $userApi;
    }

    public function run($args, $response)
    {
        switch ($args['action']) {
            case 'get_last':
                return $this->getLast($args, $response);
                break;
        }

        $response->writeHead(404, array('Content-Type' => 'text/html'));
        return $response->end("Not found\n");
    }

    public function getLast($args, $response)
    {
        $apiKey = $args['apikey'];
        $connectorKey = $args['connectorkey'];

        $this->userApi->setApikey($apiKey);
        $userId = $this->userApi->getUseridByApikey();

        $this->connector->setKey($connectorKey);
        $userId2 = $this->connector->getUseridByKey();

        if ($userId2 == $userId) {
            $data = $this->connector->fetchLastData();
            $response->writeHead(
                200,
                array('Content-Type' => 'application/json', 'Access-Control-Allow-Origin' => '*')
            );
            return $response->end(json_encode($data));
        } else {
            // kinda fallback. what if it is a call for last email?
            return $this->getLastEmail($args, $response);
        }
    }

    public function getLastEmail($args, $response)
    {
        $apiKey = $args['apikey'];
        $connectorKey = $args['connectorkey'];

        $this->userApi->setApikey($apiKey);
        $userId = $this->userApi->getUseridByApikey();

        $this->emailConnector->setKey($connectorKey);
        $userId2 = $this->emailConnector->getUseridByKey();

        if ($userId2 == $userId) {
            $data = $this->emailConnector->fetchLastData();
            $response->writeHead(
                200,
                array('Content-Type' => 'application/json', 'Access-Control-Allow-Origin' => '*')
            );
            return $response->end(json_encode($data));
        } else {
            $response->writeHead(404, array('Content-Type' => 'text/html'));
            return $response->end("Not found\n");
        }
    }
}
