<?php

require_once 'App/common.php';

class App_logout extends App_common {

    function exec()
    {
        
        parent::exec();       
        $this->logout();
        
    }
    
    
    function logout()
    {
        
        unset($_SESSION['login']);
        
        $url = "/";
        $this->redirect($url);
        exit;
 
        
    }




}

?>