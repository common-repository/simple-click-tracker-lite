<?php
$sql_bydomain = Sct_Base::get_user_join_ids();
if(is_array($sql_bydomain) && @count($sql_bydomain)>0){
    $assigned_domains = implode(',',json_decode($sql_bydomain['assigned_domain']));
    $us = $wpdb->get_results("select user_id from  ".self::$table['domain']." where domain_id IN($assigned_domains)",ARRAY_A);
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
$sql = '
SELECT
	f.*
FROM
	'.self::$table['funnel'].' f

GROUP BY
	f.funnel_id
ORDER BY
	f.name';
}else{
$sql = '
SELECT
	f.*
FROM
	'.self::$table['funnel'].' f
WHERE
	f.user_id = '.Sct_Base::getActorUserId().'
GROUP BY
	f.funnel_id
ORDER BY
	f.name';    
}
$funnel_list = $wpdb->get_results($sql, ARRAY_A);
?>
	<div class="sct_button_bar">
		<button onclick="window.location.href='<?php _e($base_url); ?>action=funnel_edit'" class="sct_button">
			Add Funnel
		</button>
	</div>

	<table class="sct_list_table" cellspacing="1" cellpadding="0">
		<tr>
			<th>&nbsp;</th>
			<th width="88%">Name</th>
		</tr>
<?php

if ($funnel_list)
{
	foreach ($funnel_list as $funnel)
	{
		$edit_url = $base_url.'action=funnel_edit&funnel_id='.$funnel['funnel_id'];
?>
		<tr>
			<td align="right" nowrap="nowrap">
				<a href="<?php _e($edit_url); ?>"><img src="<?php _e(SCT_BASE_URL); ?>/includes/icons/pencil.png" width="16" height="16" style="width: 16px; height: 16px;" alt="Edit" title="Edit" border="0" /></a>
			</td>
			<td><a href="<?php _e($edit_url); ?>" funnel="Edit"><?php _e(strip_tags($funnel['name'])); ?></a></td>
		</tr>
<?php
	}
}
else
{
?>
		<tr>
			<td class="sct_empty" colspan="2" align="center"><br />Empty<br /><br /></td>
		</tr>
<?php
}
?>
	</table>
