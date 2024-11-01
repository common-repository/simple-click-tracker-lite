<?php

$url_parts = parse_url($_REQUEST['redirect']);

$domain	= $url_parts['host'];
//$path	= array_shift(explode('-', $url_parts['path']));
$path = @$url_parts['path'];
$qry =@ $url_parts['query'];
Sct_Base::handleRedirect($domain, $path,$qry);
header('location: '.$_REQUEST['error']);
exit();