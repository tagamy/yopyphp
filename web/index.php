<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

if (is_readable('/etc/yopyphp/config.php')) {
    require_once '/etc/yopyphp/config.php';
 } else {
    require_once '../conf/config.php';
 }
require_once 'init.php';
require_once 'Control.php';

//URL-Mapping
$query = '?' . $_SERVER['QUERY_STRING'];

$request_uri = $_SERVER['REQUEST_URI'];
$request_uri = str_replace($query, '', $request_uri);

if ($request_uri != "/" && preg_match('/\/$/', $request_uri)) {
    $url = preg_replace('/\/$/', '', $request_uri);
    header("Location: " . $url);
    exit;
 }

$request_uri = preg_replace('/(^\/)|(\/$)/', '', $request_uri);

$request = explode('/', $request_uri);
$mode   = $request[0];
$params = array_slice($request, 1); 

//メンテンナンス時
if ($is_maintenance) {
    $mode = '503';
 }

//CSRF対策
if ($_SERVER['REQUEST_METHOD'] == "POST") {    
    if (isset($_SERVER['HTTP_REFERER']) && !preg_match("/^https?:\/\/{$_SERVER['HTTP_HOST']}/", $_SERVER['HTTP_REFERER'])) {
        $mode = '403';
    } 
 }

$ctrl = new Control();
$app = $ctrl->factory($mode, $params);
$app->exec();
exit;

