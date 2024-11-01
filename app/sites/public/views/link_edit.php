<?php
if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
    $schemes = 'https://';
else
    $schemes = 'http://';
    
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
if ((int)@$_REQUEST['link_id'] && !self::$form_vars)
{
    if($user_type!=2){
	   self::$form_vars = $wpdb->get_row('SELECT * FROM '.self::$table['link'].' WHERE link_id = '.sanitize_text_field((int)$_REQUEST['link_id']).'', ARRAY_A);
    }else{
        self::$form_vars = $wpdb->get_row('SELECT * FROM '.self::$table['link'].' WHERE link_id = '.sanitize_text_field((int)$_REQUEST['link_id']).'', ARRAY_A);
    }
	if ((int)@$_REQUEST['copy'])
	{
		self::$form_vars['link_id'] = 0;
		self::$form_vars['name'] = 'Copy of '.self::$form_vars['name'];	

	}
}
$save_url	= $base_url.'app='.self::$name.'&action=link_save&link_id='.(int)@self::$form_vars['link_id'];
$delete_url	= $base_url.'app='.self::$name.'&action=link_delete&link_id='.(int)@self::$form_vars['link_id'];
if (@self::$form_vars['has_children'])
{
	$cancel_url	= $base_url.'app='.self::$name.'&action=split_test_grid';
}
else
{
	$cancel_url	= $base_url;
}
if (!(int)@self::$form_vars['link_id'])
{
	if ((int)@$_REQUEST['domain_id'])
	{
		//self::$form_vars['domain_id'] = (int)@$_REQUEST['domain_id'];
	}
	else
	{
		//self::$form_vars['domain_id'] = get_user_meta(get_current_user_id(), 'sct_dflt_domain_id', true);
	}
	if (trim(@$_REQUEST['path']))
	{
		self::$form_vars['path'] = trim(sanitize_text_field($_REQUEST['path']), '/');
	}
	else
	{
		//self::$form_vars['group_id'] = get_user_meta(get_current_user_id(), 'sct_dflt_group_id', true);	
	}
}
$group_save_url	= SCT_AJAX_URL.'&do=group_quick_add';

?>
<script type="text/javascript">
function deleteRecord()
{
	if (window.confirm('Are you sure you want to delete this link?') == true)
	{
		window.location.href = '<?php _e($delete_url); ?>';
	}
}
function copyAsNew()
{
	window.location.href = '<?php _e($base_url); ?>action=link_edit&link_id=<?php _e(sanitize_text_field($_REQUEST['link_id'])); ?>&copy=1';
}
var sctfb_thumbailimage = function(title,onInsert,isMultiple){
  if(isMultiple == undefined)
   isMultiple = false;
  // Media Library params
  var frame = wp.media({
   title   : title,
   multiple  : isMultiple,
   library  : { type : 'image'},
   button   : { text : 'Insert' }
  });
  // Runs on select
  frame.on('select',function(){
   var objSettings = frame.state().get('selection').first().toJSON();
   var selection = frame.state().get('selection');
   var arrImages = [];
   if(isMultiple == true){ //return image object when multiple
    selection.map( function( attachment ) {
     var objImage = attachment.toJSON();
     var obj = {};
     obj.url = objImage.url;
     obj.id  = objImage.id;
     arrImages.push(obj);
    });
    onInsert(arrImages);
   }else{
    //return image url and id - when single
    sct_thumbail_file(objSettings.url);
    // onInsert("sdsds");
    // console.log(objSettings);
   }
  });
  // Open ML
  frame.open();
 }
 function sct_thumbail_file(img){
    jQuery('#fb_image').val('');
    jQuery('#fb_image').val(img);
 }
 function sctfb_onchng(){
    jQuery('#img_scts').click();
 }
 jQuery(document).ready(function(){
    
        jQuery('#imageUploadForm').on('submit',(function(e) {
        e.preventDefault();
        jQuery('#sct_img_loading').show();
        var formData = new FormData(this);
        jQuery.ajax({
            type:'POST',
            url: "<?php _e(admin_url()) ?>admin-ajax.php?action=sctimgupload",
            data:formData,
            cache:false,
            contentType: false,
            processData: false,
            success:function(name){
                jQuery('#sct_img_loading').hide();
                if(name!=''){
                jQuery('#fb_image').val('');
                jQuery('#fb_image').val(name);
                }else{
                    alert("An error occurred. Please Try Again.");
                }
            }
        });
    }));
    jQuery("#img_scts").on("change", function() {
        jQuery("#imageUploadForm").submit();
    });

    
 });
