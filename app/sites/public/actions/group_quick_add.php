<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
$response = array(
'success'	=> false
);
$group = array(
'name'	=> trim(@sanitize_text_field($_REQUEST['name'])),
);
if (!$group['name'])
{
	$response['error'] = 'Group name is required';
}
if (!$response['error'])
{
	$group['user_id'] = Sct_Base::getActorUserId();
	$wpdb->insert(Sct_Base::$table['group'], $group);
	$group['group_id'] = $wpdb->insert_id;
	$group_option_list = Sct_Base::getGroupOptionList();
	$response = array(
	'success'	=> true
	);
	foreach ($group_option_list as $group_id => $name)
	{
		$selected = '';
		if ($group_id == $group['group_id'])
		{
			$selected = ' selected';
		}
		$response['option_list'] .= '<option value="'.$group_id.'"'.$selected.'>'.$name.'</option>';
	}
}
ob_end_clean();
header('Content-Type: application/json');
exit(json_encode($response));