<?php
$form_vars['funnel_id']		= intval(@$_REQUEST['form_vars']['funnel_id']);
$form_vars['name']			= trim(@$_REQUEST['form_vars']['name']);

if (!$form_vars['name'])
{
	self::$errors['name'] = 'Funnel name is required';
}

if (!self::$errors)
{
	if ($form_vars['funnel_id'])
	{
	    $form_vars['funnel_id'] = sanitize_text_field($_REQUEST['funnel_id']);
        $form_vars['no_of_up'] = trim(@$_REQUEST['form_vars']['no_of_up']);
        $form_vars['no_of_dw'] = trim(@$_REQUEST['form_vars']['no_of_dw']);
        $form_vars['no_of_t'] = trim(@$_REQUEST['form_vars']['no_of_t']);
        $form_vars['start_date'] = trim(sanitize_text_field($_REQUEST['start_date']));
        $form_vars['end_date'] = trim(sanitize_text_field($_REQUEST['end_date']));
        $form_vars['funnel_type'] = trim(@$_REQUEST['form_vars']['funnel_type']);
        $form_vars['c_cost'] =  preg_replace('/[^0-9\.]+/is', '', sanitize_text_field($_REQUEST['c_cost']));
        $form_vars['f_cost'] =  preg_replace('/[^0-9\.]+/is', '', sanitize_text_field($_REQUEST['f_cost']));
		$wpdb->update(self::$table['funnel'], $form_vars, array('funnel_id' => $form_vars['funnel_id']));
	}
	else
	{
		$form_vars['user_id']	= Sct_Base::getActorUserId();
        $form_vars['funnel_id'] = $_REQUEST['funnel_id'];
        $form_vars['no_of_up'] = trim(@$_REQUEST['form_vars']['no_of_up']);
        $form_vars['no_of_dw'] = trim(@$_REQUEST['form_vars']['no_of_dw']);
        $form_vars['no_of_t'] = trim(@$_REQUEST['form_vars']['no_of_t']);
        $form_vars['start_date'] = trim(sanitize_text_field($_REQUEST['start_date']));
        $form_vars['end_date'] = trim(sanitize_text_field($_REQUEST['end_date']));
        $form_vars['funnel_type'] = trim(@$_REQUEST['form_vars']['funnel_type']);
        $form_vars['c_cost'] =  preg_replace('/[^0-9\.]+/is', '', sanitize_text_field($_REQUEST['c_cost']));
        $form_vars['f_cost'] =  preg_replace('/[^0-9\.]+/is', '', sanitize_text_field($_REQUEST['f_cost']));
		$wpdb->insert(self::$table['funnel'], $form_vars);

		$form_vars['funnel_id'] = $wpdb->insert_id;
	}

	$sort_order = 0;
    $sort_order1 = 0;
    $sort_order2 = 0;
    $sort_order3 = 0;
	$funnel_link_id_list = array();
    $funnel_link_id_list_d = array();
    $funnel_link_id_list_dts = array();
    /****************General Landing Page******************/
    $land_link = 1;
 if(isset($_REQUEST['goal_vars_landing']) && is_array($_REQUEST['goal_vars_landing'])){
    foreach ($_REQUEST['goal_vars_landing'] as $sort_order => $record)
	{
		if (isset($record['link_id']) && $record['link_id']!='')
		{
			$sort_order++;
            $record['link_id'] = rtrim($record['link_id'],'/');
            $record['redirect_link'] = rtrim($record['redirect_link'],'/');
			$form_vars['conv_value']	= preg_replace('/[^0-9\.]+/is', '',@$_REQUEST['form_vars']['conv_value']);
			//$form_vars['click_cost']	= preg_replace('/[^0-9\.]+/is', '', @self::$form_vars['click_cost']);

			$funnel_link = array(
			'funnel_id'		=> $form_vars['funnel_id'],
			'funnel_url'	=> $record['link_id'],
            'red_url'		=> $record['redirect_link'],
			'conv_value'	=> preg_replace('/[^0-9\.]+/is', '', $record['conv_value']),
			'sort_order'	=> $sort_order,
            'link_order'    => $land_link,
            'funnel_type'   => 2
			);

			$funnel_link_id = $wpdb->get_var('SELECT funnel_link_id FROM '.self::$table['funnel_link_new'].' WHERE funnel_id = "'.$form_vars['funnel_id'].'" and funnel_type="2" AND funnel_url = "'.$record['link_id'].'"');

			if ($funnel_link_id)
			{
				$funnel_link['funnel_link_id'] = $funnel_link_id;

				$wpdb->update(self::$table['funnel_link_new'], $funnel_link, array('funnel_link_id' => $funnel_link['funnel_link_id']));
			}
			else
			{
				$wpdb->insert(self::$table['funnel_link_new'], $funnel_link);

				$funnel_link_id = $wpdb->insert_id;
                
			}
            $land_link++;
			//$funnel_link_id_list[$funnel_link_id] = $funnel_link_id;
		}
        
	}
 }
    $upsel_order = 1;
    /******* # Of Upsells*   it val in db is 0***/
 if(isset($_REQUEST['goal_vars_u']) && is_array($_REQUEST['goal_vars_u'])){
	foreach ($_REQUEST['goal_vars_u'] as $sort_order1 => $record)
	{
		if (isset($record['link_id']) && $record['link_id']!='')
		{
			$sort_order1++;
            $record['link_id'] = rtrim($record['link_id'],'/');
            $record['redirect_link'] = rtrim($record['redirect_link'],'/');
			$form_vars['conv_value']	= preg_replace('/[^0-9\.]+/is', '', @$_REQUEST['form_vars']['conv_value']);
			//$form_vars['click_cost']	= preg_replace('/[^0-9\.]+/is', '', @self::$form_vars['click_cost']);

			$funnel_link = array(
			'funnel_id'		=> $form_vars['funnel_id'],
			'funnel_url'	=> $record['link_id'],
            'red_url'		=> $record['redirect_link'],
			'conv_value'	=> preg_replace('/[^0-9\.]+/is', '', $record['conv_value']),
            'link_order'    => $upsel_order,
			'sort_order'	=> $sort_order1,
            'funnel_type'   => 0
			);
			$funnel_link_id_u = $wpdb->get_var('SELECT funnel_link_id FROM '.self::$table['funnel_link_new'].' WHERE funnel_id = "'.$form_vars['funnel_id'].'" and funnel_type="0" AND funnel_url = "'.$record['link_id'].'"');

			if ($funnel_link_id_u)
			{
				$funnel_link['funnel_link_id'] = $funnel_link_id_u;

				$wpdb->update(self::$table['funnel_link_new'], $funnel_link, array('funnel_link_id' => $funnel_link['funnel_link_id']));
			}
			else
			{
				$wpdb->insert(self::$table['funnel_link_new'], $funnel_link);

				$funnel_link_id_u = $wpdb->insert_id;
                
                
			}

			$funnel_link_id_list[$funnel_link_id_u] = $funnel_link_id_u;
            $upsel_order++;
		}
	}
 
    $wpdb->query('DELETE FROM '.Sct_Base::$table['funnel_link_new'].' WHERE funnel_id = '.(int)$form_vars['funnel_id'].' AND funnel_type="0" AND funnel_link_id NOT IN ('.implode(',', $funnel_link_id_list).')');
 }
 /******* # Of Dwonsells*   it val in db is 1***/
 $down_order =1;
 if(isset($_REQUEST['goal_vars_d']) && is_array($_REQUEST['goal_vars_d'])){
	foreach ($_REQUEST['goal_vars_d'] as $sort_order2 => $record)
	{
		if (isset($record['link_id']) && $record['link_id']!='')
		{
			$sort_order2++;
            $record['link_id'] = rtrim($record['link_id'],'/');
            $record['redirect_link'] = rtrim($record['redirect_link'],'/');
			$form_vars['conv_value']	= preg_replace('/[^0-9\.]+/is', '', @self::$form_vars['conv_value']);
			//$form_vars['click_cost']	= preg_replace('/[^0-9\.]+/is', '', @self::$form_vars['click_cost']);

			$funnel_link = array(
			'funnel_id'		=> $form_vars['funnel_id'],
			'funnel_url'		=> $record['link_id'],
            'red_url'		=> $record['redirect_link'],
			'conv_value'	=> preg_replace('/[^0-9\.]+/is', '', $record['conv_value']),
            'link_order'    => $down_order,
			'sort_order'	=> $sort_order2,
            'funnel_type'   => 1
			);

			$funnel_link_id_d = $wpdb->get_var('SELECT funnel_link_id FROM '.self::$table['funnel_link_new'].' WHERE funnel_id = "'.$form_vars['funnel_id'].'" and funnel_type="1" AND funnel_url = "'.$record['link_id'].'"');

			if ($funnel_link_id_d)
			{
				$funnel_link['funnel_link_id'] = $funnel_link_id_d;

				$wpdb->update(self::$table['funnel_link_new'], $funnel_link, array('funnel_link_id' => $funnel_link['funnel_link_id']));
			}
			else
			{
				$wpdb->insert(self::$table['funnel_link_new'], $funnel_link);

				$funnel_link_id_d = $wpdb->insert_id;
                
			}

			$funnel_link_id_list_d[$funnel_link_id_d] = $funnel_link_id_d;
            $down_order++;
		}
	}
    $wpdb->query('DELETE FROM '.Sct_Base::$table['funnel_link_new'].' WHERE funnel_id = '.(int)$form_vars['funnel_id'].' AND funnel_type="1" AND funnel_link_id NOT IN ('.implode(',', $funnel_link_id_list_d).')');
 }	
$thanks_order = 1;
/******* # Of Thanks *   it val in db is 3***/
if(isset($_REQUEST['goal_vars_t']) && is_array($_REQUEST['goal_vars_t'])){
	foreach ($_REQUEST['goal_vars_t'] as $sort_order3 => $record)
	{
		if (isset($record['link_id']) && $record['link_id']!='')
		{
			$sort_order3++;
            $record['link_id'] = rtrim($record['link_id'],'/');
            $record['redirect_link'] = rtrim($record['redirect_link'],'/');
			$form_vars['conv_value']	= preg_replace('/[^0-9\.]+/is', '', @self::$form_vars['conv_value']);
			//$form_vars['click_cost']	= preg_replace('/[^0-9\.]+/is', '', @self::$form_vars['click_cost']);

			$funnel_link = array(
			'funnel_id'		=> $form_vars['funnel_id'],
			'funnel_url'		=> $record['link_id'],
            'red_url'		=> $record['redirect_link'],
			'conv_value'	=> preg_replace('/[^0-9\.]+/is', '', $record['conv_value']),
			'link_order'    => $thanks_order,
			'sort_order'	=> $sort_order3,
            'funnel_type'   => 3
			);

			$funnel_link_id_t = $wpdb->get_var('SELECT funnel_link_id FROM '.self::$table['funnel_link_new'].' WHERE funnel_id = "'.$form_vars['funnel_id'].'" and funnel_type="3" AND funnel_url = "'.$record['link_id'].'"');

			if ($funnel_link_id_t)
			{
				$funnel_link['funnel_link_id'] = $funnel_link_id_t;

				$wpdb->update(self::$table['funnel_link_new'], $funnel_link, array('funnel_link_id' => $funnel_link['funnel_link_id']));
			}
			else
			{
				$wpdb->insert(self::$table['funnel_link_new'], $funnel_link);

				$funnel_link_id_t = $wpdb->insert_id;
			}

			$funnel_link_id_list_dts[$funnel_link_id_t] = $funnel_link_id_t;
            $thanks_order++;
		}
	}
	$wpdb->query('DELETE FROM '.Sct_Base::$table['funnel_link_new'].' WHERE funnel_id = '.(int)$form_vars['funnel_id'].' AND funnel_type="3" AND funnel_link_id NOT IN ('.implode(',', $funnel_link_id_list_dts).')');
    }
    header('location: '.$base_url.'action=funnel_grid');
	exit();
}

self::$action = 'funnel_edit';