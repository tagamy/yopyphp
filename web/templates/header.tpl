<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<title>yopyphp</title>
<link rel="stylesheet" href="/css/bootstrap.min.css" type="text/css" media="screen" /> 
<script src="/js/jquery-1.6.2.min.js"></script>
<script src="/js/application.js"></script>
</head>
<body>

<div class="topbar">
<div class="fill">
<div class="container">
  <h3><a href="/">yopyphp</a></h3>
  <ul>
    <li><a href="#">page1</a></li>
    <li><a href="#">page2</a></li>
    <li><a href="#">page3</a></li>

    <li class="menu">
    <a href="#" class="menu">その他</a>
    <ul class="menu-dropdown">
      <li><a href="#">page4</a></li>
      <li><a href="#">page5</a></li>
    </ul>
  </li>
</ul>

<ul class="nav secondary-nav">
{if $login}  
  <li>
  <a href="http://twitter.com/{$login.twitter.screen_name}" target="_blank">
  <img src="http://api.twitter.com/1/users/profile_image?screen_name={$login.twitter.screen_name}&size=mini" style="vertical-align:middle;">
  @{$login.twitter.screen_name}
  </a>
  </li>
{else}
  <li><a href="/login/twitter">ログイン</a></li>
{/if}  
</ul>

</div>
</div>
</div>

<div class="container" style="padding-top:50px;">

<div class="row">
<div class="columns span16">

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
<ul class="breadcrumbs">
  {foreach from=$breadcrumbs item=breadcrumb}
  <li>
  <a href="{$breadcrumb.link}" title="{$breadcrumb.title}">{$breadcrumb.title|strim:100}</a>
  {if !$breadcrumb@last}
  &gt;
  {/if}
  </li>
  {/foreach}
</ul>
{/if}

</div>
</div>

