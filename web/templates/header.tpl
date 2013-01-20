<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>yopyphp</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<link rel="stylesheet" href="/assets/bootstrap/css/bootstrap.min.css" type="text/css">
<style type="text/css">
body { 
    padding-top: 60px;
    padding-bottom: 40px;
}
</style>
<link rel="stylesheet" href="/assets/bootstrap/css/bootstrap-responsive.min.css" type="text/css"> 
<link rel="stylesheet" href="/assets/yopyphp/css/style.css" type="text/css">
</head>
<body>

<div class="navbar navbar-fixed-top">
<div class="navbar-inner">
<div class="container">
  <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
  <span class="icon-bar"></span>
  <span class="icon-bar"></span>
  <span class="icon-bar"></span>
  </a>
  <a href="/" class="brand">yopyphp</a>
<div class="nav-collapse">
  <ul class="nav">
    <li><a href="/blog">blog</a></li>
  </ul>  
  <ul class="nav pull-right">
    {if $login}  
    <li>
    <a href="http://twitter.com/{$login.screen_name}" target="_blank">
    <img src="http://api.twitter.com/1/users/profile_image?screen_name={$login.screen_name}&size=mini" style="vertical-align:middle;">
    @{$login.screen_name}
    </a>
    </li>
    <li>
    <a href="/logout">logout</a>
    </li>
    {else}
    <li><a href="/login/twitter">login</a></li>
    {/if}  
  </ul>
</div>
</div>

</div>
</div>


<div class="container">

<div class="row">
<div class="span16">

{if $error}
<div class="alert-message block-message error">
  <a class="close" href="#" onclick="$('.alert-message').hide();">×</a>
  <p>{$error}</p>
</div>
{/if}
{if $success}
<div class="alert-message block-message info">
  <a class="close" href="#" onclick="$('.alert-message').hide();">×</a>
  <p>{$success}</p>
</div>
{/if}

{if $debug}
<pre>{$debug}</pre>
{/if}

{if isset($breadcrumbs)}
<ul class="breadcrumb">
  {foreach from=$breadcrumbs item=breadcrumb}
  {if !$breadcrumb@last}
  <li><a href="{$breadcrumb.link}" title="{$breadcrumb.title}">{$breadcrumb.title}</a> <span class="divider">/</span></li>
  {else}
  <li class="active">{$breadcrumb.title}</li>
  {/if}
  </li>
  {/foreach}
</ul>
{/if}

</div>
</div>

