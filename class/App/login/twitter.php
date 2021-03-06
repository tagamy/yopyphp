<?php

require_once 'MySocials/Twitter.php'; 

class App_login_twitter extends App_login
{

    var $model;
    var $tw;

    function exec() 
    {

        app_common::exec();
        $this->model = $this->connectMaster();
            
        $this->tw = new MySocials_Twitter();

        if ($_REQUEST) {

            $twitter = $this->callback();
            if ($twitter) {
                $this->login($twitter);
            }

        }
        else {
            $this->authorize();
        }
        
        $this->show();
        
    }


    function authorize() 
    {

        $callback = SITE_URL . 'login/twitter';

        try {
            $this->tw->authorize($callback);
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }

    }

    
    function callback() 
    {
        
        try {
            $body = $this->tw->callback();
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
        
        $access_token = $body['oauth_token'];
        $secret_token = $body['oauth_token_secret'];
        $user_id      = $body['user_id'];
        $screen_name  = $body['screen_name'];

        if (empty($access_token) || empty($secret_token)) {
            $this->error  = "アクセストークンを取得できませんでした。";
            return false;
        }

        if (empty($user_id) || empty($screen_name)) {
            $this->error = "アカウント情報を取得できませんでした。";
            return false;
        }

        return $body;

    }


    function login($twitter)
    {        


 
        $user_id = $twitter['user_id'];

        $user = $this->getUser($user_id);
        
        if ($user) {
            //login success
            $_SESSION['login'] = $user;

            $url = "/";
            $this->redirect($url);            

            exit;

        } 

        //check owner
        $oath = $this->checkOath();
        
        if ($oath) {
            $this->error = "すでに他のマスターと契約済みです。";
            return false;
        }
        
        //take oath
        $_SESSION['twitter'] = $twitter;
        $url = "/oath";
        $this->redirect($url);            
        
        exit;
        

    }


    function getUser($user_id) {

        $sql = "SELECT user_id, screen_name, authority FROM user WHERE user_id = ?";
        $row = $this->model->getRow($sql, array($user_id));

        return $row;

    }


    function checkOath() {

        $sql = "SELECT count(*) FROM user";
        $res = $this->model->getOne($sql);

        return $res;

    }


    
}

