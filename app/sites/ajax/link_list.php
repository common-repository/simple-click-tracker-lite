<?php

$result = '';

$and_list = array();

$and_list[] = 'l.user_id = "'.addslashes(@sanitize_text_field($_REQUEST['user_id'])).'"';

$and_list[] = 'l.parent_id = 0';

if (isset($_REQUEST['search_string']))
{
	$or_list = array();

	$part_list = explode(' ', sanitize_text_field($_REQUEST['search_string']));

	foreach ($part_list as $part)
	{
		$part = trim($part);

		if ($part)
		{
			$or_list[] = 'l.name LIKE "%'.addslashes($part).'%"';
			$or_list[] = 'l.path LIKE "%'.addslashes($part).'%"';
			$or_list[] = 'l.url LIKE "%'.addslashes($part).'%"';
		}
	}

	if ($or_list)
	{
		$and_list[] = '('.implode(' OR ', $or_list).')';
	}
}

$sql_1d = '
SELECT
	c.`link_id`,
	COUNT(DISTINCT c.`ip`) AS unique_1d,
	COUNT(*) AS total_1d
FROM
	'.self::$table['click'].' c,
	'.self::$table['link'].' l
WHERE
	(c.link_id = l.link_id OR c.parent_id = l.link_id)
AND
	l.user_id = "'.addslashes(@sanitize_text_field($_REQUEST['user_id'])).'"
AND
	c.`date_time` >= DATE_SUB(NOW(), INTERVAL 1 DAY)
GROUP BY
	c.parent_id
';

$sql_1dt = '
SELECT
	c.`link_id`,
	COUNT(DISTINCT c.`ip`) AS unique_1dt,
	COUNT(*) AS total_1dt
FROM
	'.self::$table['click'].' c,
	'.self::$table['link'].' l
WHERE
	(c.link_id = l.link_id OR c.parent_id = l.link_id)
AND
	l.user_id = "'.addslashes(@sanitize_text_field($_REQUEST['user_id'])).'"
AND
	c.`date_time` >= DATE_SUB(NOW(), INTERVAL 2 DAY)
AND
	c.`date_time` < DATE_SUB(NOW(), INTERVAL 1 DAY)
GROUP BY
	c.parent_id
';

$sql_7d = '
SELECT
	c.`link_id`,
	COUNT(DISTINCT c.`ip`) AS unique_7d,
	COUNT(*) AS total_7d
FROM
	'.self::$table['click'].' c,
	'.self::$table['link'].' l
WHERE
	(c.link_id = l.link_id OR c.parent_id = l.link_id)
AND
	l.user_id = "'.addslashes(@sanitize_text_field($_REQUEST['user_id'])).'"
AND
	c.`date_time` >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY
	c.parent_id
';

$sql_7dt = '
SELECT
	c.`link_id`,
	COUNT(DISTINCT c.`ip`) AS unique_7dt,
	COUNT(*) AS total_7dt
FROM
	'.self::$table['click'].' c,
	'.self::$table['link'].' l
WHERE
	(c.link_id = l.link_id OR c.parent_id = l.link_id)
AND
	l.user_id = "'.addslashes(@sanitize_text_field($_REQUEST['user_id'])).'"
AND
	c.`date_time` >= DATE_SUB(NOW(), INTERVAL 14 DAY)
AND
	c.`date_time` < DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY
	c.parent_id
';

$sql_1m = '
SELECT
	c.`link_id`,
	COUNT(DISTINCT c.`ip`) AS unique_1m,
	COUNT(*) AS total_1m
FROM
	'.self::$table['click'].' c,
	'.self::$table['link'].' l
WHERE
	(c.link_id = l.link_id OR c.parent_id = l.link_id)
AND
	l.user_id = "'.addslashes(@sanitize_text_field($_REQUEST['user_id'])).'"
AND
	c.`date_time` >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
GROUP BY
	c.parent_id
';

$sql_1mt = '
SELECT
	c.`link_id`,
	COUNT(DISTINCT c.`ip`) AS unique_1mt,
	COUNT(*) AS total_1mt
FROM
	'.self::$table['click'].' c,
	'.self::$table['link'].' l
WHERE
	(c.link_id = l.link_id OR c.parent_id = l.link_id)
AND
	l.user_id = "'.addslashes(@sanitize_text_field($_REQUEST['user_id'])).'"
AND
	c.`date_time` >= DATE_SUB(NOW(), INTERVAL 2 MONTH)
AND
	c.`date_time` < DATE_SUB(NOW(), INTERVAL 1 MONTH)
GROUP BY
	c.parent_id
';

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
	'.implode(' AND ', $and_list);

if (isset($_REQUEST['sort_by']))
{
	$sql .= '
	ORDER BY
		'.preg_replace('/[^0-9a-zA-Z\-\_\.]+/is', '', sanitize_text_field($_REQUEST['sort_by'] ));

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
		<th width="1%" rowspan="2">&nbsp;</td>

		<th width="25%" rowspan="2" onclick="sct_sortBy('name');">Title</td>
		<th width="30%" rowspan="2" onclick="sct_sortBy('path');">Share this redirect link...</td>

		<th style="text-align: center;" colspan="4">Unique Clicks</td>

		<th style="text-align: center;" colspan="4">Total Clicks</td>

		<th style="text-align: center;" rowspan="2" onclick="sct_sortBy('first_click');">First<br>Click</td>
		<th style="text-align: center;" rowspan="2" onclick="sct_sortBy('last_click');">Last<br>Click</td>
	</tr>
	<tr>
		<th style="text-align: center;" onclick="sct_sortBy('unique_clicks');">all</td>
		<th style="text-align: center;" onclick="sct_sortBy('unique_1d');">1d</td>
		<th style="text-align: center;" onclick="sct_sortBy('unique_7d');">7d</td>
		<th style="text-align: center;" onclick="sct_sortBy('unique_1m');">1m</td>

		<th style="text-align: center;" onclick="sct_sortBy('total_clicks');">all</td>
		<th style="text-align: center;" onclick="sct_sortBy('total_1d');">1d</td>
		<th style="text-align: center;" onclick="sct_sortBy('total_7d');">7d</td>
		<th style="text-align: center;" onclick="sct_sortBy('total_1m');">1m</td>
	</tr>
</thead>
<tbody>
END;

	foreach ($link_list as $link)
	{
		$redirect = 'http://'.trim($link['domain'], '/').'/'.trim($link['path'], '/');

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
			$result .= ' (SP)';
		}

		$result .= '</a></td>';
		$result .= '<td width="30%" style="white-space: nowrap;"><input type="text" value="'.$redirect.'" onfocus="this.select();" onmouseup="return false;" style="width: 95%;" /><a href="'.$redirect.'" target="_blank"><img src="'.SCT_BASE_URL.'/includes/icons/eye.png" width="16" height="16" alt="follow link" /></a></td>';

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