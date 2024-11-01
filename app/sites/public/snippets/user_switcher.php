<?php

$sql_bydomain = Sct_Base::get_user_join_ids();
if(is_array($sql_bydomain) && count($sql_bydomain)>0){
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
_e('<div id="sct_nav_user">');

$t_parent_user_id_list = $wpdb->get_results('SELECT parent_user_id FROM '.self::$table['user_join'].' WHERE child_user_id = '.Sct_Base::getUserId(), ARRAY_A);

$id_list = array();
foreach ($t_parent_user_id_list as $record)
{
	$id_list[$record['parent_user_id']] = $record['parent_user_id'];
}

$parent_user_list = Sct_Base::get_UserListById($id_list);

if (@$_REQUEST['sct_parent_user_id'] == 'me')
{
	@setcookie('sct_parent_user_id', 0);
	$_COOKIE['sct_parent_user_id'] = 0;
}

if ($parent_user_list)
{
	_e('Manage links for <select id="sct_nav_user_select">');
	_e('<option value="me">-- Me --</option>');
	foreach ($parent_user_list as $user)
	{
		$selected = '';

		if ($_REQUEST['sct_parent_user_id'] == $user['ID'])
		{
			@setcookie('sct_parent_user_id', $user['ID']);
			$_COOKIE['sct_parent_user_id'] = $user['ID'];
		}

		if ($_COOKIE['sct_parent_user_id'] == $user['ID'])
		{
			$selected = ' selected';
		}

		_e('<option value="'.$user['ID'].'"'.$selected.'>'.trim($user['display_name']).'</option>');
	}
	_e('</select>');
}

_e('</div>');

?>
<script type="text/javascript">
jQuery('#sct_nav_user_select').change(function(){
	window.location.href = '<?php _e($base_url); ?>sct_parent_user_id=' + jQuery('#sct_nav_user_select').val();
});
</script>
<?php
}
?>