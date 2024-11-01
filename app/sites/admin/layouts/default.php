<script type="text/javascript" src="<?php _e(SCT_BASE_URL); ?>/includes/xbrander_admin.js"></script>

<div id="sct_admin">

	<h1 style="margin: 5px;"><img src="<?php _e(SCT_BASE_URL); ?>/includes/images/xbrander_logo.png" width="225" height="40" alt="xBrander" title="xBrander" border="0" /><span style="font-size: 12px;"> v<?php _e(SCT_VERSION); ?></span></h1>

	<table id="sct_layout_table" style="width: 800px;">
	<tr>
		<td>
			<ul id="sct_nav_tabs">
				<li<?php if (self::$action == 'files' || self::$action == 'file_attributes'){ _e(' class="selected"'); } ?>><a href="admin.php?page=<?php _e(self::$name); ?>&action=files">Files</a></li>
				<li<?php if (self::$action == 'settings'){ _e(' class="selected"'); } ?>><a href="admin.php?page=<?php  _e(self::$name); ?>&action=settings">Settings</a></li>
			</ul>
			<div id="sct_view_wrapper">
				<div id="sct_view_box">
					<div style="margin: 15px;">
					<?php require($view_path); ?>
					</div>
				</div>
				<div id="sct_spacer"></div>
				<div style="clear: both;"></div>
			</div>
		</td>
	</tr>
	</table>

</div>