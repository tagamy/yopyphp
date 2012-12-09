<?php

/* PEAR */
require_once 'HTTP/Request2.php';

/* documents */
// mixi (OAuth 2.0)
// https://dev.twitter.com/docs/auth/oauth
// http://developer.mixi.co.jp/connect/mixi_graph_api/mixi_io_spec_top/people-api/

class MySocials_Mixi {

    //***************
    // mixiでログイン     
    //***************

    function authorize() {
        
        //mixi
        $scope = "r_profile w_voice w_share r_profile_gender r_profile_birthday";
        
        $url  = "https://mixi.jp/connect_authorize.pl";
        $url .= "?client_id=" . MIXI_CONSUMER_KEY;
        $url .= "&response_type=code";
        $url .= "&scope=" . $scope;
        
        $this->redirect($url);
        
    }


    function callback() {

        $code = $_GET['code'];

        if (empty($code)) {
            Throw new Exception("リクエストが不正です。");
        }

        $token = $this->getToken($code);

        if (empty($token)) {
            Throw new Exception("mixiからアクセストークンを取得できませんでした。");
        }

        return $token;

                
    }


    function getToken($code) {

        if (empty($code)) {
            return false;
        }

        $url  = "https://secure.mixi-platform.com/2/token";

        $params = array(
                        "grant_type"    => "authorization_code",
                        "client_id"     => MIXI_CONSUMER_KEY,
                        "client_secret" => MIXI_CONSUMER_SECRET,
                        "code"          => $code,
                        "redirect_uri"  => 'http://' . $_SERVER['HTTP_HOST'] . '/login/mixi',
                        );

        $request = new HTTP_Request2($url, HTTP_Request2::METHOD_POST);
        $request->addPostParameter($params);
        $request->setConfig('ssl_verify_peer', false);

        try {
            $response = $request->send();
            if (200 != $response->getStatus()) {
                $this->error = "mixiに接続できませんでした(HTTP:" . $response->getStatus() . ")";
                return false;
            }
            
            $json = $response->getBody();

            if (empty($json)) {
                $this->error = "トークンを取得できませんでした。";
                return false;
            }            
            
            $token = json_decode($json, true);
            $token['create_on'] = time();
            $token['scopes'] = explode(" ", $token['scope']);

            return $token;


        } catch (HTTP_Request2_Exception $e) {
            $this->errors[] = "mixiに接続できませんでした({$e})";
            return false;
        }
         
    }
        
    
    function getProfile($token) {
        
        $access_token = $token['access_token'];

        $url  = "http://api.mixi-platform.com/2/people/@me/@self?fields=@all";

        $request = new HTTP_Request2($url, HTTP_Request2::METHOD_GET);
        $request->setHeader(array(
                                  'Authorization' => "OAuth {$access_token}"
                                  ));
        
        try {

            $response = $request->send();
            
            if (200 != $response->getStatus()) {
                $this->errors[] = "People APIに接続できませんでした(HTTP:" . $response->getStatus() . ")";
                return false;
            }
            
            
            $json = $response->getBody();
            
            if (empty($json)) {
                $this->errors[] = "プロフィールを取得できませんでした。";
                return false;
            }

            $data = json_decode($json, true);
            $profile = $data['entry'];
            
            return $profile;
            
        } catch (HTTP_Request2_Exception $e) {
            $this->errors[] = "People APIに接続できませんでした({$e})";
            return false;
        }
        
    }
    
  
    function redirect($url) {
        header('Location: ' . $url);
        exit;
    }


  
}

?>