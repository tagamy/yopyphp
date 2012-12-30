<?php

require_once 'common.php'; 

class App_oath extends App_common
{

    var $model;

    function exec() 
    {

        parent::exec();
        $this->model = $this->connectMaster();

        
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $oath = $this->getOath();
            if ($oath) {
                $this->setOath($oath);
            }
        }
        else {
            $oath = $this->getOath();
        }

        $this->smarty->assign('oath', $oath);


        $this->show();
        
    }


    function getOath()
    {

        $twitter = $_SESSION['twitter'];

        if (empty($twitter)) {
            $this->error = "認証情報がありません。";
            return false;
        }

        $user_id = $twitter['user_id'];

        $user = $this->getUser($user_id);

        if ($user) {
            $this->error = "すでに契約済みです。";
            return false;
        }

        
        $chk = $this->checkOath();
        
        if ($chk) {
            $this->error = "すでに他のマスターと契約済みです。";
            return false;
        }

        return $twitter;
        

    }



    function setOath($oath) {


        $user_id     = $oath['user_id'];
        $screen_name = $oath['screen_name'];
        $authority   = 1;

        $sql  = "INSERT INTO user (user_id, screen_name, authority, create_on) ";
        $sql .= "VALUES (?, ?, ?, now())";

        $this->model->query($sql, array($user_id, $screen_name, $authority));

        $user = $this->getUser($user_id);

        if (empty($user)) {
            $this->error = "契約に失敗しました。";
            return false;
        }

        $_SESSION['login'] = $user;
        $url = "/";
        $this->redirect($url);

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

