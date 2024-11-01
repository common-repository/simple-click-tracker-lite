<?php
global $wpdb;
self::updateFileList();
$file_list = $wpdb->get_results('SELECT * FROM '.self::$table['file'].' ORDER BY title', ARRAY_A);
?>
	<div class="sct_button_bar" style="padding-bottom: 10px;">
		<!-- <input type="button" value=" Upload New File " onclick="window.location.href='admin.php?page=<?php _e(self::$name); ?>&action=file_edit'" class="action" /> -->
		<p>Click the file title or name to edit search and replace attributes.</p>
	</div>
	<table class="sct_list_table" cellspacing="1" cellpadding="0">
		<tr>
			<th width="99%">File</th>
			<th width="1%">Shortcode</th>
		</tr>
<?php
if ($file_list)
{
	$i = 0;
	$total = count($file_list);
	foreach ($file_list as $file)
	{
?>
		<tr>
			<td>
				<strong style="color: #900;"><?php _e(strip_tags($file['title'])); ?></strong><br />
				&nbsp;&nbsp;&nbsp;&nbsp;<?php _e($file['original_file_name']); ?>
			</td>
			<td align="center" nowrap="nowrap" style="padding-left: 10px; padding-right: 10px;">
				<?php _e(htmlspecialchars('[xbrander file='.$file['file_key'].']')); ?>
			</td>
		</tr>
<?php
	}
}
else
{
?>
		<tr>
			<td class="sct_empty" colspan="2" align="center">
				<br />
				To add files, log-in go to the <a href="http://xbrander.com/dashboard" target="_blank">xBrander dashboard</a><br />
				<br />
			</td>
		</tr>
<?php
}
?>
	</table>