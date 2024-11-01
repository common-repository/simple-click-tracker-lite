<?php
$result = '';
$and_list = array();
$user_id = sanitize_text_field($_REQUEST['user_id']);
$assigned_domain = sanitize_text_field($_REQUEST['assigned_domain']);
$user_type = sanitize_text_field($_REQUEST['user_type']);

if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
    $schemes = 'https://';
else
    $schemes = 'http://';


//$and_list[] = 'l.user_id = '.$user_id;
if($user_type!=2){
    $and_list[] = 'l.user_id IN( '.$user_id.')';
}
$and_list[] = 'l.parent_id = 0';
if (@$_REQUEST['search_string'])
{
	$or_list = array();
	$part_list = explode(' ', sanitize_text_field($_REQUEST['search_string']));
	//foreach ($part_list as $part)
	//{
		//$part = trim($part);
		//if ($part)
	//	{
			/*$or_list[] = 'l.name LIKE "%'.addslashes($part).'%"';
			$or_list[] = 'l.path LIKE "%'.addslashes($part).'%"';
			$or_list[] = 'l.url LIKE "%'.addslashes($part).'%"'; */
            $part = $_REQUEST['search_string'];
            $or_list[] = 'l.name LIKE "%'.addslashes($part).'%"';
			$or_list[] = 'l.path LIKE "%'.addslashes($part).'%"';
            $or_list[] = 'description LIKE "%'.addslashes($part).'%"';
			//$or_list[] = 'l.url LIKE "'.addslashes($part).'"';
            
	//	}
	//}
	if ($or_list)
	{
		$and_list[] = '('.implode(' OR ', $or_list).')';
        //$and_list[] = '('.implode(' and ', $or_list).')';
	}
}
if (isset($_REQUEST['domain_id']) && (int)$_REQUEST['domain_id'])
{
	$and_list[] = 'l.domain_id = '.sanitize_text_field((int)$_REQUEST['domain_id']);
}else{
    if($user_type!=2){
        $and_list[] = 'l.domain_id IN( '.$assigned_domain.')';
    }
}
if (isset($_REQUEST['group_id']) && (int)$_REQUEST['group_id'])
{
	$and_list[] = 'l.group_id = '.sanitize_text_field((int)$_REQUEST['group_id']);
}
$and_list[] = 'l.domain_id = d.domain_id';
$sql = '
SELECT
	COUNT(*)
FROM
	'.self::$table['link'].' l,
	'.self::$table['domain'].' d
WHERE
	'.implode(' AND ', $and_list);
$total = $wpdb->get_var($sql);
$sql = '
SELECT  DISTINCT 
	l.link_id
FROM
	'.self::$table['link'].' l,
	'.self::$table['domain'].' d
WHERE
	'.implode(' AND ', $and_list);
if (@$_REQUEST['sort_by'])
{
	$sql .= '
	ORDER BY
		'.preg_replace('/[^0-9a-zA-Z\-\_\.]+/is', '', sanitize_text_field($_REQUEST['sort_by']));
	if ($_REQUEST['sort_by'] == 'name' || $_REQUEST['sort_by'] == 'path')
	{
		$sql .= ' ASC';
	}
	else
	{
		$sql .= ' DESC';
	}
}
else
{
	$sql .= '
	ORDER BY
		l.created DESC';
}
$limit = sanitize_text_field($_REQUEST['per_page']);
$page = 1;
if ((int)@$_REQUEST['p'])
{
	$page = sanitize_text_field((int)$_REQUEST['p']);
}
$offset = 0;
if ($page > 1)
{
	$offset = (($page - 1) * $limit);
}
$num_pages = ceil($total / $limit);
$sql .= ' LIMIT '.$offset.', '.$limit;
$link_list = $wpdb->get_results($sql, ARRAY_A);
$link_id_list = array(0);
foreach ($link_list as $link)
{
	$link_id_list[$link['link_id']] = $link['link_id'];
}
if($user_type!=2){
$sql_1d = '
SELECT
	c.`link_id`,
	COUNT(DISTINCT c.`ip`) AS unique_1d,
	COUNT(*) AS total_1d
FROM
	'.self::$table['click'].' c,
	'.self::$table['link'].' l
WHERE
	l.link_id IN ('.implode(',', $link_id_list).')
AND
	(c.link_id = l.link_id OR c.parent_id = l.link_id)
AND
	l.user_id IN('.addslashes(@sanitize_text_field($_REQUEST['user_id'])).')
AND
	c.`date_time` >= DATE_SUB(NOW(), INTERVAL 1 DAY)
GROUP BY
	c.parent_id
';
}else{
$sql_1d = '
SELECT
	c.`link_id`,
	COUNT(DISTINCT c.`ip`) AS unique_1d,
	COUNT(*) AS total_1d
FROM
	'.self::$table['click'].' c,
	'.self::$table['link'].' l
WHERE
	l.link_id IN ('.implode(',', $link_id_list).')
AND
	(c.link_id = l.link_id OR c.parent_id = l.link_id)
AND
	c.`date_time` >= DATE_SUB(NOW(), INTERVAL 1 DAY)
GROUP BY
	c.parent_id
';    
}