</script>
<style>
.qr_form_list{
    margin-bottom: 25px;
    text-align: left;
}
.qr_form{
    display: grid;
    justify-content: center;
    
}
.qr_form_list input {
    padding:0px 10px !important;
    font-size: 12px !important;
    margin-top: 10px;

}
.qr_form_list label{
    font-size: 15px;
    margin-bottom: 15px;
}


input, select {
	font-size: 16px !important;
	line-height: 24px !important;
	padding: 3px !important;
}
th {
	font-size: 14px !important;
	line-height: 24px !important;
	padding: 10px 3px 3px 3px !important;
}
tr{
    width: 99%;
}

</style>
<form  style="display: none;" id="imageUploadForm" enctype="multipart/form-data" >
<input type="file" id="img_scts" name="img_url" />
</form>

<form action="<?php _e($save_url) ?>" method="POST" enctype="multipart/form-data" >
<input type="hidden" name="form_vars[link_id]" value="<?php   _e(intval(self::$form_vars['link_id'])) ?>" />
<h2><?php if ((int)@self::$form_vars['link_id']){ ?>Edit<?php } else { ?>New<?php } ?> Link</h2>
<div style="clear: both;"></div>
<?php

Sct_Form::fadeSave();
Sct_Form::startTable();
Sct_Form::listErrors(self::$errors);
?>



	<div class="sct_button_bar">
		<input type="submit" class="button" value="Save" name="save" id="save_button" style="padding: 3px 10px !important;" />
        <input type="submit" class="button" value="Save & Close" name="save" id="save_button" style="padding: 3px 10px !important;" />
        <input type="submit" class="button" value="Save & New" name="save" id="save_button" style="padding: 3px 10px !important;" />
		<?php if (isset(self::$form_vars['link_id']) && intval(self::$form_vars['link_id'])){ ?>
		<input type="button" class="button" value="Delete" onclick="deleteRecord();" style="padding: 3px 10px !important;" />
		<?php } ?>
		<input type="button" class="button" value="Cancel" onclick="window.location.href='<?php _e($cancel_url) ?>'" style="padding: 3px 10px !important;" />
        <input type="button" class="button" value="Back to List" onclick="window.location.href='<?php _e($cancel_url) ?>'" style="padding: 3px 10px !important;" />
		<?php if (isset(self::$form_vars['link_id']) && intval(self::$form_vars['link_id'])){ ?>
		<input type="button" class="button" value="Copy Link As New..." onclick="copyAsNew();" style="padding: 3px 10px !important;" />
		<?php } ?>
	</div>
<?php
Sct_Form::text('Title', 'form_vars[name]', stripcslashes(@self::$form_vars['name']));
?>
<tr>
    <th>Description</th>
    <td><textarea name="form_vars[description]" rows="3" style="width: 74%;"><?php _e(stripcslashes(@self::$form_vars['description'])); ?></textarea></td>
</tr>
<?php
if($user_type!=2){
    $group_option_list = self::getGroupOptionList(true);
}else{
    $group_option_list = self::getGroupOptionLists(true);
}
$caption =<<<END
<button type="button" onclick="sct_new_group()" style="padding: 3px 10px !important;">Add New Group</button>
<div id="group_name_div" style="display: none;">
Group Name:
<input type="text" id="group_name" name="group_name" size="20" />
<input type="button" id="group_name_submit" value="Save" />
</div>
END;
Sct_Form::select('Group', 'form_vars[group_id]', @self::$form_vars['group_id'], $group_option_list, $caption);
Sct_Form::clearRow();
$sct_last_domain_ID = get_option("sct_last_domain_ID");
?>
<tr>
	<th>Redirect&nbsp;URL:</th>
	<td>
		<table style="margin: 0;">
			<td valign="top"><?php _e($schemes); ?></td>
			<td width="25%" valign="top">
				<select name="form_vars[domain_id]" style="width: 100%;">
<?php
if($user_type==2){
$domain_option_list = self::getDomainOptionListmulti($assigned_user);
}else{
    $domain_option_list = self::getDomainOptionListmulti_domain($assigned_domains);
}
$selected_domain = "";
foreach ($domain_option_list as $domain_id => $name)
{
	$selected = '';
	if (@self::$form_vars['domain_id'] == $domain_id)
	{
		//$selected = 'selected="selected"';
        $selected = "selected='selected'";
        $selected_domain = $name;
	}
     if(!isset($_GET['link_id'])){
        if (@$sct_last_domain_ID == $domain_id)
    	{
            $selected = "selected='selected'";
            $selected_domain = $name;
    	}
    }
    _e("<option value='$domain_id' $selected>$name</option>");
}

