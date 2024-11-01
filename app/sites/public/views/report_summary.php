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
    //$assigned_user = Sct_Base::getActorUserId();
    $user_type = $sql_bydomain['user_type'];
}else{
    $user_type = 2;
    $assigned_user = Sct_Base::getActorUserId();
} 
$last_list = array(
'year'	=> 'Year',
'month'	=> 'Month',
'week'	=> 'Week',
'day'	=> 'Day',
'hour'	=> 'Hour',
'all'	=> 'ALL'
);

if (!@$_REQUEST['last'])
{
	$_REQUEST['last'] = 'month';
}
if($user_type==2){
$domain_list = $wpdb->get_results('SELECT * FROM '.self::$table['domain'].'  ORDER BY domain', ARRAY_A);
$group_list = $wpdb->get_results('SELECT * FROM '.self::$table['group'].'  ORDER BY name', ARRAY_A);
}else{
$domain_list = $wpdb->get_results('SELECT * FROM '.self::$table['domain'].' WHERE domain_id IN('.$assigned_domains.') ORDER BY domain', ARRAY_A);
$group_list = $wpdb->get_results('SELECT * FROM '.self::$table['group'].' WHERE user_id = "'.Sct_Base::getActorUserId().'" ORDER BY name', ARRAY_A); 
}


$where_list = array();

if ((int)@$_REQUEST['group_id'])
{
	$where_list[] = ' l.group_id = '.(int)$_REQUEST['group_id'];
}

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
if($user_type==2){
$link_wheres = array();
}else{
$link_wheres = array('user_id = "'.addslashes(Sct_Base::getActorUserId()).'"');    
}

if ((int)@$_REQUEST['domain_id'])
{
	$where_list[] = ' l.domain_id = '.sanitize_text_field((int)$_REQUEST['domain_id']);
	$link_wheres[] = ' domain_id = '.sanitize_text_field((int)$_REQUEST['domain_id']);
}

if ((int)@$_REQUEST['group_id'])
{
	$where_list[] = ' l.group_id = '.(int)$_REQUEST['group_id'];
	$link_wheres[] = ' group_id = '.(int)$_REQUEST['group_id'];
}
if($user_type==2){
        if(empty($link_wheres)){
            $link_list = $wpdb->get_results('SELECT * FROM '.self::$table['link'].'  ORDER BY name', ARRAY_A);
        }else{
            $link_list = $wpdb->get_results('SELECT * FROM '.self::$table['link'].' WHERE '.implode(' AND ', $link_wheres).'  ORDER BY name', ARRAY_A);
        }
}else{
$link_list = $wpdb->get_results('SELECT * FROM '.self::$table['link'].' WHERE '.implode(' AND ', $link_wheres).' ORDER BY name', ARRAY_A);    
}


if ((int)@$_REQUEST['link_id'])
{
	$where_list[] = ' l.link_id = '.(int)$_REQUEST['link_id'];
}

switch ($_REQUEST['last'])
{
	case 'all':
		$date_format = 'DATE_FORMAT(c.`date_time`, "%c/%e")';
		break;

	case 'year':
		$date_format = 'DATE_FORMAT(c.`date_time`, "%b")';
		$where_list[] = 'c.`date_time` >= DATE_SUB(NOW(), INTERVAL 1 YEAR)';
		break;

	case 'month':
		$date_format = 'DATE_FORMAT(c.`date_time`, "%c/%e")';
		$where_list[] = 'c.`date_time` >= DATE_SUB(NOW(), INTERVAL 1 MONTH)';
		break;

	case 'week':
		$date_format = 'DATE_FORMAT(c.`date_time`, "%c/%e")';
		$where_list[] = 'c.`date_time` >= DATE_SUB(NOW(), INTERVAL 1 WEEK)';
		break;

	case 'day':
		$date_format = 'DATE_FORMAT(c.`date_time`, "%H")';
		$where_list[] = 'c.`date_time` >= DATE_SUB(NOW(), INTERVAL 1 DAY)';
		break;

	case 'hour':
		$date_format = 'DATE_FORMAT(c.`date_time`, "%i")';
		$where_list[] = 'c.`date_time` >= DATE_SUB(NOW(), INTERVAL 1 HOUR)';
		break;
}

$wheres = '';
if ($where_list)
{
	$wheres = ' AND '.implode(' AND ', $where_list);
}
if($user_type==2){
$base_sql = '
SELECT
	l.*,
	d.*,
	'.$date_format.' AS `key`,
	COUNT(*) AS `total_clicks_count`,
	COUNT(DISTINCT c.`ip`) AS `unique_clicks`
FROM
	'.Sct_Base::$table['click'].' c
LEFT JOIN '.Sct_Base::$table['link'].' l ON c.link_id = l.link_id
LEFT JOIN '.Sct_Base::$table['domain'].' d ON l.domain_id = d.domain_id
WHERE

	l.parent_id = 0
'.$wheres;
}else{
    $base_sql = '
SELECT
	l.*,
	d.*,
	'.$date_format.' AS `key`,
	COUNT(*) AS `total_clicks_count`,
	COUNT(DISTINCT c.`ip`) AS `unique_clicks`
FROM
	'.Sct_Base::$table['click'].' c
LEFT JOIN '.Sct_Base::$table['link'].' l ON c.link_id = l.link_id
LEFT JOIN '.Sct_Base::$table['domain'].' d ON l.domain_id = d.domain_id
WHERE
	l.domain_id IN ('.$assigned_domains.')
AND
	l.parent_id = 0
'.$wheres;    
}
$sql = $base_sql.'
GROUP BY
	'.$date_format.'
