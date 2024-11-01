<?php
require dirname(dirname(__FILE__)).'/snippets/summary_report.php';
$sql_bydomain = Sct_Base::get_user_join_ids();
if(is_array($sql_bydomain) && @count($sql_bydomain)>0){
    $assigned_domains = implode(',',json_decode($sql_bydomain['assigned_domain']));
    $us = $wpdb->get_results("select DISTINCT user_id from  ".self::$table['domain']." where domain_id IN($assigned_domains)",ARRAY_A);
    $arv = array();
    foreach($us as $u){
        $arv[] = $u['user_id'];
    }
    $assigned_user = implode(',',$arv).','.Sct_Base::getActorUserId();
    $user_type = $sql_bydomain['user_type'];
}else{
    $user_type = 2;
    $assigned_user = Sct_Base::getActorUserId();
}
if($user_type==2){
//$domain_list = $wpdb->get_results('SELECT * FROM '.self::$table['domain'].' WHERE user_id = "'.addslashes(Sct_Base::getActorUserId()).'" or user_id="0" ORDER BY domain', ARRAY_A);
$domain_list = $wpdb->get_results('SELECT * FROM '.self::$table['domain'].'  ORDER BY domain', ARRAY_A);
//$domain_list_check = $wpdb->get_results('SELECT * FROM '.self::$table['domain'].' WHERE user_id = "'.addslashes(Sct_Base::getActorUserId()).'" and domain!="'.addslashes($_SERVER['HTTP_HOST']).'" ORDER BY domain', ARRAY_A);
$domain_list_check = $wpdb->get_results('SELECT * FROM '.self::$table['domain'].' WHERE domain!="'.addslashes($_SERVER['HTTP_HOST']).'" ORDER BY domain', ARRAY_A);
$group_list = $wpdb->get_results('SELECT * FROM '.self::$table['group'].'  ORDER BY name', ARRAY_A);
}else{
$domain_list = $wpdb->get_results('SELECT * FROM '.self::$table['domain'].' WHERE user_id = "'.addslashes(Sct_Base::getActorUserId()).'" or domain_id IN('.$assigned_domains.') ORDER BY domain', ARRAY_A);
$domain_list_check = $wpdb->get_results('SELECT * FROM '.self::$table['domain'].' WHERE user_id = "'.addslashes(Sct_Base::getActorUserId()).'" and domain!="'.addslashes($_SERVER['HTTP_HOST']).'" or domain_id IN('.$assigned_domains.') ORDER BY domain', ARRAY_A);
$group_list = $wpdb->get_results('SELECT * FROM '.self::$table['group'].' WHERE user_id = "'.addslashes(Sct_Base::getActorUserId()).'" ORDER BY name', ARRAY_A);    
}

if($user_type == 2){
$sql = '
SELECT
	*
FROM
	'.self::$table['link'].'
ORDER BY
	name';
}else{
    $sql = '
        SELECT
        	*
        FROM
        	'.self::$table['link'].'
        WHERE
        	user_id = "'.addslashes(Sct_Base::getActorUserId()).'"
        ORDER BY
        	name';    
}
$link_list = $wpdb->get_results($sql, ARRAY_A);
if($user_type==2){
if (!$link_list)
{
	require dirname(__FILE__).'/link_edit.php';
	return;
}
}
$type_list = self::getTypeOptionList();
$new_link_url = $base_url.'action=link_edit';
$installed = 1;
if (isset($_REQUEST['domain_id']))
{
	update_user_meta(get_current_user_id(), 'sct_dflt_domain_id', (int)$_REQUEST['domain_id']);
}
else
{
	$_REQUEST['domain_id'] = get_user_meta(get_current_user_id(), 'sct_dflt_domain_id', true);
}
if (isset($_REQUEST['group_id']))
{
	update_user_meta(get_current_user_id(), 'sct_dflt_group_id', (int)$_REQUEST['group_id']);
}
else
{
	$_REQUEST['group_id'] = get_user_meta(get_current_user_id(), 'sct_dflt_group_id', true);
}

?>
<div id="sct_report_summary"></div>
<div style="text-align: right; margin-bottom: 10px;">
	<img id="sct_load_ball" style="display: none;" src="<?php _e(SCT_BASE_URL); ?>/includes/icons/loader-ball.gif" width="16" height="16" alt="edit" />
	<select name="group" id="sct_filter_group" class="sct_filter_select" style="max-width: 200px; padding: 2px;">
		<option value="0">-- All Groups --</option>
<?php
foreach ($group_list as $group)
{
	$selected = '';
	if ((int)@$_REQUEST['group_id'] == (int)$group['group_id'])
	{
		$selected = ' selected';
	}
	_e('<option value="'.$group['group_id'].'"'.$selected.'>'.$group['name'].'</option>');
}
?>
	</select>
