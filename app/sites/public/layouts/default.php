<?php
if (is_admin())
{
?>
<h1 style="padding-top: 5px;">Simple Click Tracker Lite <span style="font-size: 12px;">v<?php _e(SCT_VERSION); ?></span></h1>
<div class="sct_notice">Check out our <a href="https://nams.ws/SCTLiteUpgrade" target="_blank">PREMIUM VERSION</a> for more features and funtionalities.
    </div>
<?php
}
?>

<div id="sct_default_layout" style="padding-right: 15px;">

<?php
if (!is_admin())
{
	include SCT_SITE_PATH.'/snippets/user_switcher.php'; 
}
?>

<div style="clear: both;"></div>

	<?php include SCT_SITE_PATH.'/snippets/nav.php'; ?>

	<div id="sct_default_layout_inner">

<?php

$view_path = SCT_SITE_PATH.'/views/'.self::$action.'.php';

if (is_file($view_path))
{
	require $view_path;
}
?>

	</div>
</div>
<div class="ads_panel">
     <?php
    	$response = wp_remote_get('https://namstoolkit.com/wp-admin/admin-ajax.php?action=ntks_getads&p=sct');
    	$response_body = wp_remote_retrieve_body($response); 
    	print_r($response_body);
     ?>
</div>
