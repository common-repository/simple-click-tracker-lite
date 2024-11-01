<?php

if ($_SERVER['REQUEST_URI'] == '/sct/ping')
{
	ob_end_clean();
	_e(time());
	exit();
}
if(isset($_REQUEST['sct']) && $_REQUEST['sct']=="ping")
{
	ob_end_clean();
	_e(time());
	exit();
}
if ($_SERVER['REQUEST_URI'] == '/sct/404')
{
	ob_end_clean();
	header("HTTP/1.0 404 Not Found");
	exit("HTTP/1.0 404 Not Found");
}

$url_ajax = 'PLUGIN_URL_HERE/wp-admin/admin-ajax.php?action=sct';

$scheme = 'http://';
if ((int)@$_SERVER['HTTPS'] == 1 || strtolower(@$_SERVER['HTTPS']) == 'on')
{
	$scheme = 'https://';
}

$url_404 = $scheme.$_SERVER['HTTP_HOST'].'/sct/404';

$query = array(
'redirect'	=> $scheme.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],
'referer'	=> $_SERVER['HTTP_REFERER'],
'error'		=> $url_404
);

header('location: '.$url_ajax.'&do=redirect&'.http_build_query($query));
exit();