if($user_type!=2){
$sql_1dt = '
SELECT
	c.`link_id`,
	COUNT(DISTINCT c.`ip`) AS unique_1dt,
	COUNT(*) AS total_1dt
FROM
	'.self::$table['click'].' c,
	'.self::$table['link'].' l
WHERE
	l.link_id IN ('.implode(',', $link_id_list).')
AND
	(c.link_id = l.link_id OR c.parent_id = l.link_id)
AND
	l.user_id IN ('.addslashes(@sanitize_text_field($_REQUEST['user_id'])).')
AND
	c.`date_time` >= DATE_SUB(NOW(), INTERVAL 2 DAY)
AND
	c.`date_time` < DATE_SUB(NOW(), INTERVAL 1 DAY)
GROUP BY
	c.parent_id
';
}else{
    $sql_1dt = '
SELECT
	c.`link_id`,
	COUNT(DISTINCT c.`ip`) AS unique_1dt,
	COUNT(*) AS total_1dt
FROM
	'.self::$table['click'].' c,
	'.self::$table['link'].' l
WHERE
	l.link_id IN ('.implode(',', $link_id_list).')
AND
	(c.link_id = l.link_id OR c.parent_id = l.link_id)
AND

	c.`date_time` >= DATE_SUB(NOW(), INTERVAL 2 DAY)
AND
	c.`date_time` < DATE_SUB(NOW(), INTERVAL 1 DAY)
GROUP BY
	c.parent_id
';
}
if($user_type!=2){
$sql_7d = '
SELECT
	c.`link_id`,
	COUNT(DISTINCT c.`ip`) AS unique_7d,
	COUNT(*) AS total_7d
FROM
	'.self::$table['click'].' c,
	'.self::$table['link'].' l
WHERE
	l.link_id IN ('.implode(',', $link_id_list).')
AND
	(c.link_id = l.link_id OR c.parent_id = l.link_id)
AND
	l.user_id IN('.addslashes(@sanitize_text_field($_REQUEST['user_id'])).')
AND
	c.`date_time` >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY
	c.parent_id
';
}else{
    $sql_7d = '
SELECT
	c.`link_id`,
	COUNT(DISTINCT c.`ip`) AS unique_7d,
	COUNT(*) AS total_7d
FROM
	'.self::$table['click'].' c,
	'.self::$table['link'].' l
WHERE
	l.link_id IN ('.implode(',', $link_id_list).')
AND
	(c.link_id = l.link_id OR c.parent_id = l.link_id)
AND
	c.`date_time` >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY
	c.parent_id
';
}
if($user_type!=2){
$sql_7dt = '
SELECT
	c.`link_id`,
	COUNT(DISTINCT c.`ip`) AS unique_7dt,
	COUNT(*) AS total_7dt
FROM
	'.self::$table['click'].' c,
	'.self::$table['link'].' l
WHERE
	l.link_id IN ('.implode(',', $link_id_list).')
AND
	(c.link_id = l.link_id OR c.parent_id = l.link_id)
AND
	l.user_id IN('.addslashes(@sanitize_text_field($_REQUEST['user_id'])).')
AND
	c.`date_time` >= DATE_SUB(NOW(), INTERVAL 14 DAY)
AND
	c.`date_time` < DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY
	c.parent_id
';
}else{
    $sql_7dt = '
SELECT
	c.`link_id`,
	COUNT(DISTINCT c.`ip`) AS unique_7dt,
	COUNT(*) AS total_7dt
FROM
	'.self::$table['click'].' c,
	'.self::$table['link'].' l
WHERE
	l.link_id IN ('.implode(',', $link_id_list).')
AND
	(c.link_id = l.link_id OR c.parent_id = l.link_id)
AND
	c.`date_time` >= DATE_SUB(NOW(), INTERVAL 14 DAY)
AND
	c.`date_time` < DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY
	c.parent_id
';
}
if($user_type!=2){
$sql_1m = '
SELECT
	c.`link_id`,
	COUNT(DISTINCT c.`ip`) AS unique_1m,
	COUNT(*) AS total_1m
FROM
	'.self::$table['click'].' c,
	'.self::$table['link'].' l
WHERE
	l.link_id IN ('.implode(',', $link_id_list).')
AND
	(c.link_id = l.link_id OR c.parent_id = l.link_id)
AND
	l.user_id IN ('.addslashes(@sanitize_text_field($_REQUEST['user_id'])).')
AND
	c.`date_time` >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
GROUP BY
	c.parent_id
';
}else{
    $sql_1m = '
SELECT
	c.`link_id`,
	COUNT(DISTINCT c.`ip`) AS unique_1m,
	COUNT(*) AS total_1m
FROM
	'.self::$table['click'].' c,
	'.self::$table['link'].' l
WHERE
	l.link_id IN ('.implode(',', $link_id_list).')
AND
	(c.link_id = l.link_id OR c.parent_id = l.link_id)
AND
	c.`date_time` >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
GROUP BY
	c.parent_id
';
}
if($user_type!=2){
$sql_1mt = '
SELECT
	c.`link_id`,
	COUNT(DISTINCT c.`ip`) AS unique_1mt,
	COUNT(*) AS total_1mt
FROM
	'.self::$table['click'].' c,
	'.self::$table['link'].' l
WHERE
	l.link_id IN ('.implode(',', $link_id_list).')
AND
	(c.link_id = l.link_id OR c.parent_id = l.link_id)
AND
	l.user_id IN ('.addslashes(@sanitize_text_field($_REQUEST['user_id'])).')
AND
	c.`date_time` >= DATE_SUB(NOW(), INTERVAL 2 MONTH)
AND
	c.`date_time` < DATE_SUB(NOW(), INTERVAL 1 MONTH)
GROUP BY
	c.parent_id
';
}else{
    $sql_1mt = '
SELECT
	c.`link_id`,
	COUNT(DISTINCT c.`ip`) AS unique_1mt,
	COUNT(*) AS total_1mt
FROM
	'.self::$table['click'].' c,
	'.self::$table['link'].' l
WHERE
	l.link_id IN ('.implode(',', $link_id_list).')
AND
	(c.link_id = l.link_id OR c.parent_id = l.link_id)
AND
	c.`date_time` >= DATE_SUB(NOW(), INTERVAL 2 MONTH)
AND
	c.`date_time` < DATE_SUB(NOW(), INTERVAL 1 MONTH)
GROUP BY
	c.parent_id
';
}
$sql = '
SELECT
	d.domain,
	a1d.*,
	a7d.*,
	a1m.*,
	t1d.*,
	t7d.*,
	t1m.*,
	l.*
