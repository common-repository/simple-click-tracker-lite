<?php
$form_vars['link_id']		= intval(@$_REQUEST['form_vars']['link_id']);
$form_vars['goal_link_id']	= intval(@$_REQUEST['form_vars']['goal_link_id']);
$form_vars['group_id']		= intval(@$_REQUEST['form_vars']['group_id']);
$form_vars['name']			= @sanitize_text_field(trim(@$_REQUEST['form_vars']['name']));
$form_vars['domain_id']		= intval(@$_REQUEST['form_vars']['domain_id']);
$form_vars['path']			= trim(@$_REQUEST['form_vars']['path']);
$form_vars['url']			= trim(@$_REQUEST['form_vars']['url']);
$form_vars['type']			= trim(@$_REQUEST['form_vars']['type']);
$form_vars['path'] = trim(trim($form_vars['path']), '/');
$form_vars['path'] = preg_replace('/[^0-9a-zA-Z\_\-\ \/]+/is', '', trim($form_vars['path']));
$form_vars['path'] = str_replace(' ', '-', $form_vars['path']);
$form_vars['description'] = sanitize_text_field(trim(@$_REQUEST['form_vars']['description']));
if(!isset($_REQUEST['query_string'])){
    $form_vars['query_string'] = 1;
}else{
    $form_vars['query_string'] = trim(sanitize_text_field($_REQUEST['query_string']));
}
$handle = @fopen($form_vars['url'],'r');
$er = 0;
if (!$form_vars['name'])
{
	self::$errors['name'] = 'Name is required';
}
if (!$form_vars['path'])
{
	self::$errors['path'] = 'Path is required';
}
if (!$form_vars['path'])
{
	self::$errors['path'] = 'Path is required';
}
elseif (strtolower($form_vars['path']) == 'sct')
{
	self::$errors['path'] = 'Path cannot be "sct". This is reserved.';
}
else
{
	$o_link_id = (int)$wpdb->get_var('SELECT * FROM '.self::$table['link'].' WHERE link_id != '.(int)$form_vars['link_id'].' AND domain_id = '.(int)$form_vars['domain_id'].' AND (path = "'.addslashes($form_vars['path']).'" OR path = "/'.addslashes($form_vars['path']).'")');
	if ($o_link_id)
	{
		self::$errors['name'] = 'Path is already in use';
	}
}
if($er==1){
    self::$errors['name'] = 'Please Enter a valid Destination URL.';
}
if (!$form_vars['url'])
{
	self::$errors['url'] = 'To URL is required';
}
else if (parse_url($form_vars['url']) === false)
{
	self::$errors['url'] = 'Invalid or incomplete';
}
if (!self::$errors)
{
    update_option("sct_last_domain_ID",$form_vars['domain_id']);
	if ($form_vars['link_id'])
	{
		//		$form_vars['modified'] = time();
        $form_vars['is_dead'] = 0;
		$wpdb->update(self::$table['link'], $form_vars, array('link_id' => $form_vars['link_id']));
	}
	else
	{
	   $form_vars['is_dead'] = 0;
		$form_vars['user_id']	= Sct_Base::getActorUserId();
		$wpdb->insert(self::$table['link'], $form_vars);
		$form_vars['link_id'] = $wpdb->insert_id;
		$sql = '
		SELECT
			COUNT(*)
		FROM
			'.self::$table['link'].'
		WHERE
			user_id = "'.addslashes(Sct_Base::getActorUserId()).'"';
		$link_count = $wpdb->get_var($sql);
		if ($link_count == 1)
		{
			$domain = $wpdb->get_row('SELECT * FROM '.self::$table['domain'].' WHERE domain_id = '.(int)$form_vars['domain_id'].' and domain!="'.addslashes($_SERVER['HTTP_HOST']).'" AND user_id = "'.addslashes(Sct_Base::getActorUserId()).'"', ARRAY_A);
            if(count($domain)>0){
    			$response = wp_remote_get('http://'.$domain['domain'].'/sct/ping');
    			if (Sct_Base::$is_full_access && (is_wp_error($response) || !(int)$response['body']))
    			{
    				header('location: '.$base_url.'action=ftp_install&domain_id='.(int)$domain['domain_id']);
    				exit();
    			}
            }
		}
	}
         $wpdb->query(
                    $wpdb->prepare(
                        "update ".self::$table['link']." set has_children=%d where link_id=%d",
                        '0',(int)$form_vars['link_id']
                    )
                );
		$back_url = $base_url.'action=link_edit&link_id='.$form_vars['link_id'].'&saved=1';
    $save_new_url = $base_url.'action=link_edit';
    if (@$_REQUEST['save'] == 'Save')
	{
	   header('location: '.$back_url);   
	}else if(@$_REQUEST['save']=="Save & New"){
	   header('location: '.$save_new_url);
	}else{
	   header('Location: '.$base_url);
	}
	exit();
}
self::$action = 'link_edit';