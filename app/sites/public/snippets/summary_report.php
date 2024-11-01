<?php

if (!@$_REQUEST['last'])
{
	$_REQUEST['last'] = 'month';
}

switch ($_REQUEST['last'])
{
	case 'month':
		$sql = '
		SELECT
			DATE_FORMAT(c.`date_time`, "%c/%e") AS `key`,
			COUNT(*) AS `total_clicks`,
			COUNT(DISTINCT c.`ip`) AS `unique_clicks`
		FROM
			'.Sct_Base::$table['click'].' c,
			'.Sct_Base::$table['link'].' l
		WHERE
			c.link_id = l.link_id
		AND
			l.user_id = '.Sct_Base::getActorUserId().'
		AND
			c.`date_time` >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
		GROUP BY
			DATE_FORMAT(c.`date_time`, "%c/%e")
		';
		break;
	
	case 'week':
		$sql = '
		SELECT
			DATE_FORMAT(c.`date_time`, "%c/%e") AS `key`,
			COUNT(*) AS `total_clicks`,
			COUNT(DISTINCT c.`ip`) AS `unique_clicks`
		FROM
			'.Sct_Base::$table['click'].' c,
			'.Sct_Base::$table['link'].' l
		WHERE
			c.link_id = l.link_id
		AND
			l.user_id = '.Sct_Base::getActorUserId().'
		AND
			c.`date_time` >= DATE_SUB(NOW(), INTERVAL 1 WEEK)
		GROUP BY
			DATE_FORMAT(c.`date_time`, "%c/%e")
		';
		break;
	
	case 'day':
		$sql = '
		SELECT
			DATE_FORMAT(c.`date_time`, "%H") AS `key`,
			COUNT(*) AS `total_clicks`,
			COUNT(DISTINCT c.`ip`) AS `unique_clicks`
		FROM
			'.Sct_Base::$table['click'].' c,
			'.Sct_Base::$table['link'].' l
		WHERE
			c.link_id = l.link_id
		AND
			l.user_id = '.Sct_Base::getActorUserId().'
		AND
			c.`date_time` >= DATE_SUB(NOW(), INTERVAL 1 DAY)
		GROUP BY
			DATE_FORMAT(c.`date_time`, "%H")
		';
		break;

	case 'hour':
		$sql = '
		SELECT
			DATE_FORMAT(c.`date_time`, "%i") AS `key`,
			COUNT(*) AS `total_clicks`,
			COUNT(DISTINCT c.`ip`) AS `unique_clicks`
		FROM
			'.Sct_Base::$table['click'].' c,
			'.Sct_Base::$table['link'].' l
		WHERE
			c.link_id = l.link_id
		AND
			l.user_id = '.Sct_Base::getActorUserId().'
		AND
			c.`date_time` >= DATE_SUB(NOW(), INTERVAL 1 DAY)
		GROUP BY
			DATE_FORMAT(c.`date_time`, "%i")
		';
		break;
}

$sql .= ' ORDER BY c.`date_time`';

$data_list = $wpdb->get_results($sql, ARRAY_A);

if ($data_list)
{
?>
<div id="chart1_div" style="width: 99%; height: 200px; border: 1px solid #DDD;"></div>

<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">

jQuery('.sct_filter_select').change(function(){
	var domain_id = jQuery('#sct_filter_domain').val();
	var link_id = jQuery('#sct_filter_link').val();
	var last = jQuery('#sct_filter_last').val();
	window.location.href = '<?php _e($base_url); ?>?action=report_summary&domain_id=' + domain_id + '&link_id=' + link_id + '&last=' + last;
});

google.load("visualization", "1", {packages:["corechart"]});

google.setOnLoadCallback(drawChart1);
function drawChart1() {
	var data = google.visualization.arrayToDataTable([

<?php

	switch ($_REQUEST['last'])
	{
		case 'month':
			_e("['Day', 'Total Clicks', 'Unique Clicks'],\n");
			break;
		
		case 'week':
			_e("['Day', 'Total Clicks', 'Unique Clicks'],\n");
			break;
		
		case 'day':
			_e("['Hour', 'Total Clicks', 'Unique Clicks'],\n");
			break;
	
		case 'hour':
			_e("['Minute', 'Total Clicks', 'Unique Clicks'],\n");
			break;
	}

	foreach ($data_list as $key => $data)
	{
		_e("['".$data['key']."', ".(int)$data['total_clicks'].", ".(int)$data['unique_clicks']."],\n");
	}

?>
	]);

	var options = { title: 'Clicks' };

	var chart1 = new google.visualization.LineChart(document.getElementById('chart1_div'));
	chart1.draw(data, options);
}
</script>
<br />
<?php
}
