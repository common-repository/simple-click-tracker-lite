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
	g.*,
	COUNT(DISTINCT l.link_id) AS total
FROM
	'.self::$table['group'].' g
LEFT JOIN
	'.self::$table['link'].' l ON g.group_id = l.group_id
GROUP BY
	g.group_id
ORDER BY
	g.name';
}else{
 $sql = '
SELECT
	g.*,
	COUNT(DISTINCT l.link_id) AS total
FROM
	'.self::$table['group'].' g
LEFT JOIN
	'.self::$table['link'].' l ON g.group_id = l.group_id
WHERE
	g.user_id = '.Sct_Base::getActorUserId().'
GROUP BY
	g.group_id
ORDER BY
	g.name';   
}
$group_list = $wpdb->get_results($sql, ARRAY_A);

?>
	<div class="sct_button_bar">
		<button onclick="window.location.href='<?php _e($base_url); ?>action=group_edit'" class="sct_button">
			Add Group
		</button>
	</div>

	<table class="sct_list_table" cellspacing="1" cellpadding="0">
		<tr>
			<th>&nbsp;</th>
			<th width="88%">Name</th>
			<th width="10%" style="text-align: center;">Links</th>
		</tr>
<?php

if ($group_list)
{
	foreach ($group_list as $group)
	{
		$edit_url = $base_url.'action=group_edit&group_id='.$group['group_id'];
		$link_list_url = $base_url.'group_id='.$group['group_id'];
?>
		<tr>
			<td align="right" nowrap="nowrap">
				<a href="<?php _e($edit_url); ?>"><img src="<?php _e(SCT_BASE_URL); ?>/includes/icons/pencil.png" width="16" height="16" style="width: 16px; height: 16px;" alt="Edit" title="Edit" border="0" /></a>
			</td>
			<td><a href="<?php _e($edit_url); ?>" group="Edit"><?php _e(strip_tags($group['name'])); ?></a></td>
			<td style="text-align: center;"><a href="<?php _e($link_list_url); ?>" group="List"><?php _e($group['total']); ?></a></td>
		</tr>
<?php
	}
}
else
{
?>
		<tr>
			<td class="sct_empty" colspan="3" align="center" style="text-align: center;"><br />Empty<br /><br /></td>
		</tr>
<?php
}

?>
	</table>
