<?php

require_once 'MySocials/Mixi.php'; 

class App_Login_Mixi extends App_Login
{

    var $mixi;

    function exec() {

        app_common::exec();
        $this->mixi = new MySocials_Mixi();

        if ($_REQUEST) {
            $this->callback();
        }
        else {
            $this->authorize();
        }

        $this->show();
        
    }


    function authorize() {

        try {
            $this->mixi->authorize();
        }
        catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }

    }

    
    function callback() {

        $this->dump($_REQUEST);
        
        try {
            $access_token = $this->mixi->callback();
        }
        catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }

        if (empty($access_token)) {
            $this->error = "アクセストークンを取得できませんでした。";
            return false;
        }


        $profile = $this->mixi->getProfile($access_token);

        if (empty($profile)) {
            $this->error = "プロフィールを取得できませんでした。";
            return false;
        }


        $uid      = $profile['id'];
        $username = $profile['displayName'];

        $_SESSION['login']['mixi'] = $profile;
 
        $url = "/";
        $this->redirect($url);

        exit;        

    }
    
}

?>