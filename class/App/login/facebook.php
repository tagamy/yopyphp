<?php

require_once 'MySocials/Facebook.php'; 

class App_Login_Facebook extends App_Login
{

    var $fb;

    function exec() {

        app_common::exec();
            
        $this->fb = new MySocials_Facebook();

        if ($_REQUEST) {
            $this->callback();
        }
        else {
            $this->authorize();
        }

        $this->show();
        
    }


    function authorize() {

        $callback = SITE_URL . 'login/facebook';

        try {
            $this->fb->authorize($callback);
        }
        catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }

    }

    
    function callback() {
        
        try {
            $access_token = $this->fb->callback();
        }
        catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }

        if (empty($access_token)) {
            $this->error = "アクセストークンを取得できませんでした。";
            return false;
        }


        $profile = $this->fb->getProfile($access_token);

        if (empty($profile)) {
            $this->error = "プロフィールを取得できませんでした。";
            return false;
        }

        $uid      = $profile['id'];
        if (isset($profile['username'])) {
            $username = $profile['username'];
        }
        else {
            $username = $uid;
        }

        $_SESSION['login']['facebook'] = $profile;
 
        $url = "/";
        $this->redirect($url);

        exit;        

    }
    
}

?>