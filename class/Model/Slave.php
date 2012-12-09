<?php

require_once 'Model/Common.php';

class Model_Slave extends Model_Common
{

    var $type = 'slave';
    
    function __construct($dsn) {

        if ($dsn != DSN_MASTER) {
            $this->db_error("You need connect to MASTER.");
            exit;
        }

        parent::__construct($dsn);
        
    }
    
    
}


?>