FROM
	'.self::$table['link'].' l
INNER JOIN
	'.self::$table['domain'].' d ON l.domain_id = d.domain_id
LEFT JOIN
	('.$sql_1d.') AS a1d ON l.link_id = a1d.link_id
LEFT JOIN
	('.$sql_7d.') AS a7d ON l.link_id = a7d.link_id
LEFT JOIN
	('.$sql_1m.') AS a1m ON l.link_id = a1m.link_id
LEFT JOIN
	('.$sql_1dt.') AS t1d ON l.link_id = t1d.link_id
LEFT JOIN
	('.$sql_7dt.') AS t7d ON l.link_id = t7d.link_id
LEFT JOIN
	('.$sql_1mt.') AS t1m ON l.link_id = t1m.link_id
WHERE
	l.link_id IN ('.implode(',', $link_id_list).')
AND
	'.implode(' AND ', $and_list);
if (@$_REQUEST['sort_by'])
{
	$sql .= '
	ORDER BY
		'.preg_replace('/[^0-9a-zA-Z\-\_\.]+/is', '', sanitize_text_field($_REQUEST['sort_by']));
	if ($_REQUEST['sort_by'] == 'name' || $_REQUEST['sort_by'] == 'path')
	{
		$sql .= ' ASC';
	}
	else
	{
		$sql .= ' DESC';
	}
}
else
{
	$sql .= '
	ORDER BY
		l.created DESC';
}
$link_list = $wpdb->get_results($sql, ARRAY_A);
if ($link_list)
{
	$result =<<<END
<table class="sct_list_table" cellpadding="0" cellspacing="1" border="0">
<thead>
	<tr>
		<th width="1%" rowspan="2"> </th>
		<th width="25%" rowspan="2" onclick="sct_sortBy('name');">Title</th>
		<th width="30%" rowspan="2" onclick="sct_sortBy('path');">Share this redirect link...</th>
		<th rowspan="2"></th>
		<th style="text-align: center;" colspan="4">Unique Clicks</th>
		<th style="text-align: center;" colspan="4">Total Clicks</th>
		<th style="text-align: center;" rowspan="2" onclick="sct_sortBy('first_click');">First<br>Click</th>
		<th style="text-align: center;" rowspan="2" onclick="sct_sortBy('last_click');">Last<br>Click</th>
	</tr>
	<tr>
		<th style="text-align: center;" onclick="sct_sortBy('unique_clicks');">all</th>
		<th style="text-align: center;" onclick="sct_sortBy('unique_clicks');">1d</th>
		<th style="text-align: center;" onclick="sct_sortBy('unique_clicks');">7d</th>
		<th style="text-align: center;" onclick="sct_sortBy('unique_clicks');">1m</th>
		<th style="text-align: center;" onclick="sct_sortBy('total_clicks');">all</th>
		<th style="text-align: center;" onclick="sct_sortBy('total_clicks');">1d</th>
		<th style="text-align: center;" onclick="sct_sortBy('total_clicks');">7d</th>
		<th style="text-align: center;" onclick="sct_sortBy('total_clicks');">1m</th>
	</tr>
</thead>
<tbody>
END;
	foreach ($link_list as $link)
	{
		//$redirect = 'http://'.trim($link['domain'], '/').'/'.trim($link['path'], '/');
        $redirect = $schemes.trim($link['domain'], '/').'/'.trim($link['path'], '/');
		$edit_url = $_REQUEST['base_url'].'action=link_edit&link_id='.$link['link_id'];
		$first_click = '--';
		if ($link['first_click'] != '0000-00-00 00:00:00')
		{
			$first_click = date('Y-m-d', strtotime($link['first_click']));
		}
		$last_click = '--';
		if ($link['last_click'] != '0000-00-00 00:00:00')
		{
			$last_click = date('Y-m-d', strtotime($link['last_click']));
		}
		$result .= '<tr>';
		$result .= '<td width="1%"><a href="'.$edit_url.'"><img src="'.SCT_BASE_URL.'/includes/icons/cog.png" width="16" height="16" alt="edit" /></a></td>';
		$result .= '<td width="25%"><a href="'.$edit_url.'">'.$link['name'];
		if ($link['has_children'])
		{
			$result .= ' <img src="'.SCT_BASE_URL.'/includes/icons/arrow_divide.png" width="16" height="16" alt="Has split test" />';
		}
		if ($link['is_dead'])
		{
			$result .= ' <img src="'.SCT_BASE_URL.'/includes/icons/exclamation.png" width="16" height="16" alt="Bad destination URL detected" />';
		}
		$result .= '</a></td>';
		$result .= '<td width="30%"><input type="text" value="'.$redirect.'" onfocus="this.select();" onmouseup="return false;" style="width: 95%;" /></td>';
		$result .= '<td><a href="'.$redirect.'" target="_blank"><img src="'.SCT_BASE_URL.'/includes/icons/eye.png" width="16" height="16" alt="follow link" /></a></td>';
		$result .= '<td style="text-align: right;">'.number_format($link['unique_clicks']).'</td>';
		$result .= '<td style="text-align: right; white-space: nowrap;">'.number_format($link['unique_1d']).getTrendArrow($link['unique_1d'], $link['unique_1dt']).'</td>';
		$result .= '<td style="text-align: right; white-space: nowrap;">'.number_format($link['unique_7d']).getTrendArrow($link['unique_7d'], $link['unique_7dt']).'</td>';
		$result .= '<td style="text-align: right; white-space: nowrap;">'.number_format($link['unique_1m']).getTrendArrow($link['unique_1m'], $link['unique_1mt']).'</td>';
		$result .= '<td style="text-align: right;">'.number_format($link['total_clicks']).'</td>';
		$result .= '<td style="text-align: right; white-space: nowrap;">'.number_format($link['total_1d']).getTrendArrow($link['total_1d'], $link['total_1dt']).'</td>';
		$result .= '<td style="text-align: right; white-space: nowrap;">'.number_format($link['total_7d']).getTrendArrow($link['total_7d'], $link['total_7dt']).'</td>';
		$result .= '<td style="text-align: right; white-space: nowrap;">'.number_format($link['total_1m']).getTrendArrow($link['total_1m'], $link['total_1mt']).'</td>';
		$result .= '<td style="text-align: center;">'.$first_click.'</td>';
		$result .= '<td style="text-align: center;">'.$last_click.'</td>';
		$result .= '</tr>';
	}
	$result .= '<tbody>';
	$result .= '</table>';
	$prev_url = '';
	$next_url = '';
	if ($page > 1)
	{
		$prev_url = 'sct_goto_page('.($page - 1).')';
	}
	if ($page < $num_pages)
	{
		$next_url = 'sct_goto_page('.($page + 1).')';
	}
	if ($prev_url || $next_url)
	{
		$result .=<<<END
<table cellpadding="0" cellspacing="1" border="0" width="100%">
<thead>

		<tr><td width="33%" style="text-align: left;">
END;
		if ($prev_url)
		{
			$result .= '<button style="float: left" onclick="'.$prev_url.'">Prev</button>';
		}
		$result .=<<<END
		</td>
		<td width="33%" style="text-align: center;">
END;
		$result .= 'Page '.$page.' of '.$num_pages;
		$result .=<<<END
		</td>
		<td width="33%" style="text-align: right;">
END;
		if ($next_url)
		{
			$result .= '<button onclick="'.$next_url.'">Next</button>';
		}
		$result .=<<<END
		</td>
	</tr>
</thead>
<tbody>
END;
	}
    if ($prev_url || $next_url)
	{
		$result .=<<<END
<table width="100%">
<thead>

		<tr>
		<td width="100%" style="text-align: center;">
END;
$totalpage = $num_pages;
$t = $page;
if($t>0){
    if($totalpage-$t>10){
        $v = $t+10;    
    }else{
        $v = $t+($totalpage-$t);
    }
    $line=$v;
    for($i=$t; $i<=$v; $i++){
        $result.="<a style='cursor: pointer;' onclick='sct_goto_page($i)'>$i</a> ";
        if($line>$i){
            $result.="| ";
        }
    }
}
<<<END
		</td>
	</tr>
</thead>
<tbody>
END;
	}
}
else
{
	$result = '<table class="sct_list_table">';
	$result .= '<tr>';
	$result .= '<td class="empty">No Links</td>';
	$result .= '</tr>';
	$result .= '</table>';
}
exit($result);
function getTrendArrow($new, $old)
{
	$style = ' style="margin-left: 3px;"';
	if ($new > $old)
	{
		$arrow = '<img src="'.SCT_UP_ARROW_URL.'" width="5" height="12"'.$style.' />';
	}
	elseif ($new < $old)
	{
		$arrow = '<img src="'.SCT_DN_ARROW_URL.'" width="5" height="12"'.$style.' />';
	}
	else
	{
		$arrow = '<img src="'.SCT_NO_ARROW_URL.'" width="5" height="12"'.$style.' />';
	}
	return $arrow;
}