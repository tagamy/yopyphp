<?php

  /*
   * App_base 
   * フレームワークの基底クラス
   */

require_once 'Smarty/libs/Smarty.class.php';

class App_base
{

    var $smarty;
    var $kvs;
    var $model;

    var $mode;
    var $params;

    var $login;
    var $acl;
    var $browser;
    var $https;

    var $javascripts;
    var $css_version;
    var $js_version;

    var $error;
    var $success;
    var $debug;

    function __construct()
    {
        
        set_exception_handler(array($this, 'catchException'));

        if (!isset($this->smarty)) {
            $smarty = new Smarty();
            $smarty->template_dir = TEMPLATE_DIR;
            $smarty->compile_dir  = COMPILE_DIR;
            //$smarty->registerFilter("variable", array($this, 'variablefilter_escape'));

            $this->smarty = $smarty;
            $this->smarty->registerPlugin("modifier", "strim", array($this, "strimText"));

        }        
        
    }

    function variablefilter_escape($value, $smarty)
    {
        if (is_string($value)) {
            $value = htmlspecialchars($value);
        }
        
        return $value;
    }
    
    
    function exec()
    {
        
        $this->checkLogin();
        $this->checkAcl();
        //$this->checkSSL();
        
    }
    
    
    function get()
    {
        
        $tpl = str_replace('_', '/', $this->mode) . '.tpl';


        $this->smarty->assign('mode',        $this->mode);
        $this->smarty->assign('params',      $this->params);
        $this->smarty->assign('login',       $this->login);
        $this->smarty->assign('acl',         $this->acl);
        $this->smarty->assign('browser',     $this->browser);
        $this->smarty->assign('hostname',    $_SERVER['HTTP_HOST']);
        $this->smarty->assign('https',       $this->https);

        $this->smarty->assign('error',   $this->error);
        $this->smarty->assign('success', $this->success);
        $this->smarty->assign('debug',   $this->debug);


        $breadcrumbs = $this->getBreadcrumbs();
        $this->smarty->assign('breadcrumbs', $breadcrumbs);

        $html = $this->smarty->fetch($tpl);

        return $html;
        
    }
    
    
    function show()
    {        

        $html = $this->get();
        
        header("Content-Type: text/html; charset=utf-8");
        echo $html;
        
    }



    function getBreadcrumbs()
    {
        
        $list = array();

        return $list;
        
    }




    function catchException($e) 
    {
        

        header("HTTP/1.0 500 internal Server Error");
        header("Content-Type: text/plain; charset=utf-8");
        echo "ERROR: " . $e->getMessage() . "\n";
        if (DEBUG) {
            echo $e->getTraceAsString();
        }

        exit;        

    }


    function connectMaster()
    {
        
        require_once 'Model/Master.php';
        $model = new Model_Master(DSN_MASTER);
        
        return $model;
        
    }


    function connectSlave() {
        
        require_once 'Model/Slave.php';
        $dsn = DSN_SLAVE1;

        $model = new Model_Slave($dsn);
        
        return $model;
        
    }
    

    function connectKVS()
    {

        require_once 'KVS.php';
        $kvs = new KVS(KVS1_HOST, KVS1_PORT);

        return $kvs;

    }

    
    function checkLogin()
    {

        if (isset($_SESSION['login'])) {
            $this->login = $_SESSION['login'];
        }
        else {            
            $this->login = false;
        }

    }
    
   
    function checkAcl()
    {

        switch ($this->acl) {
        case "all":
            break;
        case "user":
            if (empty($this->login['user_id'])) {

                $url = '/login';
                
                $ref = base64_encode($_SERVER['REQUEST_URI']);
                $url .= "?ref=" . urlencode($ref);


                $this->redirect($url);
                exit;
            }
            break;   
        default:
            break;
        }
            

    }


    function checkSSL()
    {

        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {

            $this->https = 1;
            
            if ($this->ssl == 0) {
                $url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                $this->redirect($url);
                exit;
            } 
            
        }
        else {
            
            $this->https = 0;
            
            if (DEBUG) {
                return true;
            }

            if ($this->ssl == 1) {
                $url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                $this->redirect($url);
                exit;
            } 
        }

    }


