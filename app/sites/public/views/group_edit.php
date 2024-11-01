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
    $user_type = $sql_bydomain['user_type'];
}else{
    $user_type = 2;
    $assigned_user = Sct_Base::getActorUserId();
}

if ((int)@$_REQUEST['group_id'] && !self::$form_vars)
{
    if($user_type==2){
	   self::$form_vars = $wpdb->get_row('SELECT * FROM '.self::$table['group'].' WHERE group_id = '.(int)$_REQUEST['group_id'].' ', ARRAY_A);
    }else{
        self::$form_vars = $wpdb->get_row('SELECT * FROM '.self::$table['group'].' WHERE group_id = '.(int)$_REQUEST['group_id'].' AND user_id = "'.addslashes(Sct_Base::getActorUserId()).'"', ARRAY_A);
    }
}

$save_url	= $base_url.'app='.self::$name.'&action=group_save&group_id='.(int)@self::$form_vars['group_id'];
$delete_url	= $base_url.'app='.self::$name.'&action=group_delete&group_id='.(int)@self::$form_vars['group_id'];
$cancel_url	= $base_url.'action=group_grid';

?>
<script type="text/javascript">
function deleteRecord()
{
	if (window.confirm('Are you sure you want to delete this group?') == true)
	{
		window.location.href = '<?php _e($delete_url); ?>';
	}
}
</script>

<form action="<?php _e($save_url) ?>" method="POST" enctype="multipart/form-data" >
<input type="hidden" name="form_vars[group_id]" value="<?php _e(intval(self::$form_vars['group_id'])); ?>" />

<h2><?php if ((int)@self::$form_vars['group_id']){ ?>Edit<?php } else { ?>New<?php } ?> Group</h2>

<div style="clear: both;"></div>

<?php

Sct_Form::fadeSave();

Sct_Form::startTable();

Sct_Form::listErrors(self::$errors);

Sct_Form::text('Group Name', 'form_vars[name]', @self::$form_vars['name']);

Sct_Form::endTable();

?>
	<div class="sct_button_bar">
		<input type="submit" class="sct_button" value="Save" id="save_button" />
		<?php if (isset(self::$form_vars['group_id']) && intval(self::$form_vars['group_id'])){ ?>
		<input type="button" class="sct_button" value="Delete" onclick="deleteRecord();" />
		<?php } ?>
		<input type="button" class="sct_button" value="Back to List" onclick="window.location.href='<?php _e($cancel_url); ?>'" />
	</div>
</form>
