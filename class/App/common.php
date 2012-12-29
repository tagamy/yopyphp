<?php

  /*
   * App_common.php
   * アプリケーションの共通メソッドはここに記述します。
   */


require_once 'base.php';

class App_common extends App_base
{

    var $smarty;
    var $mode;
    var $params;

    var $login;
    var $acl;
    var $browser;
    var $https;

    var $error;
    var $success;

    function __construct()
    {
        
        parent::__construct();
        
    }


    function exec()
    {
        parent::exec();
    }




    function getPager($base_url, $total, $page, $perpage)
    {

        $maxpage = ceil($total / $perpage);

        $start = (($page - 1) * $perpage) + 1;
        $end   = $start + $perpage - 1;
        
        if ($end > $total) $end = $total;

        if ($maxpage > 10) {
            $startpage = $page - 5;
            $lastpage  = $page + 5;
            
            if ($startpage < 1) {
                $startpage = 1;
                $lastpage = 10;
            }
            
            if ($lastpage > $maxpage) {
                $startpage = $maxpage - 9;
                $lastpage = $maxpage;
            }
        }
        else {
            $startpage = 1;
            $lastpage = $maxpage;
        }

        $prevpage = $page - 1;
        if ($prevpage < 1) $prevpage = 0;
        
        $nextpage = $page + 1;
        if ($nextpage > $maxpage) $nextpage = 0;

        $params = $_GET;

        if (isset($params['page'])) {
            unset($params['page']);
        }
        
        if ($params) {
            $query = "&" . http_build_query($params);
        }
        else {
            $query = '';
        }

        $pager = array(
                       "base_url" => $base_url,
                       "query"    => $query,
                       "total"    => $total,
                       "start"    => $start,
                       "end"      => $end,
                       "perpage"  => $perpage,
                       "page"     => $page,
                       "maxpage"  => $maxpage,
                       "startpage" => $startpage,
                       "lastpage"  => $lastpage,
                       "prevpage"  => $prevpage,
                       "nextpage"  => $nextpage,
                       );

        return $pager;

    }

}