    function getBrowser()
    {

        require_once 'Net/UserAgent/Detect.php';

        $browserSearch = array('ie8up', 'firefox', 'chrome', 'safari', 'opera9up');
        $browser = Net_UserAgent_Detect::getBrowser($browserSearch);

        if (preg_match("/iPhone/", $agent)) {
            $browser = "iphone";
        }

        if (preg_match("/Android/", $agent)) {
            if (preg_match("/Mobile/", $agent)) { 
                $browser = "android";
            }
            else {
                $browser = "android-tablet";
            }
        }
                
        return $browser;

    }


    function getBot()
    {

        $bot = '';
        
        $agent = $_SERVER['HTTP_USER_AGENT']; 

        if(preg_match("/Slurp/", $agent)){
            $bot = 'yahoo';
        }
        if(preg_match("/J-BSC/", $agent)){
            $bot = 'yahoo-blog';
        }
        if(preg_match("/Googlebot/", $agent)){
            $bot = 'google';
        }
        if(preg_match("/msnbot/", $agent)){
            $bot = 'msn';
        }
        if(preg_match("/Yeti/", $agent)){
            $bot = 'nhn';
        }
        if(preg_match("/baidu/", $agent)){
            $bot = 'baidu';
        }
        if(preg_match("/bingbot/", $agent)){
            $bot = 'bing';
        }
        if(preg_match("/Hatena/", $agent)){
            $bot = 'hatena';
        }

        return $bot;

    }


    function redirect($url)
    {
        header('Location: ' . $url);
        exit;
    }


    function showError($status = '')
    {
        
        switch ($status) {
        case "404":
            header("HTTP/1.0 404 Not Found");
            $error_status = "404 Not Found";
            break;
        case "403":
            header("HTTP/1.0 403 Forbidden");
            $error_status = "403 Forbidden";
            break;
        case "503":
            header("HTTP/1.0 503 Service Unavailable");
            $error_status = "503 Service Unavailable";
            break;
        default:
            header("HTTP/1.0 400 Bad Request");
            $error_status = "400 Bad Request";
            break;
        }

        $this->mode = 'error';
        $this->smarty->assign('error_status', $error_status);
        $this->show();
        exit;
    }


    function showHeader($status)
    {
        
        switch ($status) {
        case "200":
            header("HTTP/1.0 200 OK");
            break;
        case "404":
            header("HTTP/1.0 404 Not Found");
            break;
        case "403":
            header("HTTP/1.0 403 Forbidden");
            break;
        case "503":
            header("HTTP/1.0 503 Service Unavailable");
            break;
        default:
            header("HTTP/1.0 400 Bad Request");
            break;
        }

    }


    
    function formatText($text, $linebreak = false) {
        
        $text = htmlspecialchars($text);
        $text = $this->addLink($text);
        
        if ($linebreak) {
            $text = nl2br($text);
        }

        return $text;
        
    }


    function strimText($text, $len = 25) {

        $text = mb_strimwidth($text, 0, $len, '...', 'UTF-8');

        return $text;

    }


    function addLink($text) {

        $pattern = '/(https?)(:\/\/[[:alnum:]+$;?.%,!#~*\/:@&=_-]+)/';
        $replace = '<a href="\\0" target="_blank">\\0</a>';
        
        $text = preg_replace($pattern, $replace, $text);
        return $text;

    }
    

    function dump($var) {

        $this->debug .= var_export($var, true) . "\n";

    }


    //実行モードを子クラスに書き換える
    function remap($mode, $mode2, $params) {

        if (!is_file(APP_DIR . "/class/App/{$mode}/{$mode2}.php")) {
            $this->showError(404);
            exit;
        }

        include_once "App/{$mode}/{$mode2}.php";
        $classname = "App_{$mode}_{$mode2}";

        $obj = new $classname;
        $obj->mode = "{$mode}_{$mode2}";
        $obj->params = $params;
        $obj->acl = $this->acl;
        $obj->exec();
        exit;

    }


    /* validator */
    function validateEmail($email) {
        
        if (empty($email)) {
            return false;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        return true;

    }



}

