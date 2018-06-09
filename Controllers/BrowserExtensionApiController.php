<?php


namespace Controllers;

use Models\DomainSanitizer;

class BrowserExtensionApiController
{

    /** @var  \Models\Storage */
    protected $storage;

    /** @var \Models\GhostEmailConnector */
    protected $ghostEmailConnector;

    /** @var \Models\EmailConnector */
    protected $emailConnector;

    protected $domain;

    public function __construct($storage, $connector, $emailConnector = false)
    {
        $this->storage   = $storage;
        $this->ghostEmailConnector = $connector;
        $this->emailConnector = $emailConnector;
    }

    /**
     * For generated emails
     *
     * @param $domain string our mail domain (for eails we generate for our users)
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }


    public function run($args, $response)
    {
        if (!$this->domain) {
            throw new \Exception('No Domain specified');
        }

        switch ($args['action']) {
            case 'get_auth_status':
                return $this->get_auth_status($args, $response);
                break;

            case 'get_email_for_domain':
                $args['domain'] = DomainSanitizer::sanitize($args['domain']);
                return $this->get_email_for_domain($args, $response);
                break;

            case 'get_email':
                return $this->get_email($args, $response);
                break;
        }

        $response->writeHead(404, array('Content-Type' => 'text/html'));
        return $response->end("Not found\n");
    }

    protected function get_email($args, $response)
    {
        $machineid = $args['machineid'];
        $userid    = $this->storage->get("{$machineid}_userid");

        if (!$userid) { // not found, returning
            $response->writeHead(200, array('Content-Type' => 'application/json', 'Access-Control-Allow-Origin'=> '*'));
            return $response->end(json_encode(['error' => 'Not found']));
        }

        $key = $this->ghostEmailConnector->setUserId($userid)->newKey();

        $response->writeHead(200, array('Content-Type' => 'application/json', 'Access-Control-Allow-Origin'=> '*'));
        return $response->end(json_encode(['ok' => $key.'@'.$this->domain]));
    }

    protected function get_email_for_domain($args, $response)
    {
        $machineid = $args['machineid'];
        $domain    = $args['domain'];
        $userid    = $this->storage->get("{$machineid}_userid");

        if (!$userid) { // not found, returning
            $response->writeHead(200, array('Content-Type' => 'application/json', 'Access-Control-Allow-Origin'=> '*'));
            return $response->end(json_encode(['error' => 'Not found']));
        }

        $key = $this->storage->get("{$userid}_{$domain}");

        if ($key) {
            // what if key exists, but it was deleted by user from emailConnectors..?
            // it will not be valid, lets check for that
            if (!$this->emailConnector) {
                throw new \Exception("Forgot to pass emailConnector to constructor! Cant do shit");
            }
            $this->emailConnector->setKey($key);
            $this->ghostEmailConnector->setKey($key);
            if (!$this->emailConnector->getUseridByKey() && !$this->ghostEmailConnector->getUseridByKey()) {
                // yep, cant lookup user by this connector, lets force key recreation
                $key = false;
            }
        }

        if (!$key) { // no connector key for domain yet, creating
            if (!$this->ghostEmailConnector) {
                throw new \Exception('EmailConnector was not passed to constructor');
            }
            $this->ghostEmailConnector->setUserId($userid);
            $key = $this->ghostEmailConnector->newKey();
            $this->storage->set("{$userid}_{$domain}", $key); // saving
            //now, saving name (aka comment) for this connector
            $this->ghostEmailConnector->setKey($key);
            $this->ghostEmailConnector->saveConnectorNameByKey($domain);
        }

        $response->writeHead(200, array('Content-Type' => 'application/json', 'Access-Control-Allow-Origin'=> '*'));
        return $response->end(json_encode(['ok' => $key.'@'.$this->domain]));
    }

    protected function get_auth_status($args, $response)
    {
        $machineid = $args['machineid'];
        $userid    = $this->storage->get("{$machineid}_userid");

        if (!$userid) { // not found, returning
            $response->writeHead(200, array('Content-Type' => 'application/json', 'Access-Control-Allow-Origin'=> '*'));
            return $response->end(json_encode(['error' => 'Not found']));
        }

        $response->writeHead(200, array('Content-Type' => 'application/json', 'Access-Control-Allow-Origin'=> '*'));
        return $response->end(json_encode(['ok' => $userid]));
    }
}
