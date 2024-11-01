<?php
$link_list = $wpdb->get_results('SELECT * FROM '.self::$table['link'].' WHERE user_id = '.Sct_Base::getActorUserId().' ORDER BY link', ARRAY_A);
?>
	<div class="sct_button_bar">
		<button onclick="window.location.href='<?php _e($base_url); ?>action=link_edit'" class="sct_button">
			Add Domain
		</button>
	</div>

	<table class="sct_list_table" cellspacing="1" cellpadding="0">
		<tr>
			<th>&nbsp;</th>
			<th width="98%">Domain</th>
		</tr>
<?php
if ($link_list)
{
	foreach ($link_list as $link)
	{
		$edit_url = $base_url.'action=link_edit&test=1&link_id='.(int)$link['link_id'];
?>
		<tr>
			<td align="right" nowrap="nowrap">
				<a href="<?php _e($edit_url); ?>"><img src="<?php _e(SCT_BASE_URL); ?>/includes/icons/pencil.png" width="16" height="16" alt="Edit" link="Edit" border="0" /></a>
			</td>
			<td><a href="<?php _e($edit_url); ?>" link="Edit"><?php _e(strip_tags($link['link'])); ?></a></td>
		</tr>
<?php
	}
}
else
{
?>
		<tr>
			<td class="sct_empty" colspan="2" align="center"><br />Empty<br /><br /></td>
		</tr>
<?php
}

?>
</table>