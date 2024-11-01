<?php
$form_vars['group_id']		= intval(@$_REQUEST['form_vars']['group_id']);
$form_vars['name']			= trim(@$_REQUEST['form_vars']['name']);
if (!$form_vars['name'])
{
	self::$errors['name'] = 'Group name is required';
}
if (!self::$errors)
{
	if ($form_vars['group_id'])
	{
		$wpdb->update(self::$table['group'], $form_vars, array('group_id' => $form_vars['group_id']));
	}
	else
	{
		$form_vars['user_id']	= Sct_Base::getActorUserId();
		$wpdb->insert(self::$table['group'], $form_vars);
		$form_vars['group_id'] = $wpdb->insert_id;
	}
	header('location: '.$base_url.'action=group_grid');
	exit();
}
self::$action = 'group_edit';