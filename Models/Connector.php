<?php

namespace Models;

class Connector
{

    /**
     * @var \Models\Storage
     */
    protected $storage;
    protected $userid;
    protected $key;
    protected $storagePrefix = '';


    public function __construct($storage)
    {
        $this->storage = $storage;
    }

    public function setUserid($userid)
    {
        $this->userid = $userid;
        return $this;
    }

    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }


    /**
     * proxy function for easier prefix management
     *
     * @param $key
     * @param $expire
     * @return mixed
     */
    private function storageExpire($key, $expire)
    {
        return $this->storage->expire($this->storagePrefix.$key, $expire);
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



    /**
     * proxy function for easier prefix management
     *
     * @param $key
     * @return mixed
     */
    private function storageDelete($key)
    {
        return $this->storage->delete($this->storagePrefix.$key);
    }

    /**
     * @return array Keys associated with userid
     * @throws \Exception
     */
    public function getKeysByUserid()
    {
        if (!$this->userid) {
            throw new \Exception('Userid not set');
        }
        $storage_key  = $this->userid.'_keys';
        $keys = $this->storageGet($storage_key);
        if ($keys == '') {
            return [];
        }
        return explode('|', $keys);
    }

    public function saveKeys($keys)
    {
        if (!$this->userid) {
            throw new \Exception('Userid not set');
        }
        $storage_key  = $this->userid.'_keys';
        $this->storageSet($storage_key, implode('|', $keys));
        foreach ($keys as $key) {
            $this->storageSet($key.'_userid', $this->userid); // for reverse lookup
        }
    }

    /**
     * Saves new key for userid. Key might be randomly generated or passed as argument
     *
     * @param string $key If not set - key will be generated, if set - saved with this name
     * @return string Key name (generated or passed one)
     * @throws \Exception
     */
    public function newKey($key = null)
    {
        if (!$this->userid) {
            throw new \Exception('Userid not set');
        }
        $keys = $this->getKeysByUserid();
        $newKey = $key ? $key : $this->generateRandomKey();

        if (!$keys) {
            $keys = [$newKey];
        } else {
            $keys[] = $newKey;
        }

        $this->saveKeys($keys);
        return $newKey;
    }


    /**
     * @return string Userid in telegram, found via random key assigned on our side
     * @throws \Exception
     */
    public function getUseridByKey()
    {
        if (!$this->key) {
            throw new \Exception('Key not set');
        }
        $storage_key  = $this->key.'_userid';
        return $this->storageGet($storage_key);
    }

    /**
     * @return string Random hash
     */
    public function generateRandomKey()
    {
        return substr(chr(rand(97, 122)).md5(microtime().rand()), 0, 6);
    }

    public function deleteConnector()
    {
        if (!$this->userid) {
            throw new \Exception('Userid not set');
        }
        if (!$this->key) {
            throw new \Exception('Key not set');
        }

        $this->storageDelete($this->key.'_userid');
        $this->storageDelete($this->key.'_name');

        $keys = $this->getKeysByUserid();

        $keys = array_filter($keys, function ($var) {
            return $var!= $this->key;
        });
        $this->saveKeys($keys);
    }

    public function saveConnectorNameByKey($name)
    {
        if (!$this->key) {
            throw new \Exception('Key not set');
        }

        $name = substr($name, 0, 20);
        $this->storageSet($this->key.'_name', $name);
    }


    public function getConnectorNameByKey()
    {
        if (!$this->key) {
            throw new \Exception('Key not set');
        }
        return $this->storageGet($this->key.'_name');
    }

    public function saveLastData($data)
    {
        if (!$this->key) {
            throw new \Exception('Key not set');
        }
        $this->storageSet($this->key.'_last', $data);
        $this->storageExpire($this->key.'_last', 1*3600);
    }

    public function fetchLastData()
    {
        if (!$this->key) {
            throw new \Exception('Key not set');
        }
        return $this->storageGet($this->key.'_last');
    }

    public function getApikeyByUserid()
    {

    }
}
