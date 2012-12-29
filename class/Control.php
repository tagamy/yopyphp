<?php

class Control
{

    function factory($mode, $params) {

        if (empty($mode)) {
            $mode = "index";
        }
      
        if (!is_file(APP_DIR . "/class/App/{$mode}.php")) {
            $mode = "error";
        }

        $acl = "all";

        include_once "App/{$mode}.php";
        $classname = "App_{$mode}";

        $obj = new $classname;
        $obj->mode = $mode;
        $obj->params = $params;
        $obj->acl  = $acl;

        return $obj;
    
    }
    
}


?>