$QRCODEURLREDIRECT =  urlencode($schemes.$selected_domain."/".htmlspecialchars(@self::$form_vars['path'])); 
?>
				</select>
			</td>
			<td valign="top">/</td>
			<td width="75%" valign="top">
				<input type="text" name="form_vars[path]" value="<?php _e(htmlspecialchars(@self::$form_vars['path'])); ?>" style="width: 100%;" /><br />
				<p style="font-size: 14px; line-height: 18px;">The second half of your redirect link, the text after the domain.<br />Characters allowed: A-Z, a-z, 0-9, -, _ <br />Ex. /my-special_offer</p>
			</td>
		</table>
	</td>
</tr>
<?php

$caption = '<p style="font-size: 14px; line-height: 18px;">The URL your visitor is redirect to after clicking your link</p>';
Sct_Form::text('Destination&nbsp;URL', 'form_vars[url]', @self::$form_vars['url'], NULL, $caption);

if(@self::$form_vars['query_string']==0){
    $checked = "checked='checked'";
}else{
    $checked = "";
}
?>
<tr>
    <th valign="top">Check query string for <br />tracking data and to<br /> enable inactive links:</th>
    <td><input type="checkbox" name="query_string" value="0" <?php _e($checked); ?> /></td>
</tr>

    
<?php
Sct_Form::endTable();
?>

	<div style="clear: both;"></div>
	<br /><br />
	<div class="sct_button_bar">
		<input type="submit" class="button" value="Save" name="save" id="save_button" style="padding: 3px 10px !important;" />
        <input type="submit" class="button" value="Save & Close" name="save" id="save_button" style="padding: 3px 10px !important;" />
        <input type="submit" class="button" value="Save & New" name="save" id="save_button" style="padding: 3px 10px !important;" />
		<?php if (isset(self::$form_vars['link_id']) && intval(self::$form_vars['link_id'])){ ?>
		<input type="button" class="button" value="Delete" onclick="deleteRecord();" style="padding: 3px 10px !important;" />
		<?php } ?>
		<input type="button" class="button" value="Cancel" onclick="window.location.href='<?php _e($cancel_url) ?>'" style="padding: 3px 10px !important;" />
        <input type="button" class="button" value="Back to List" onclick="window.location.href='<?php _e($cancel_url) ?>'" style="padding: 3px 10px !important;" />
		<?php if (isset(self::$form_vars['link_id']) && intval(self::$form_vars['link_id'])){ ?>
		<input type="button" class="button" value="Copy Link As New..." onclick="copyAsNew();" style="padding: 3px 10px !important;" />
		<?php } ?>
	</div>
</form>


<script id="dest_url_template" type="text/plain">
<?php
Sct_Form::text_funnel('Alt. Destination URL #xxx', 'form_vars[alt_url][xxx]', @self::$form_vars['alt_url']['xxx'],@self::$form_vars['link_ratio']['xxx'],'form_vars[link_ratio][xxx]',@self::$form_vars['lk_id']['xxx'],'form_vars[funnel_link_id]['.$i.']');
?>
</script>
<script type="text/javascript">
function reset_curent_link(){
    var c = window.confirm("Are you sure to reset?");
    if(c){
    var v = jQuery('#reset_ids').val();
    jQuery.post("<?php _e(admin_url()) ?>admin-ajax.php?action=sct_reset_split_cal","ids="+v,function(data){
        jQuery('.visit_uniq').html('0');
        jQuery('.goal_uniq').html('0');
        jQuery('.t_goal_uniq').html('0%');
    });
    }
}


function sct_new_group(){
	jQuery('#group_name_div').show();
	return false;
}
jQuery('#group_name_submit').click(function(){
	jQuery.ajax({
		url: '<?php _e($group_save_url) ?>',
		type: 'POST',
		data: { name: jQuery('#group_name').val(), user_id: '<?php _e(Sct_Base::getActorUserId()); ?>' },
		dataType: 'json',
		cache: false
	}).done(function(data){
		jQuery('#group_name_div').hide();
		if (data.success)
		{
			jQuery('#form_vars_group_id').html(data.option_list);
		}
		else
		{
			alert(data.error);
		}
	}).fail(function(){
		alert('Sorry! There seems to be a problem talking to the server. Please try again.');
	});
});
</script>