<?php

$form_vars = self::$form_vars;

if (!self::$errors)
{
	update_option('sct_api_key',	trim($form_vars['api_key']));

	header('location: admin.php?page='.self::$name.'&action=settings&saved=1');
	exit();
}

self::$action = 'settings';