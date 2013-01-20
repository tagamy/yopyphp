<?php

require_once 'App/common.php';

class App_blog extends App_common
{
 
    function exec()
    {
        
        parent::exec();

        $breadcrumbs = array(
                             array(
                                   "title"  => "top",
                                   "link" => "/"
                                   ),
                             array(
                                   "title" => "blog",
                                   "link" => "/blog"
                                   )
                             );

        $this->breadcrumbs = $breadcrumbs;
                             

        $this->show();

    }

}
