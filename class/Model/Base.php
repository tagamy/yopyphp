<?php

class Model_Base {
    
    var $db;
    var $sql_dump = 0;

    function __construct($dsn)
    {
        
        try {
            $conn = new PDO($dsn, DB_USER, DB_PASS, array(PDO::ATTR_PERSISTENT => true));
        } catch (PDOException $e) {
            $this->db_error($e);
        }
        
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->db =& $conn;
        
    }


    function __descruct()
    {
        $this->disconnect();
    }


    function db_error($error) 
    {

        header("HTTP/1.0 500 Internal Server Error");
        
        echo "Sorry..";
        if (DEBUG) {
            echo $error->getMessage();
        }
    
        exit;

    }


    function disconnect() 
    {
        $this->db = null;
    }


    function query($sql, $params = array()) 
    {

        if ($this->sql_dump) {
            $this->dumpsql($sql, $params);
        }

        $sth = $this->db->prepare($sql);
        $res = $sth->execute($params);

        return $res;

    }


    function getOne($sql, $params = array())
    {

        if ($this->sql_dump) {
            $this->dumpsql($sql, $params);
        }
        
        $sth = $this->db->prepare($sql);
        $sth->execute($params);

        $value = $sth->fetchColumn();

        return $value;

    }

    
    function getRow($sql, $params = array())
    {

        if ($this->sql_dump) {
            $this->dumpsql($sql, $params);
        }

        $sth = $this->db->prepare($sql);
        $sth->execute($params);

        $row = $sth->fetch(PDO::FETCH_ASSOC);

        return $row;

    }

    function getCol($sql, $params = array())
    {

        if ($this->sql_dump) {
            $this->dumpsql($sql, $params);
        }


        $sth = $this->db->prepare($sql);
        $sth->execute($params);

        $rows = $sth->fetchAll(PDO::FETCH_COLUMN, 0);

        return $rows;

    }


    function getAll($sql, $params = array())
    {

        if ($this->sql_dump) {
            $this->dumpsql($sql, $params);
        }

        $sth = $this->db->prepare($sql);
        $sth->execute($params);
        
        $rows = $sth->fetchAll(PDO::FETCH_ASSOC);

        return $rows;

    }


    function getAssoc($sql, $params = array())
    {

        if ($this->sql_dump) {
            $this->dumpsql($sql, $params);
        }

        $sth = $this->db->prepare($sql);
        $sth->execute($params);
        
        $rows = $sth->fetchAll(PDO::FETCH_NUM);

        $result = array();

        for ($i = 0; $i < count($rows); $i++) {

            $result[$rows[$i][0]] = $rows[$i][1];
            
        }

        return $result;

    }


    function getLastId()
    {

        $id = $this->db->lastInsertId();

        return $id;

    }


    function quote($val) {
        
        $val = $this->db->quote($val);

        return $val;
        
    }


    function getCache($keyword, $host = MCD1_HOST, $port = MCD1_PORT)
    {
        
        if (!extension_loaded('memcache')) {
            return false;
        }
        
        $memcache = new Memcache;
        $res = $memcache->connect($host, $port);
        
        if (!$res) {
            return false;
        }
        
        $cached = $memcache->get($keyword);
        
        return $cached;
        
    }
    

    function setCache($keyword, $data, $lifetime = 86400, $host = MCD1_HOST, $port = MCD1_PORT)
    {
        
        if (!extension_loaded('memcache')) {
            return false;
        }
        
        $memcache = new Memcache;
        $res = $memcache->connect($host, $port);
        
        if (!$res) {
            return false;
        }

        $res = $memcache->set($keyword, $data, MEMCACHE_COMPRESSED, $lifetime);
        
        return $res;

    }



    function deleteCache($keyword, $host = MCD1_HOST, $port = MCD1_PORT)
    {
        
        if (!extension_loaded('memcache')) {
            return false;
        }
        
        $memcache = new Memcache;
        $res = $memcache->connect($host, $port);
        
        if (!$res) {
            return false;
        }
        
        $res = $memcache->delete($keyword);
        
        return $res;

    }


    function dumpsql($sql, $params)
    {

        echo "<pre>";

        echo $sql . "\n";
        var_dump($params);

        if ($this->sql_dump == 2) {
            $sql = "EXPLAIN " . $sql;
            $sth = $this->db->prepare($sql);
            $sth->execute($params);
            $rows = $sth->fetchAll(PDO::FETCH_ASSOC);            
            var_dump($rows);
        }

        echo "</pre>";
        

    }



}


?>