<?php
if (Sct_Base::$is_full_access)
{
?>
	<select name="domain" id="sct_filter_domain" class="sct_filter_select" style="max-width: 200px; padding: 2px;">
		<option value="0">-- All Domains --</option>
<?php
$found = 0;
foreach ($domain_list as $domain)
{
	$selected = '';
	if ((int)@$_REQUEST['domain_id'] == (int)$domain['domain_id'])
	{
		$selected = ' selected';
		$found = 1;
	}
	_e('<option value="'.$domain['domain_id'].'"'.$selected.'>'.$domain['domain'].'</option>');
}
if (!$found)
{
	$_REQUEST['domain_id'] = 0;
	update_user_meta(get_current_user_id(), 'sct_dflt_domain_id', sanitize_text_field($_REQUEST['domain_id']));
}
?>
	</select>
<?php
}
?>
<select name="per_page" onchange="changePerPage(this.value)">
	<option>10</option>
	<option>25</option>
	<option>50</option>
	<option>100</option>
	<option>250</option>
</select>
	Search: <input type="text" id="sct_search" name="sct_search" style="width: 250px;" /><input type="button" id="sct_bttn_clear_search" value="Reset" onclick="sct_resetSearch()" />
	<a href="<?php _e($new_link_url); ?>"><button style="float: left;">New Link</button></a>
	<div style="clear: both;"></div>
</div>
<!--
<table class="sct_list_table" cellpadding="0" cellspacing="1" border="0">
<tr>
	<th width="25%">Title</td>
	<th width="30%">Share this redirect link...</td>
	<th width="10%" style="text-align: center;">Total</td>
	<th width="10%" style="text-align: center;">Unique</td>
	<th width="5%" style="text-align: center;">1d</td>
	<th width="5%" style="text-align: center;">7d</td>
	<th width="5%" style="text-align: center;">1m</td>
	<th width="5%" style="text-align: center;">1d</td>
	<th width="5%" style="text-align: center;">7d</td>
	<th width="5%" style="text-align: center;">1m</td>
	<th width="10%" style="text-align: center;">First</td>
	<th width="10%" style="text-align: center;">Last</td>
	<th width="5%">&nbsp;</td>
</tr>
</table>
 -->
<div id="sct_link_list" style="position: relative; text-align: center;">
	<img src="<?php _e(SCT_BASE_URL); ?>/includes/icons/loader-ball.gif" width="16" height="16" alt="edit" style="margin: auto; position: absolute; top: 50%; transform: translate(0, -50%)" />
</div>
<p>
<img src="<?php _e(SCT_BASE_URL); ?>/includes/icons/arrow_divide.png" width="16" height="16" alt="Has split test" /> Split Test<br />
<img src="<?php _e(SCT_BASE_URL); ?>/includes/icons/exclamation.png" width="16" height="16" alt="Bad destination URL" /> Bad destination URL detected
</p>
<script type="text/javascript">
var sort_by = null;
var page = 1;
var per_page = 10;
jQuery(document).ready(function() {
	sct_loadList();
	jQuery('#sct_search').keyup(function(){
		var sct_search = jQuery('#sct_search').val();
		if (sct_search.length >= 3 || sct_search.length == 0)
		{
			sct_typewatch(function() {
				sct_loadList();
			}, 500);
		}
	});
});
jQuery('#sct_filter_domain').change(function(){
	window.location.href = '<?php _e($base_url); ?>domain_id=' + jQuery('#sct_filter_domain').val();
});
jQuery('#sct_filter_group').change(function(){
	window.location.href = '<?php _e($base_url); ?>group_id=' + jQuery('#sct_filter_group').val();
});
function sct_resetSearch()
{
	jQuery('#sct_search').val('');
	sct_loadList();
}
function sct_loadList()
{
	jQuery('#sct_load_ball').show();
	jQuery.ajax({
		url: '<?php _e(SCT_AJAX_URL); ?>&do=link_list2',
		type: 'POST',
		data: {
			search_string: jQuery('#sct_search').val(),
			sort_by: sort_by,
			base_url: '<?php _e($base_url); ?>',
			user_id: '<?php _e($assigned_user); ?>',
			domain_id: '<?php _e((int)@$_REQUEST['domain_id']); ?>',
            assigned_domain:'<?php _e($assigned_domains); ?>',
            user_type: '<?php _e($user_type); ?>',
			group_id: jQuery('#sct_filter_group').val(),
			p: page,
			per_page: per_page
		},
		dataType: 'html'
	}).done(function(data) {
		jQuery('#sct_link_list').html(data);
		jQuery('#sct_load_ball').hide();
	});
}
function sct_goto_page(n_page)
{
	page = n_page;
	sct_loadList()
}
function sct_sortBy(field_name)
{
	sort_by = field_name;
	sct_loadList();
}
/**
 * Change default per page value
 * @param {integer} per_page 
 */
function changePerPage(limit) {
		per_page = limit;
		sct_loadList();
}
var sct_typewatch = (function(){
	var timer = 0;
	return function(callback, ms){
		clearTimeout (timer);
		timer = setTimeout(callback, ms);
	};
})();
</script>