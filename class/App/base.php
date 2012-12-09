<?php

  /*
   * App_base 
   * フレームワークの基底クラス
   */

require_once 'Smarty/libs/Smarty.class.php';

class App_base {

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

    function __construct() {
        
        set_exception_handler(array($this, 'catchException'));

        if (!isset($this->smarty)) {
            $smarty = new Smarty();
            $smarty->template_dir = TEMPLATE_DIR;
            $smarty->compile_dir  = COMPILE_DIR;
            $smarty->loadFilter("variable", "htmlspecialchars");

            $this->smarty = $smarty;
            $this->smarty->registerPlugin("modifier", "strim", array($this, "strimText"));

        }        

        /*
        if (!isset($this->browser)) {
            $this->browser = $this->getBrowser();
        }
        */

        /*
        if (!isset($this->bot)) {
            $this->bot     = $this->getBot();
        }
        */

        /*
        if (DEVICE == "mobile") {
            if (isset($_GET)) {
                mb_convert_variables('UTF-8', 'SJIS-win', $_GET);
            }
            if (isset($_POST)) {
                mb_convert_variables('UTF-8', 'SJIS-win', $_POST);
            }
        }
        */
        
    }
    
    
    function exec() {
        
        $this->checkLogin();
        $this->checkAcl();
        //$this->checkSSL();
        
    }
    
    
    function get() {
        
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
    
    
    function show() {        

        $html = $this->get();
        
        if (DEVICE == "mobile") {
            $html = mb_convert_encoding($html, 'SJIS-win', 'UTF-8');
            //header("Content-Type: application/xhtml+xml; charset=Shift-JIS");
            header("Content-Type: text/html; charset=Shift-JIS");
        }
        else {
            header("Content-Type: text/html; charset=utf-8");
        }

        echo $html;
        
    }



    function getBreadcrumbs() {
        
        $list = array();

        return $list;
        
    }




    function catchException($e) {
        

        if (DEBUG) {
            $this->smarty->assign('error', $e->getMessage());
            $this->smarty->assign('trace', $e->getTraceAsString());

        }


        header("HTTP/1.0 500 internal Server Error");
        header("Content-Type: text/plain; charset=utf-8");
        echo "ERROR: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString();
        exit;
        

    }


    function connectMASTER() {
        
        require_once 'Model/Master.php';
        $model = new Model_Master(DSN_MASTER);
        
        return $model;
        
    }


    function connectSLAVE() {
        
        require_once 'Model/Slave.php';
        $dsn = DSN_SLAVE1;

        $model = new Model_Slave($dsn);
        
        return $model;
        
    }
    

    function connectKVS() {

        require_once 'KVS.php';
        $kvs = new KVS(KVS1_HOST, KVS1_PORT);

        return $kvs;

    }

    
    function checkLogin() {

        if (isset($_SESSION['login'])) {
            $this->login = $_SESSION['login'];
        }
        /*
        elseif (isset($_COOKIE['bid'])) {
            $this->login = $this->authLogin();
        }
        */
        else {            
            $this->login = false;
        }

    }
    
    
    function authLogin() {

        $bid = $_COOKIE['bid'];

        if (empty ($bid)) {
            return false;
        }

        require_once 'Crypt/Blowfish.php';
        $bf = new Crypt_Blowfish(SECRET_KEY);

        if (PEAR::isError($bf)) {
            if (DEBUG) {
                echo $bf->getMessage();
            }
            return false;
        }

        $bid = base64_decode($bid);
        $bid = $bf->decrypt($bid);
        $row = explode(":", $bid);

        $account       = $row[0];
        $password_sha1 = $row[1];
        $ip            = $row[2];
        $timestamp     = $row[3];

        if (empty($account) || empty($password_sha1) || empty($ip)) {
            return false;
        }

        /*
        if ($ip != $_SERVER['REMOTE_ADDR']) {
            return false;
        }
        */

        $this->connectMaster();
        
        $sql = "SELECT user_id, plan_id FROM user WHERE account = ? AND password = ? AND state = 1";
        $user = $this->model->getRow($sql, array($account, $password_sha1));
        
        if (empty($user)) {
            return false;
        }
        
        $user_id = $user['user_id'];
        $plan_id = $user['plan_id'];

        $sql = "SELECT shelf_id FROM shelf WHERE user_id = ? AND priority = 1";
        $shelf_id = $this->model->getOne($sql, array($user_id));

        $sql = "UPDATE user SET last_login = now() WHERE user_id = ?";
        $this->model->query($sql, array($user_id));

        $this->model->disconnect();


        $login['user_id']  = $user_id;
        $login['account']  = $account;
        $login['shelf_id'] = $shelf_id;
        $login['plan_id']  = $plan_id;

        $_SESSION['login'] = $login;


        return $login;

    }


    function checkAcl() {

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
        case "admin":
            if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
                header("WWW-Authenticate: Basic realm=\"Booklog Realm\"");
                header("HTTP/1.0 401 Unauthorized");
                exit;
            }
            if ($_SERVER['PHP_AUTH_USER'] != "booklog" || $_SERVER['PHP_AUTH_PW'] != "paperb0y") {
                header("WWW-Authenticate: Basic realm=\"Booklog Realm\"");
                header("HTTP/1.0 401 Unauthorized");
                exit;                
            }
            break;

        }

    }


    function checkSSL() {

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


    function getBrowser() {

        $is_mobile = 0;
        
        $agent = $_SERVER['HTTP_USER_AGENT']; 
        if(preg_match("/^DoCoMo/", $agent)){
            $is_mobile = 1;
        }elseif(preg_match("/^J-PHONE|^Vodafone|^SoftBank/", $agent)){
            $is_mobile = 1;
        }elseif(preg_match("/^UP.Browser|^KDDI/", $agent)){
            $is_mobile = 1;
        }else if(preg_match("/WILLCOM/", $agent)){
            $is_mobile = 1;
        }else if(preg_match("/^PDXGW/", $agent)){
            $is_mobile = 1;
        }else if(preg_match("/DDIPOCKET/", $agent)){
            $is_mobile = 1;
        }else{
            $is_mobile = 0;
        }


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


    function getBot() {

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
        if(preg_match("/emBot-GalaBuzz/", $agent)){
            $bot = 'galabuzz';
            $this->showHeader(503);
            exit;
        }

        return $bot;

    }


    function redirect($url) {
        header('Location: ' . $url);
        exit;
    }


    function showError($status = '') {
        
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


    function showHeader($status) {
        
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


    //都道府県一覧を取得
    function getPrefs() {

        $rows = array(
                      1 => "北海道",
                      2 => "青森県",
                      3 => "岩手県",
                      4 => "宮城県",
                      5 => "秋田県",
                      6 => "山形県",
                      7 => "福島県",
                      8 => "茨城県",
                      9 => "栃木県",
                      10 => "群馬県",
                      11 => "埼玉県",
                      12 => "千葉県",
                      13 => "東京都",
                      14 => "神奈川県",
                      15 => "新潟県",
                      16 => "富山県",
                      17 => "石川県",
                      18 => "福井県",
                      19 => "山梨県",
                      20 => "長野県",
                      21 => "岐阜県",
                      22 => "静岡県",
                      23 => "愛知県",
                      24 => "三重県",
                      25 => "滋賀県",
                      26 => "京都府",
                      27 => "大阪府",
                      28 => "兵庫県",
                      29 => "奈良県",
                      30 => "和歌山県",
                      31 => "鳥取県",
                      32 => "島根県",
                      33 => "岡山県",
                      34 => "広島県",
                      35 => "山口県",
                      36 => "徳島県",
                      37 => "香川県",
                      38 => "愛媛県",
                      39 => "高知県",
                      40 => "福岡県",
                      41 => "佐賀県",
                      42 => "長崎県",
                      43 => "熊本県",
                      44 => "大分県",
                      45 => "宮崎県",
                      46 => "鹿児島県",
                      47 => "沖縄県",
                      99 => "海外",
                      );


        return $rows;
        
    }


    function getPrefName($pref_id) {

        $prefs = $this->getPrefs();

        if (isset($prefs[$pref_id])) {
            return $prefs[$pref_id];
        }

        return "";

    }


    function getGenderName($id) {

        $name = "";

        if ($id == 1) {
            $name = "男性";
        }        
        
        if ($id == 2) {
            $name = "女性";
        }        
        
        return $name;
        
    }


    function formatBirth($birth) {

        if (empty($birth)) {
            return '';
        }

        $row = explode("-", $birth);
        $y = (int)$row[0];
        $m = (int)$row[1];
        $d = (int)$row[2];

        $name = "";
        if ($y) {
            $name .= "{$y}年";
        }
        if ($m) {
            $name .= "{$m}月";
        }
        if ($d) {
            $name .= "{$d}日";
        }

        if ($y) {
            $date1 = new Datetime();
            $date2 = new Datetime($birth);
            $interval = $date1->diff($date2);
            $age = $interval->format('%y');
            
            if ($age) {
                $name .= "({$age}歳)";
            }
            
        }

        return $name;

    }



}

?>