<?php

namespace Models;

class BrowserConnection
{

    protected $machineid;
    protected $userid;
    protected $storage;

    public function __construct($storage)
    {
        $this->storage = $storage;
    }

    public function setMachineid($machineid)
    {
        $this->machineid = $machineid;
        return $this;
    }

    public function setUserid($userid)
    {
        $this->userid = $userid;
        return $this;
    }


    public function associate()
    {
        if (!$this->userid) {
            throw new \Exception('Userid not set');
        }
        if (!$this->machineid) {
            throw new \Exception('Machineid not set');
        }

        return $this->storage->set($this->machineid.'_userid', $this->userid);
    }
}
