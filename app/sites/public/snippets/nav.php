<?php
$sql_bydomain = Sct_Base::get_user_join_ids();
if(is_array($sql_bydomain) && @count($sql_bydomain)>0){
    $assigned_domains = implode(',',json_decode($sql_bydomain['assigned_domain']));
    $user_type = $sql_bydomain['user_type'];
}else{
    $user_type = 2;
} 

$nav_item_list['index'] = array(
'title'	=> 'Links',
'url'	=> $base_url
);


$nav_item_list['group_grid'] = array(
'title'	=> 'Groups',
'url'	=> $base_url.'action=group_grid'
);



$nav_item_list['funnel_grid'] = array(
'title'	=> 'Funnels',
'url'	=> $base_url.'action=funnel_grid'
);

$nav_item_list['report_summary'] = array(
'title'	=> 'Stats',
'url'	=> $base_url.'action=report_summary'
);

if (is_admin())
{
	$nav_item_list['settings'] = array(
	'title'	=> 'Settings',
	'url'	=> $base_url.'action=settings'
	);
}


if (Sct_Base::$is_full_access)
{
	$nav_item_list['help'] = array(
	'title'	=> 'Help',
	//'url'	=> $base_url.'action=help'
    'url'	=> 'http://nams.ws/help'
	);
}

_e('<div class="sct_nav_list">');
_e('<ul>');

foreach ($nav_item_list as $action => $nav_item)
{
	$selected = '';
	if (self::$action == $action)
	{
		$selected = ' class="selected"';
	}
    if($nav_item['title']=="Help"){
        $t = "target='_blank'";
    }else{
        $t = "";
    }
	_e('<li'.$selected.'><a href="'.$nav_item['url'].'"  '.$t.'>'.$nav_item['title'].'</a></li>');
}

_e('</ul>');
_e('<div style="clear: both; margin: 0; padding: 0;"></div>');
_e('</div>');
