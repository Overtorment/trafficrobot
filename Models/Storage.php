<?php


namespace Models;

class Storage
{

    private $storage;

    public function __construct()
    {
        $this->storage = new \Predis\Client(array(
          "scheme" => \Models\App::getConfig()->redis->scheme,
          "host" => \Models\App::getConfig()->redis->host,
          "port" => \Models\App::getConfig()->redis->port,
          "persistent" => \Models\App::getConfig()->redis->persistent,
          "password" => \Models\App::getConfig()->redis->password
        ));
    }

    public function get($key)
    {
        $got = $this->storage->get($key);
        if ($got) {
            $unserialized = @unserialize($got);
            if ($unserialized !== false) {
                return $unserialized;
            }
        }
        return $got;
    }

    public function set($key, $value)
    {
        return $this->storage->set($key, serialize($value));
    }

    public function expire($key, $expire)
    {
        return $this->storage->expire($key, $expire);
    }

    public function delete($key)
    {
        return $this->storage->del($key);
    }

    public function keys($pattern)
    {
        return $this->storage->keys($pattern);
    }

    public function rpop($queue)
    {
        return json_decode($this->storage->rpop($queue), true);
    }
}
