<?php

class KVS {
    
    var $conn;

    function __construct($host, $port)
    {        


        if (!extension_loaded('memcache')) {
            throw new Exception("you need memcache.so extension.");
        }

        $memcache = new Memcache;
        $res = @$memcache->connect($host, $port);

        if (!$res) {
            throw new Exception("DB Error - KVS is down.");
        }

        $this->conn = $memcache;

        /*
        if (!extension_loaded('memcached')) {
            throw new Exception("you need memcached.so extension.");
        }

        $memcached = new Memcached;
        $memcached->setOption(Memcached::OPT_COMPRESSION, false);

        $res = $memcached->addServer($host, $port, 100);
        
        if (!$res) {
            throw new Exception("DB Error - KVS is down.");
        }
        
        $this->conn = $memcached;
        */

    }


    function get($id) {
        
        $data = $this->conn->get($id);
        $data = json_decode($data, true);
        //$data = unserialize($data);
        return $data;
        
    }

    
    function set($id, $data) {

        $data = json_encode($data);
        //$data = serialize($data);
        $res = $this->conn->set($id, $data);                
        return $res;

    }



    function delete($id) {
                
        $res = $this->conn->delete($id);        
        return $res;

    }


}


?>
