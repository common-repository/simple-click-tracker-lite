<?php
/*
 Plugin Name: Simple Click Tracker Lite
 Plugin URI: https://namstoolkit.com/sct-welcome
 Description: Simple Click Tracker that just works
 Version: 1.3
 Author: NAMS, Inc
 Author URI: https://mynams.com/
 License: GPLv2 or later
 License URL: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */
if (!defined('SCT_VERSION'))
{
	define('SCT_VERSION',	'1.3');
}
define('SCT_PATH',		dirname(__FILE__));
define('SCT_APP_PATH',	SCT_PATH.'/app');
define('SCT_IMAGE_PATH',	SCT_PATH.'/includes/images');
define('SCT_BASE_URL',	plugin_dir_url(__FILE__));
define('SCT_AJAX_URL',	admin_url('admin-ajax.php').'?action=sct');

define('SCT_NO_ARROW_URL', plugins_url('includes/images/s.gif' , __FILE__));
define('SCT_UP_ARROW_URL', plugins_url('includes/icons/arrow_up.png' , __FILE__));
define('SCT_DN_ARROW_URL', plugins_url('includes/icons/arrow_up.png' , __FILE__));
define('SCT_IMP_URL', plugins_url('includes/images/import.png' , __FILE__));
define('SCT_IMPORTSAMPLE_URL', plugins_url('includes/sample.csv' , __FILE__));
define('SCT_TREE_DIR', plugins_url('includes/tree' , __FILE__));

require_once ('s3_click_tracker_functions.php'); 
register_activation_hook(__FILE__, 'Sct_activatePlugin' );