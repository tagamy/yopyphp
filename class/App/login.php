<?php

require_once 'App/common.php';

class App_login extends App_common {

    function exec()
    {
        
        parent::exec();       

        if (isset($this->params[0])) {
            $mode2 = $this->params[0];
            $this->remap($this->mode, $mode2, $this->params);            
        }

        $this->show();
        
    }
    
}

?>