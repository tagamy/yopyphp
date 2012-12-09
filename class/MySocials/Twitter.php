<?php

/* PEAR */
//mixi Facebookログインで利用します
require_once 'HTTP/Request2.php';

//Twitterログインで利用します
require_once 'HTTP/OAuth/Consumer.php';

/* documents */
// Twitter (OAuth 1.0)
// https://dev.twitter.com/docs/auth/sign-in-with-twitter
// https://dev.twitter.com/docs/auth/oauth

// Facebook (OAuth 2.0)
// http://developers.facebook.com/docs/authentication/
// 

// mixi (OAuth 2.0)
// https://dev.twitter.com/docs/auth/oauth
// http://developer.mixi.co.jp/connect/mixi_graph_api/mixi_io_spec_top/people-api/

class MySocials_Twitter {
            
    //******************
    // twitterでログイン
    //******************
    
    function authorize($callback) {
        
        try {
            
            if (!defined('TWITTER_CONSUMER_KEY') || TWITTER_CONSUMER_KEY == '') {
                throw new Exception("TWITTER_CONSUMER_KEYを指定してください。");
            }

            if (!defined('TWITTER_CONSUMER_SECRET') || TWITTER_CONSUMER_SECRET == '') {
                throw new Exception("TWITTER_CONSUMER_SECRETを指定してください。");
            }
            
            $consumer = new HTTP_OAuth_Consumer(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET);            
            $consumer->getRequestToken('http://twitter.com/oauth/request_token', $callback);
            
            $token        = $consumer->getToken();
            $token_secret = $consumer->getTokenSecret();

            if (empty($token) || empty($token_secret)) {
                throw new Exception("トークンを取得できませんでした。");
            }

            $_SESSION['twitter']['token']        = $token;
            $_SESSION['twitter']['token_secret'] = $token_secret;

            $url = 'http://twitter.com/oauth/authorize?oauth_token=' . $token;
            //$url = 'http://twitter.com/oauth/authenticate?oauth_token=' . $token;
            $this->redirect($url);
            exit;            

        } catch (Exception $e) {
            throw $e;
        }

    }


    function callback() {
        
        $token    = $_REQUEST['oauth_token'];
        $verifier = $_REQUEST['oauth_verifier'];

        if (empty($token) || empty($verifier)) {
            throw new Exception("コールバックに失敗しました。");
        }

        if ($token != $_SESSION['twitter']['token']) {
            throw new Exception("トークンが一致しません。");
        }
        
        $consumer = new HTTP_OAuth_Consumer(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, $_SESSION['twitter']['token'], $_SESSION['twitter']['token_secret']);
        $token = $consumer->getAccessToken('http://twitter.com/oauth/access_token', $verifier);
  
        $response = $consumer->getLastResponse();
        $body     = $response->getDataFromBody();

        if (empty($body)) {            
            throw new Exception("レスポンスを取得できませんでした。");
        }
       
        return $body;
 

    }


    function redirect($url) {
        header('Location: ' . $url);
        exit;
    }
    
}

?>