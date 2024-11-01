<?php
if (is_admin())  
{
	// Do admin stuff
	function Sct_loadPlugin()
	{
		if (@$_REQUEST['page'] == 'simple_click_tracker')
		{
			include_once SCT_APP_PATH.'/Sct_Admin.php';
			Sct_Admin::init();
			//Sct_Admin::upgradePlugin();
		}
	}
	function Sct_displayView()
	{
	   include_once SCT_APP_PATH.'/Sct_Admin.php';
        Sct_Admin::displayView();
        
	}
	function Sct_addAdminMenu()
	{
		add_menu_page('Simple Click Tracker', 'Simple Click Tracker', 'administrator', 'simple_click_tracker', 'Sct_displayView', 'dashicons-admin-links');
	}
	function Sct_activatePlugin()
	{
		include_once SCT_APP_PATH.'/Sct_Admin.php';
		Sct_Admin::init();
		Sct_Admin::upgradePlugin();
        sct_valid_domain();
	}
    function sct_valid_domain(){
		include_once SCT_APP_PATH.'/Sct_Admin.php';
		Sct_Admin::init();
		Sct_Admin::upgrade_versions();
    }
	function Sct_enqueueScript()
	{
	   $get_ver = get_option('rlm_version_simple_click_tracker');
       if($get_ver!=SCT_VERSION){
            update_option('rlm_version_simple_click_tracker',SCT_VERSION);
        }
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-form');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style('jquery-ui',  plugins_url('includes/jquery-ui.css' , __FILE__));
		wp_enqueue_script('jquery-ui-core', null, array('jquery'));
		wp_enqueue_script('jquery-ui-widget', null, array('jquery-ui-core'));
		wp_enqueue_script('jquery-ui-position', null, array('jquery-ui-core'));
		wp_enqueue_script('jquery-ui-autocomplete', null, array('jquery-ui-widget', 'jquery-ui-position'));
	}
	function Sct_admin_notice()
	{
		$notice = get_option('sct_notification');
		if ($notice)
		{
			_e('<div class="updated"><p>'.$notice.'</p></div>');
		}
	}
	add_action('admin_notices', 'Sct_admin_notice');
	add_action('admin_init', 'Sct_enqueueScript');
	add_action('wp_loaded', 'Sct_loadPlugin', 1);
	add_action('admin_menu', 'Sct_addAdminMenu');
	register_activation_hook(__FILE__, 'Sct_activatePlugin' );
}
else
{
	// Do public stuff
	if (isset($_REQUEST['app']) && $_REQUEST['app'] == 'simple_click_tracker')
	{
		add_action('wp', 'Sct_doAction');
	}
	function Sct_doAction()
	{
		if (!class_exists('Sct_Public'))
		{
			include_once SCT_APP_PATH.'/Sct_Public.php';
			Sct_Public::init();
		}
		Sct_Public::doAction();
	}
	function Sct_handleShortcode($attribs)
	{
		if (!class_exists('Sct_Public'))
		{
			include_once SCT_APP_PATH.'/Sct_Public.php';
			Sct_Public::init();
		}
		return Sct_Public::doView($attribs);
	}
	add_shortcode('simple_click_tracker', 'Sct_handleShortcode');
	function Sct_enqueueScripts()
	{
		wp_enqueue_script('jquery');
        
		wp_enqueue_script('jquery-ui-core', null, array('jquery'));
		wp_enqueue_script('jquery-ui-widget', null, array('jquery-ui-core'));
		wp_enqueue_script('jquery-ui-position', null, array('jquery-ui-core'));
		wp_enqueue_script('jquery-ui-autocomplete', null, array('jquery-ui-widget', 'jquery-ui-position'));
		wp_enqueue_style('jquery-ui',  plugins_url('includes/jquery-ui.css' , __FILE__));
		wp_register_script('jquery-fixedheader', plugins_url('includes/fixedheader/jquery.fixedheadertable.min.js' , __FILE__), array('jquery'), '1.3');
		wp_enqueue_script('jquery-fixedheader');
		wp_register_script('jquery-tablesorter', plugins_url('includes/tablesorter/jquery.tablesorter.min.js' , __FILE__), array('jquery'), '2.0.5b');
		wp_enqueue_script('jquery-tablesorter');
		wp_register_style('jquery-tablesorter-css', plugins_url('includes/fixedheader/css/defaultTheme.css' , __FILE__), NULL, '1.3');
		wp_enqueue_style('jquery-tablesorter-css');
		wp_register_style('jquery-tablesorter-css', plugins_url('includes/tablesorter/themes/blue/style.css' , __FILE__), NULL, '2.0.5b');
		wp_enqueue_style('jquery-tablesorter-css');
	}
	add_action('wp_enqueue_scripts', 'Sct_enqueueScripts');
	function Sct_addToHead()
	{
	   wp_register_style('jquery-style_public', plugins_url('includes/style_public.css' , __FILE__));
	   wp_enqueue_style('jquery-style_public');
       wp_register_script('jquery-sct_public', plugins_url('includes/sct_public.js' , __FILE__));
		wp_enqueue_script('jquery-sct_public');
		_e('
<script type="text/javascript">
var sct_ajax_url = "'.SCT_AJAX_URL.'";
var sct_plugin_url = "'.SCT_BASE_URL.'";
</script>
		');
	}
	add_action('wp_head', 'Sct_addToHead');
	function Sct_Plugins_Loaded()
	{
		include_once SCT_APP_PATH.'/Sct_Public.php';
		Sct_Public::init();
        $query = @$_SERVER['QUERY_STRING'];
		Sct_Base::handleRedirect($_SERVER['HTTP_HOST'], @$_SERVER['REQUEST_URI'], $query ,false);
	}
	add_action('plugins_loaded', 'Sct_Plugins_Loaded');
}
function SCT_load_wp_media_files() {
      wp_enqueue_media();
    }
    add_action( 'admin_enqueue_scripts', 'SCT_load_wp_media_files' );
function Sct_Ajax()
{
	require_once SCT_APP_PATH.'/Sct_Ajax.php';
	Sct_Ajax::init();
	Sct_Ajax::doAction();
}
function sct_ajax_controling(){
    global $wpdb;
    include_once('app/ajax.php');
    die();
}
add_action('wp_ajax_sct', 'Sct_Ajax');
add_action('wp_ajax_nopriv_sct', 'Sct_Ajax');
add_action("wp_ajax_sctimgupload",'sct_ajax_controling');
add_action("wp_ajax_nopriv_sctimgupload",'sct_ajax_controling');
//4.3
add_action("wp_ajax_delete_multiple_links","sct_ajax_controling");
add_action("wp_ajax_nopriv_delete_multiple_links","sct_ajax_controling");