ORDER BY
	c.`date_time`';


$data_list = $wpdb->get_results($sql, ARRAY_A);

?>

<form id="sct_filter_form">
<table>
<tr>
	<td>Group&nbsp;</td>
	<td>
		<select name="group_id" id="sct_filter_group" class="sct_filter_select" style="max-width: 200px;">
			<option value="0">-- all --</option>
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
	</td>
	<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
<?php
if (Sct_Base::$is_full_access)
{
?>
	<td>Domain&nbsp;</td>
	<td>
		<select name="domain" id="sct_filter_domain" class="sct_filter_select" style="max-width: 200px;">
			<option value="0">-- all --</option>
<?php
foreach ($domain_list as $domain)
{
	$selected = '';
	if ((int)@$_REQUEST['domain_id'] == (int)$domain['domain_id'])
	{
		$selected = ' selected';
	}
	_e('<option value="'.$domain['domain_id'].'"'.$selected.'>'.$domain['domain'].'</option>');
}
?>
		</select>
	</td>
	<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
<?php
}
?>
	<td>Link&nbsp;</td>
	<td>
		<select name="link" id="sct_filter_link" class="sct_filter_select" style="max-width: 200px;">
			<option value="0">-- all --</option>
<?php
foreach ($link_list as $link)
{
	$selected = '';
	if ((int)@$_REQUEST['link_id'] == (int)$link['link_id'])
	{
		$selected = ' selected';
	}
	_e('<option value="'.$link['link_id'].'"'.$selected.'>'.$link['name'].'</option>');
}
?>
		</select>
	</td>
	<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
	<td>Last&nbsp;</td>
	<td>
		<select name="last" id="sct_filter_last" class="sct_filter_select" style="max-width: 200px;">
<?php
foreach ($last_list as $value => $last)
{
	$selected = '';
	if ($_REQUEST['last'] == $value)
	{
		$selected = ' selected';
	}
	_e('<option value="'.$value.'"'.$selected.'>'.$last.'</option>');
}
?>
		</select>
	</td>
</tr>
</table>

</form>

<div id="chart1_div" style="width: 99%; height: 350px; border: 1px solid #DDD;"></div>

<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">

jQuery('.sct_filter_select').change(function(){
	var group_id = jQuery('#sct_filter_group').val();
	var domain_id = jQuery('#sct_filter_domain').val();
	var link_id = jQuery('#sct_filter_link').val();
	var last = jQuery('#sct_filter_last').val();
	window.location.href = '<?php _e($base_url); ?>action=report_summary&group_id=' + group_id + '&domain_id=' + domain_id + '&link_id=' + link_id + '&last=' + last;
});

<?php

if ($data_list)
{
	$list = array();

	switch ($_REQUEST['last'])
	{
		case 'month':
			$list[] = "['Day', 'Total Clicks', 'Unique Clicks']";
			break;

		case 'week':
			$list[] = "['Day', 'Total Clicks', 'Unique Clicks']";
			break;

		case 'day':
			$list[] = "['Hour', 'Total Clicks', 'Unique Clicks']";
			break;

		case 'hour':
			$list[] = "['Minute', 'Total Clicks', 'Unique Clicks']";
			break;

		case 'year':
			$list[] = "['Month', 'Total Clicks', 'Unique Clicks']";
			break;
	}

	foreach ($data_list as $key => $data)
	{
		$list[] = "['".$data['key']."', ".(int)$data['total_clicks'].", ".(int)$data['unique_clicks']."]";
	}
?>
google.load("visualization", "1", {packages:["corechart"]});

google.setOnLoadCallback(drawChart1);
function drawChart1() {
	var data = google.visualization.arrayToDataTable([<?php _e(implode(',', $list)); ?>]);

	var options = { title: 'Clicks' };

	var chart1 = new google.visualization.LineChart(document.getElementById('chart1_div'));
	chart1.draw(data, options);
}
<?php
}
?>
</script>
<table class="sct_list_table" cellpadding="0" cellspacing="1" border="0">
<thead>
	<tr>
		<th>Title</th>
		<th>Link</th>
		<th width="15%" style="text-align: center;">Unique Clicks</th>
		<th width="15%" style="text-align: center;">Total Clicks</th>
	</tr>
</thead>
<tbody>
<?php

$sql = $base_sql.'
GROUP BY
	l.link_id
ORDER BY
	unique_clicks DESC';

$data_list = $wpdb->get_results($sql, ARRAY_A);

foreach ($data_list as $key => $data)
{
	$redirect = 'http://'.trim($data['domain'], '/').'/'.trim($data['path'], '/');

	$edit_url = $base_url.'action=link_edit&link_id='.$data['link_id'];
?>
<tr>
	<td><a href="<?php _e($edit_url); ?>" target="_blank"><?php _e($data['name']); ?></a></td>
	<td><a href="<?php _e($redirect); ?>" target="_blank"><?php _e($redirect); ?></a></td>
	<td style="text-align: center;"><?php _e($data['unique_clicks']); ?></td>
	<td style="text-align: center;"><?php _e($data['total_clicks']); ?></td>
</tr>
<?php
}
?>
</tbody>
</table>