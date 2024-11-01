<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);
header('Content-Type: application/json');

$sql_bydomain = Sct_Base::get_user_join_ids();
if (is_array($sql_bydomain) && count($sql_bydomain) > 0) {
	$assigned_domains = implode(',', json_decode($sql_bydomain['assigned_domain']));
	$user_type = $sql_bydomain['user_type'];
} else {
	$user_type = 2;
}

$response = array(
	'success'	=> false
);
$group = array(
	'name'	=> trim(@$_REQUEST['name'])
);
if (!$group['name']) {
	$response['error'] = 'Group name is required';
}
if (!$response['error']) {
	//$group['user_id'] = Sct_Base::getActorUserId();
	$group['user_id'] = $_REQUEST['user_id'];
	$group_exist = $wpdb->query(
		$wpdb->prepare("SELECT * FROM " . Sct_Base::$table['group'] . " WHERE name = '%s'", [$group['name']])
	);

	if ($group_exist > 0) {
		$response = array(
			'error'	=> 'This group already exists',
		);
		exit(json_encode($response));
	}

	$wpdb->insert(Sct_Base::$table['group'], $group);
	$group['group_id'] = $wpdb->insert_id;
	//$group_option_list = Sct_Base::getGroupOptionList();
	if ($user_type == 2) {
		$group_option_list = Sct_Base::getGroupOptionList_users($group['user_id']);
	} else {
		$group_option_list = Sct_Base::getGroupOptionList_user($group['user_id']);
	}
	$response = array(
		'success'	=> true
	);
	foreach ($group_option_list as $group_id => $name) {
		$selected = '';
		if ($group_id == $group['group_id']) {
			$selected = ' selected';
		}
		$response['option_list'] .= '<option value="' . $group_id . '"' . $selected . '>' . $name . '</option>';
	}
}
ob_end_clean();
exit(json_encode($response));
