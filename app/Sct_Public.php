<?php
require_once 'Sct_Base.php';
class Sct_Public extends Sct_Base
{
	public static function init()
	{
		self::initBase();
		define('SCT_SITE_PATH',	SCT_APP_PATH.'/sites/public');
	}
	public static function doAction()
	{
		global $wpdb, $post;
		$base_url = get_permalink($post->ID).'?';
		if (!self::$action)
		{
			if (!@$_REQUEST['action'])
			{
				$_REQUEST['action'] = 'index';
			}
			self::$action = preg_replace('/[^a-z\_]+/is', '', sanitize_text_field($_REQUEST['action']));
		}
		self::$form_vars = sanitize_text_field(@$_POST['form_vars']);
		@array_walk_recursive(self::$form_vars, 'Sct_Public::stripSlashes');
		$file_path = SCT_SITE_PATH.'/actions/default.php';
		if (is_file($file_path))
		{
			require $file_path;
		}
		$file_path = SCT_SITE_PATH.'/actions/'.self::$action.'.php';
		if (is_file($file_path))
		{
			require $file_path;
		}
	}
	public static function stripSlashes(&$item, $key)
	{
		$item = stripslashes($item);
	}
	public static function doView($attribs = NULL)
	{
		global $wpdb, $post;
		require_once SCT_APP_PATH.'/Sct_Form.php';
		$base_url = get_permalink($post->ID).'?';
		$result = '';
		if (!Sct_Public::$action)
		{
		  
			if (isset($attribs['action']))
			{
				self::$action = $attribs['action'];
			}
			elseif (isset($_REQUEST['action']))
			{
				self::$action = sanitize_text_field($_REQUEST['action']);
			}
		}
		self::$action = preg_replace('/[^0-9a-zA-Z\_\-]+/is', '', strtolower(self::$action));
		if (!self::$action)
		{
			self::$action = 'index';
		}
		$form_vars = self::$form_vars;
		$file_path = SCT_SITE_PATH.'/actions/default.php';
		if (is_file($file_path))
		{
			require $file_path;
		}
		$file_path = SCT_SITE_PATH.'/layouts/default.php';
		if (is_file($file_path))
		{
			ob_start();
			require $file_path;
			$result = ob_get_clean();
		}
		return $result;
	}
}
?>