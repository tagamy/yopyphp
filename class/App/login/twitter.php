<?php

require_once 'MySocials/Twitter.php'; 

class App_login_twitter extends App_login
{

    var $tw;

    function exec() {

        app_common::exec();
            
        $this->tw = new MySocials_Twitter();

        if ($_REQUEST) {
            $this->callback();
        }
        else {
            $this->authorize();
        }
        
        $this->show();
        
    }


    function authorize() {

        $callback = SITE_URL . 'login/twitter';

        try {
            $this->tw->authorize($callback);
        }
        catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }

    }

    
    function callback() {
        
        try {
            $body = $this->tw->callback();
        }
        catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
        
        $access_token    = $body['oauth_token'];
        $secret_token    = $body['oauth_token_secret'];
        $twitter_user_id = $body['user_id'];
        $screen_name     = $body['screen_name'];

        if (empty($access_token) || empty($secret_token)) {
            $this->error  = "アクセストークンを取得できませんでした。";
            return false;
        }

        if (empty($twitter_user_id) || empty($screen_name)) {
            $this->error = "アカウント情報を取得できませんでした。";
            return false;
        }

        $_SESSION['login']['twitter'] = $body;
 
        $url = "/";
        $this->redirect($url);
        
        exit;
        

    }

    
}


?>