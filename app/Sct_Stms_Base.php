<?php
require_once 'Sct_Base.php';

class Sct_Stms_Base extends Sct_Base
{

	public static function getUserId()
	{
		return 1;
		
		$user = stms_get_current_member();

		return (int)$user->ID;
	}

	public static function getLoggoutUrl()
	{
		return 'https://affiliatereceivables.com/member/?imhandler=logout';
	}

	public static function getUserKey()
	{
		$user = stms_get_current_member();

		if (!@$_SESSION['affp_user_key'])
		{
			$_SESSION['affp_user_key'] = md5(AFFP_SALT.$user->ID);
		}

		return $_SESSION['affp_user_key'];
	}

	public static function getNewUserKey()
	{
		$user = stms_get_current_member();

		if (!@$_SESSION['affp_new_user_key'])
		{
			$_SESSION['affp_new_user_key'] = md5(AFFP_SALT.$user->ID);
		}

		return $_SESSION['affp_new_user_key'];
	}
}
?>