<?php

/* PEAR */
require_once 'HTTP/Request2.php';

// Facebook (OAuth 2.0)
// http://developers.facebook.com/docs/authentication/
// 

class MySocials_Facebook {
            

    //*******************
    // Facebookでログイン
    //*******************
    
    function authorize() {

        $scope = 'publish_stream,offline_access,user_birthday,email';
        $redirect_uri = SITE_URL . "login/facebook";

        $state = md5(uniqid(rand(), TRUE));
        $_SESSION['facebook']['state'] = $state;

        $url  = "http://www.facebook.com/dialog/oauth/?";
        $url .= "scope=" . $scope . "&";
        $url .= "client_id=" . FB_API_KEY . "&";
        $url .= "redirect_uri=" . rawurlencode($redirect_uri) . "&";
        $url .= "response_type=code&";
        $url .= "state=" . $state;

        $this->redirect($url);

    }


    function callback() {        
        
        //check CSRF
        if ($_REQUEST['state'] != $_SESSION['facebook']['state']) {
            $this->error = "CSRFの疑いがあるため、処理を停止しました。";
            return false;
        }

        //get code
        $code  = $_REQUEST['code'];
        
        if (empty($code)) {
            if (isset($_GET['error'])) {
                Throw new Exception($_GET['error'] . ": " . $_GET['error_description']);
            }
            else {
                Throw new Exception("Facebookと接続できませんでした。");
            }
            return false;
        }
        
        //get access token
        $redirect_uri = SITE_URL . "login/facebook";

        $api  = "https://graph.facebook.com/oauth/access_token?";
        $api .= "client_id=" . FB_API_KEY . "&";
        $api .= "redirect_uri=" . $redirect_uri . "&";
        $api .= "client_secret=" . FB_SECRET_KEY . "&";
        $api .= "code=" . $code;
        
        $token = file_get_contents($api);
        
        if (preg_match('/access_token=(.*)/', $token, $matches)) {
            $access_token = $matches[1];
        }
        else {
            $access_token = '';
        }
        
        if (empty($access_token)) {
            Throw new Exception("アクセストークンを取得できませんでした。");
        }
        
        return $access_token;

    }
        

    function getProfile($access_token) {

        if (empty($access_token)) {
            Throw new Exception('$access_token is null.');
        }

        //function get profile
        $api = "https://graph.facebook.com/me?access_token=" . $access_token;
        $json = file_get_contents($api);
        
        if (empty($json)) {
            Throw new Exception("Facebookプロフィールにアクセスできませんでした。");
        }
        
        $data = json_decode($json, true);

        if (empty($data)) {
            Throw new Exception("Facebookプロフィールを取得できませんでした。");
        }

        return $data;
                
    }


    function redirect($url) {
        header('Location: ' . $url);
        exit;
    }

    
    
}

?>