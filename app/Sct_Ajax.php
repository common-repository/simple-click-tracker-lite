<?php
require_once 'Sct_Base.php';
class Sct_Ajax extends Sct_Base
{
	public static function init()
	{
		self::initBase();
		define('SCT_SITE_PATH',	SCT_APP_PATH.'/sites/ajax');
	}
	public static function doAction()
	{
		global $wpdb;

		self::$action = preg_replace('/[^a-z0-9\_]+/is', '', $_REQUEST['do']);

		$file_path = SCT_SITE_PATH.'/default.php';

		if (is_file($file_path))
		{
			require $file_path;
		}
		
		$file_path = SCT_SITE_PATH.'/'.self::$action.'.php';

		if (is_file($file_path))
		{
			require $file_path;
		}
	}
}
?>