<?php

namespace Models;

class UserApi
{
    /** @var \Models\Storage */
    protected $storage;
    protected $storagePrefix = '';
    protected $userid;
    protected $apikey;

    public function __construct($storage)
    {
        $this->storage = $storage;
    }

    /**
     * proxy function for easier prefix management
     *
     * @param $key
     * @param $value
     * @return mixed
     */
    private function storageSet($key, $value)
    {
        return $this->storage->set($this->storagePrefix.$key, $value);
    }

    /**
     * proxy function for easier prefix management
     *
     * @param $key
     * @return mixed
     */
    private function storageGet($key)
    {
        return $this->storage->get($this->storagePrefix.$key);
    }

    public function setApikey($apikey)
    {
        $this->apikey = $apikey;
        return $this;
    }

    public function setUserid($userid)
    {
        $this->userid = $userid;
        return $this;
    }

    /**
     * @return string Random hash
     */
    public function generateRandomKey()
    {
        return chr(rand(97, 122)).md5(microtime().rand());
    }

    public function getUseridByApikey()
    {
        if (!$this->apikey) {
            throw new \Exception('Key not set');
        }

        return $this->storageGet($this->apikey.'_userid');
    }

    public function getApikeyByUserid()
    {
        if (!$this->userid) {
            throw new \Exception('Userid not set');
        }

        $apikey = $this->storageGet($this->userid.'_apikey');

        if (!$apikey) {
            $apikey = $this->generateRandomKey();
            $this->storageSet($this->userid.'_apikey', $apikey);
            $this->storageSet($apikey.'_userid', $this->userid);
        }

        return $apikey;
    }
}
