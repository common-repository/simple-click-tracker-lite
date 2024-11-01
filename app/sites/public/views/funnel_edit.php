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

if ((int)@$_REQUEST['funnel_id'] && !self::$form_vars)
{
    if($user_type = 2){
	   self::$form_vars = $wpdb->get_row('SELECT * FROM '.self::$table['funnel'].' WHERE funnel_id = '.sanitize_text_field((int)$_REQUEST['funnel_id']).' ', ARRAY_A);
    }else{
        self::$form_vars = $wpdb->get_row('SELECT * FROM '.self::$table['funnel'].' WHERE funnel_id = '.sanitize_text_field((int)$_REQUEST['funnel_id']).' AND user_id = "'.addslashes(Sct_Base::getActorUserId()).'"', ARRAY_A);
    }
}

$save_url	= $base_url.'app='.self::$name.'&action=funnel_save&funnel_id='.(int)@self::$form_vars['funnel_id'];
$delete_url	= $base_url.'app='.self::$name.'&action=funnel_delete&funnel_id='.(int)@self::$form_vars['funnel_id'];
$cancel_url	= $base_url.'action=funnel_grid';
?>
<script type="text/javascript">
function deleteRecord()
{
	if (window.confirm('Are you sure you want to delete this funnel?') == true)
	{
		window.location.href = '<?php _e($delete_url) ?>';
	}
}
</script>

<form action="<?php _e($save_url); ?>" method="POST" enctype="multipart/form-data" >
<input type="hidden" name="form_vars[funnel_id]" value="<?php _e(intval(self::$form_vars['funnel_id'])); ?>" />

<h2><?php if ((int)@self::$form_vars['funnel_id']){ ?>Edit<?php } else { ?>New<?php } ?> Funnel</h2>

<div style="clear: both;"></div>

<?php

Sct_Form::fadeSave();

?>
<table class="sct_form_table" cellpadding="0" cellspacing="5" width="100%" style="margin: 0;">
<?php

Sct_Form::listErrors(self::$errors);

Sct_Form::text('Funnel&nbsp;Name', 'form_vars[name]', @self::$form_vars['name']);

Sct_Form::clearRow();
if(@self::$form_vars['funnel_type']==1){
    $a1 = "checked='checked'";
    $a2 = "";
    $d = "display:none";
}else{
    $a1 = "";
    $a2 = "checked='checked'";
    $d = "";
}
?>
<tr>
    <td colspan="2">
    <table width="100%">
        <tr>
            <td>
                <table width="100%">
                    <tr>
                        <th>Funnel Type: </th>
                        <td>Opt In <input onclick="funnel_type(this.value)" type="radio" <?php _e($a1); ?> value="1" name="form_vars[funnel_type]"  /> &nbsp;
                            Front End Sales <input onclick="funnel_type(this.value)" type="radio" <?php _e($a2); ?> value="0" name="form_vars[funnel_type]" /></td>
                    </tr>
                    
                    <?php
                    if(@self::$form_vars['no_of_up']!=''){
                        $no_up = @self::$form_vars['no_of_up'];
                    }else{
                        $no_up = 1;
                    }
                    if(@self::$form_vars['no_of_dw']!=''){
                        $no_dw = @self::$form_vars['no_of_dw'];
                    }else{
                        $no_dw = 1;
                    }
                    if(@self::$form_vars['no_of_t']!=''){
                        $no_t = @self::$form_vars['no_of_t'];
                    }else{
                        $no_t = 1;
                    }
                    Sct_Form::text_N('# Of Upsells', 'form_vars[no_of_up]', $no_up,20); //# of upsells || form_vars_no_of_up = 0
                    Sct_Form::text_N('# Of Downsells', 'form_vars[no_of_dw]', $no_dw,20); //# of downsells || form_vars_no_of_dw = 1;
                    Sct_Form::text_N('# Of Thank you Page', 'form_vars[no_of_t]', $no_t,20); //# of downsells || form_vars_no_of_dw = 1;
                    ?>
                    <input type="hidden" id="up_sells" value="<?php _e($no_up); ?>" />
                    <input type="hidden" id="down_sells" value="<?php _e($no_dw); ?>" />
                    <input type="hidden" id="t_sells" value="<?php _e($no_t); ?>" />
                    <tr>
                        <th>Start Date</th>
                        <td><input type="text" name="start_date" id="datepicker" value="<?php _e(@self::$form_vars['start_date']); ?>" size="30" /></td>
                    </tr>
                    <tr>
                        <th>End Date</th>
                        <td><input type="text" name="end_date" id="datepicker1" value="<?php _e(@self::$form_vars['end_date']); ?>" size="30" /></td>
                    </tr>
                </table>
            </td>
            <td>
                <table width="100%">
                    <tr>
                        <th>Click Cost</th>
                        <td><input type="text" name="c_cost"  value="<?php _e(@self::$form_vars['c_cost']); ?>" size="30" /></td>
                    </tr>
                    <tr>
                        <th>Funnel Cost</th>
                        <td><input type="text" name="f_cost"  value="<?php _e(@self::$form_vars['f_cost']); ?>" size="30" /></td>
                    </tr>
                </table>
            </td>
        </tr>
        
    </table>
    </td>
</tr>
<?php
$k = 0;
$funnel_link_list_l = array();
$t_funnel_link_list_s = $wpdb->get_results('SELECT * FROM '.Sct_Base::$table['funnel_link_new'].' WHERE funnel_id = '.(int)@self::$form_vars['funnel_id'].' and funnel_type="2" ORDER BY sort_order, funnel_link_id', ARRAY_A);
foreach ($t_funnel_link_list_s as $t_funnel_link)
{
	$k++;
	$funnel_link_list_l[$k] = $t_funnel_link;
}
//funnel Type 0 for upsells
$i = 0;
$funnel_link_list = array();
$t_funnel_link_list = $wpdb->get_results('SELECT * FROM '.Sct_Base::$table['funnel_link_new'].' WHERE funnel_id = '.(int)@self::$form_vars['funnel_id'].' and funnel_type="0" ORDER BY sort_order, funnel_link_id', ARRAY_A);
foreach ($t_funnel_link_list as $t_funnel_link)
{
	$i++;
	$funnel_link_list[$i] = $t_funnel_link;
}
//funnel Type 1 for Downsells
$j = 0;
$funnel_link_listd = array();
$t_funnel_link_lists = $wpdb->get_results('SELECT * FROM '.Sct_Base::$table['funnel_link_new'].' WHERE funnel_id = '.(int)@self::$form_vars['funnel_id'].' and funnel_type="1" ORDER BY sort_order, funnel_link_id', ARRAY_A);
foreach ($t_funnel_link_lists as $t_funnel_link)
{
	$j++;
	$funnel_link_listd[$j] = $t_funnel_link;
}
//funnel Type 3 for Thank You Page
$l = 0;
$funnel_link_listt = array();
$t_funnel_link_listt = $wpdb->get_results('SELECT * FROM '.Sct_Base::$table['funnel_link_new'].' WHERE funnel_id = '.(int)@self::$form_vars['funnel_id'].' and funnel_type="3" ORDER BY sort_order, funnel_link_id', ARRAY_A);
foreach ($t_funnel_link_listt as $t_funnel_link)
{
	$l++;
	$funnel_link_listt[$l] = $t_funnel_link;
}


$sql = '
SELECT
	l.link_id,
	l.name,
	d.domain
FROM
	'.Sct_Base::$table['link'].' l,
	'.Sct_Base::$table['domain'].' d
WHERE
	l.domain_id = d.domain_id
AND
	l.user_id = '.Sct_Base::getActorUserId().'
AND
	l.parent_id = 0
ORDER BY
	d.domain,
	l.name
';

$link_list = array(0 => '-- none --');
$t_list_list = $wpdb->get_results($sql, ARRAY_A);
foreach ($t_list_list as $t_list)
{
	$link_list[$t_list['link_id']] = '['.$t_list['domain'].'] '.$t_list['name'];
}

$min = $no_up;

Sct_Form::$return = 'value';

$row = '<tr>';
$row .= '	<td colspan="2">';
$row .= '		<table id="sct_goal_page_grid">';
$row .= '			<tr>';
$row .= '				<th style="text-align: center; width: 55%;">Goal&nbsp;Pages &nbsp;&nbsp;&nbsp;&nbsp;</th>';
$row .= '				<th style="text-align: center; width: 55%;">Redirect&nbsp;Link &nbsp;&nbsp;&nbsp;&nbsp;</th>';
$row .= '				<th style="text-align: center; width: 100px;">Conv.&nbsp;Value</th>';
$row .= '			</tr>';

?>
<style>
.funl tr th{
    width: 100px !important;
}
</style>
<?php
for ($i = 1; $i <= 1; $i++)
{
	$row .= '<tr >';
	$row .= '	<td style="width: 55%;">';
	$row .= '		<table class="funl">';
    $row .= Sct_Form::text("Landing Page", 'goal_vars_landing['.$i.'][link_id]', @$funnel_link_list_l[$i]['funnel_url'], 30);
	$row .= '		</table>';
	$row .= '	</td>';
	$row .= '	<td style="width: 55%;">';
	$row .= '		<table class="funl">';
    $row .= Sct_Form::text("", 'goal_vars_landing['.$i.'][redirect_link]', @$funnel_link_list_l[$i]['red_url'], 30);
	$row .= '		</table>';
	$row .= '	</td>';
	$row .= '	<td style="width: 100px;">';
	$row .= '		<table style="width: 100px;" id="opt_value" class="funl">';
	$row .= Sct_Form::text('', 'goal_vars_landing['.$i.'][conv_value]', doubleval(@$funnel_link_list_l[$i]['conv_value']),30);
	$row .= '		</table> &nbsp; &nbsp;';

	$row .= '	</td>';
	
	$row .= '</tr>';
}
for ($i = 1; $i <= $min; $i++)
{
	$row .= '<tr class="r_u" id="ru_'.$i.'">';
	$row .= '	<td style="width: 55%;">';
	$row .= '		<table class="funl">';
	$row .= Sct_Form::text("U".$i, 'goal_vars_u['.$i.'][link_id]', @$funnel_link_list[$i]['funnel_url'],30);
	$row .= '		</table>';
	$row .= '	</td>';
    	$row .= '	<td style="width: 55%;">';
	$row .= '		<table class="funl">';
    $row .= Sct_Form::text("", 'goal_vars_u['.$i.'][redirect_link]', @$funnel_link_list[$i]['red_url'], 30);
	$row .= '		</table>';
	$row .= '	</td>';
	$row .= '	<td style="width: 100px;">';
	$row .= '		<table style="width: 100px;" class="funl">';
	$row .= Sct_Form::text('', 'goal_vars_u['.$i.'][conv_value]', doubleval(@$funnel_link_list[$i]['conv_value']),30);
	$row .= '		</table>';
	$row .= '	</td>';
	
	$row .= '</tr>';
}
$min_d = $no_dw;

for ($i = 1; $i <= $min_d; $i++)
{
	$row .= '<tr class="r_d" id="rd_'.$i.'">';
	$row .= '	<td style="width: 55%;">';
	$row .= '		<table class="funl">';
	$row .= Sct_Form::text("D".$i, 'goal_vars_d['.$i.'][link_id]', @$funnel_link_listd[$i]['funnel_url'], 30);
	$row .= '		</table>';
	$row .= '	</td>';
      	$row .= '	<td style="width: 55%;" class="funl" >';
	$row .= '		<table>';
    $row .= Sct_Form::text("", 'goal_vars_d['.$i.'][redirect_link]', @$funnel_link_listd[$i]['red_url'], 30);
	$row .= '		</table>';
	$row .= '	</td>';
	$row .= '	<td style="width: 100px;">';
	$row .= '		<table style="width: 100px;" class="funl">';
	$row .= Sct_Form::text('', 'goal_vars_d['.$i.'][conv_value]', doubleval(@$funnel_link_listd[$i]['conv_value']),30);
	$row .= '		</table>';
	$row .= '	</td>';

	$row .= '</tr>';
}
for ($i = 1; $i <= $no_t; $i++)
{
	$row .= '<tr class="r_t" id="rt_'.$i.'">';
	$row .= '	<td style="width: 55%;">';
	$row .= '		<table class="funl">';
	$row .= Sct_Form::text("T".$i, 'goal_vars_t['.$i.'][link_id]', @$funnel_link_listt[$i]['funnel_url'], 30);
	$row .= '		</table>';
	$row .= '	</td>';
      	$row .= '	<td style="width: 55%;" class="funl" >';
	$row .= '		<table>';
    $row .= Sct_Form::text("", 'goal_vars_t['.$i.'][redirect_link]', @$funnel_link_listt[$i]['red_url'], 30);
	$row .= '		</table>';
	$row .= '	</td>';
	$row .= '	<td style="width: 100px;">';
	$row .= '		<table style="width: 100px;" class="funl">';
	$row .= Sct_Form::text('', 'goal_vars_t['.$i.'][conv_value]', doubleval(@$funnel_link_listt[$i]['conv_value']),30);
	$row .= '		</table>';
	$row .= '	</td>';

	$row .= '</tr>';
}
$row .= '		</table>';
$row .= '	</td>';
$row .= '</tr>';



_e($row);

Sct_Form::$return = null;

?>
</table>
	<button type="button" style="display: none;" class="button" onclick="sct_new_goal_link()" style="padding: 5px 10px; cursor: pointer; float: right;">Add Goal Link</button>

	<div style="clear: both;"></div>

	<br /><br />

	<div class="sct_button_bar">
		<input type="submit" class="sct_button" value="Save" id="save_button" />
		<?php if (isset(self::$form_vars['funnel_id']) && intval(self::$form_vars['funnel_id'])){ ?>
		<input type="button" class="sct_button" value="Delete" onclick="deleteRecord();" />
		<?php } ?>
		<input type="button" class="sct_button" value="Back to List" onclick="window.location.href='<?php _e($cancel_url) ?>'" />
	</div>
</form>

<table id="sct_goal_page_template_u" style="display: none;">
<tr class="goals_u" id="ru_xx" >
	<td style="width: 55%;">
		<table class="funl">
<?php
Sct_Form::text('no_of_upxxx', 'goal_vars_u[xxx][i_u]', '', 30);
?>
		</table>
	</td>
    	<td style="width: 55%;">
		<table class="funl">
<?php
Sct_Form::text('', 'goal_vars_u[xxx][rlu]', '', 30);
?>
		</table>
	</td>
	<td style="width: 100px;">
		<table style="width: 100px;" class="funl">
<?php
Sct_Form::text('', 'goal_vars_u[xxx][c_u]', '',30);
?>
		</table>
	</td>

</tr>
</table>

<table id="sct_goal_page_template_d" style="display: none;">
<tr class="goals_d" id="rd_xx">
	<td style="width: 55%;">
		<table class="funl">
<?php
Sct_Form::text('no_of_dwxxx', 'goal_vars_d[xxx][id_d]', '', 30); // goal_vars_d[xxx][id_d]
?>
		</table>
	</td>
    	<td style="width: 55%;">
		<table class="funl">
<?php
Sct_Form::text('', 'goal_vars_d[xxx][rld]', '', 30);
?>
		</table>
	</td>
	<td style="width: 100px;">
		<table style="width: 100px;" class="funl">
<?php
Sct_Form::text('', 'goal_vars_d[xxx][c_d]', '',30);
?>
		</table>
	</td>

</tr>
</table>

<table id="sct_goal_page_template_t" style="display: none;">
<tr class="goals_t" id="rt_xx">
	<td style="width: 55%;">
		<table class="funl">
<?php
Sct_Form::text('no_of_dwxxx', 'goal_vars_t[xxx][id_d]', '', 30); 
?>
		</table>
	</td>
    	<td style="width: 55%;">
		<table class="funl">
<?php
Sct_Form::text('', 'goal_vars_t[xxx][rld]', '', 30);
?>
		</table>
	</td>
	<td style="width: 100px;">
		<table style="width: 100px;" class="funl">
<?php
Sct_Form::text('', 'goal_vars_t[xxx][c_d]', '',30);
?>
		</table>
	</td>

</tr>
</table>
<?php

if ((int)@self::$form_vars['funnel_id'])
{
    
	_e('<a name="split_results"></a><h3 style="background-color: #EEE; padding: 5px 10px;">Results</h3>');

?>
    <input type="button" class="button-primary" onclick="treeView()" value="View Funnel Tree" style="float: right; margin-right: 1%;"  />
    <div style="clear: both;"></div>

        <script src="<?php _e(SCT_TREE_DIR); ?>/src/easyTree.js"></script>
        <style>
        .parent_li ul{
            margin: 3px 0 0 35px !important;
        }
        .easy-tree li::before, .easy-tree li::after{
            left: 0px !important;
        }
        .easy-tree li::after{
            border-top: none !important;
        }
        .easy-tree li{
            padding: 16px 0 0 !important;
        }
        </style>
        <div  class="easy-tree" style="padding-left: 5%; display: none">
        <?php
        $get_funnel = $wpdb->get_row("select * from ".self::$table['funnel']." where funnel_id='".(int)@self::$form_vars['funnel_id']."'",ARRAY_A);
         $sql= "select * from ".Sct_Base::$table['funnel_link_new']."  where  funnel_id = ".(int)@self::$form_vars['funnel_id']." and funnel_type NOT IN(3,1) order by sort_order";
	$getfunnel = $wpdb->get_results($sql, ARRAY_A);

    _e("<ul>");
    $upsel_order_id  = "";
        foreach($getfunnel as $k=>$v){
             if($v['funnel_type']==1){
                    $d = "Downsell";
                }else if($v['funnel_type']==2){
                    $d = "Landing Page";
                
                }else if($v['funnel_type']==0){
                    $d = "Upsell";
                }else{
                    $d = "Thanks";
                }
                if($v['funnel_type']==2){
                    $t = "";
                }else{
                    $t =  $v['link_order'];
                }
             if($v['funnel_type']==2){
                 _e("<li>".$d." $t -".$get_funnel['name']."</b> (".$v['funnel_url'].") <ul>");
             }
             if($v['funnel_type']==0){
                    _e("<li>".$d." $t -".$get_funnel['name']."</b> (".$v['funnel_url'].")");
                if($v['funnel_type']!=2){
                    $get_down = $wpdb->get_row("select * from ".Sct_Base::$table['funnel_link_new']." where  funnel_id = ".(int)@self::$form_vars['funnel_id']." and funnel_type='1' and link_order='".$v['link_order']."'  order by sort_order ",ARRAY_A);
                    if(count($get_down)){
                         if($get_down['funnel_type']==1){
                                $dd = "Downsell";
                            }else if($get_down['funnel_type']==2){
                                $dd = "Landing Page";
                            
                            }else if($get_down['funnel_type']==0){
                                $dd = "Upsell";
                            }else{
                                $dd = "Thanks";
                            }
                            if($get_down['funnel_type']==2){
                                $tt = "";
                            }else{
                                $tt =  $get_down['link_order'];
                            }
                            _e("<ul><li>".$dd." $tt -".$get_funnel['name']."</b> (".$get_down['funnel_url'].")");
                    }
                    /********/
                    $get_down = $wpdb->get_row("select * from ".Sct_Base::$table['funnel_link_new']." where  funnel_id = ".(int)@self::$form_vars['funnel_id']." and funnel_type='3' and link_order='".$v['link_order']."'  order by sort_order ",ARRAY_A);
                    if(count($get_down)){
                         if($get_down['funnel_type']==1){
                                $dd = "Downsell";
                            }else if($get_down['funnel_type']==2){
                                $dd = "Landing Page";
                            
                            }else if($get_down['funnel_type']==0){
                                $dd = "Upsell";
                            }else{
                                $dd = "Thanks";
                            }
                            if($get_down['funnel_type']==2){
                                $tt = "";
                            }else{
                                $tt =  $get_down['link_order'];
                            }
                            _e("<ul><li>".$dd." $tt -".$get_funnel['name']."</b> (".$get_down['funnel_url'].")</li></ul>");
                            $upsel_order_id = $get_down['link_order'];
                    }
                    _e("</li></ul>");
                }
                    ?>
                          
                    <?php
                    
               
                _e("</li>");
            }   
        }
        $upsel_order_id =$upsel_order_id+1;
    $get_down = $wpdb->get_row("select * from ".Sct_Base::$table['funnel_link_new']." where  funnel_id = ".(int)@self::$form_vars['funnel_id']." and funnel_type='3' and link_order='$upsel_order_id'  order by sort_order ",ARRAY_A);
            if(count($get_down)){
                 if($get_down['funnel_type']==1){
                        $dd = "Downsell";
                    }else if($get_down['funnel_type']==2){
                        $dd = "Landing Page";
                    
                    }else if($get_down['funnel_type']==0){
                        $dd = "Upsell";
                    }else{
                        $dd = "Thanks";
                    }
                    if($get_down['funnel_type']==2){
                        $tt = "";
                    }else{
                        $tt =  $get_down['link_order'];
                    }
                 _e("<li>".$dd." $tt -".$get_funnel['name']."</b> (".$get_down['funnel_url']."</li>");
            }
    _e("</li></ul>");
        ?>
        </div>
        <script>
            (function ($) {
                function init() {
                    $('.easy-tree').EasyTree({
                        addable: false,
                        editable: false,
                        deletable: false
                    });
                }
        
                window.onload = init();
            })(jQuery)
        </script>

<table class="sct_form_table" cellpadding="0" cellspacing="5" width="100%" id="dest_url_wrapper" style="margin: 0;">
<tr>
	<th align="left" style="text-align: left; vertical-align: bottom; width:35%;">Goal Page</th>
    <th align="left" style="text-align: left; vertical-align: bottom; width: 13%;">Conversion Value</th>
	<th align="center" style="text-align: center; vertical-align: bottom; width:10%;">Clicks</th>
    <th align="left" style="text-align: left; vertical-align: bottom; width: 10%;">Conversions</th>
    <th align="center" style="text-align: center; vertical-align: bottom; width:5%;">Ratio</th>
    <!--<th align="center" style="text-align: center; vertical-align: bottom; width:10%;">RPC</th>-->
	<!--<th align="center" style="text-align: center; vertical-align: bottom;">%</th>-->
	<!--<th align="center" style="text-align: right; vertical-align: right;">ROI</th>-->
    <th align="center" style="text-align: right; vertical-align: right; width: 13%;">Total Revenue</th>
</tr>
<?php

	$sql = '
	SELECT
		SUM(v.visits)
	FROM
		'.Sct_Base::$table['link'].' l
	JOIN
		'.Sct_Base::$table['funnel_link'].' fl ON l.link_id = fl.link_id
	LEFT JOIN
		(
			SELECT
				link_id,
				COUNT(*) as visits
			FROM
				'.Sct_Base::$table['click'].'
			WHERE
				link_id IN (SELECT link_id FROM '.Sct_Base::$table['funnel_link'].' WHERE funnel_id = '.(int)@self::$form_vars['funnel_id'].')
			GROUP BY
				link_id
		) AS v ON l.link_id = v.link_id
	LEFT JOIN
		'.Sct_Base::$table['click'].' c ON l.link_id = c.source_link_id
	WHERE
		fl.funnel_id = '.(int)@self::$form_vars['funnel_id'].'
	';

	$total_clicks = $wpdb->get_var($sql);
    
	$sqls = '
	SELECT
		l.*,
		fl.*,
		v.visits
	FROM
		'.Sct_Base::$table['link'].' l
	JOIN
		'.Sct_Base::$table['funnel_link'].' fl ON l.link_id = fl.link_id
	LEFT JOIN
		(
			SELECT
				link_id,
				COUNT(*) as visits
			FROM
				'.Sct_Base::$table['click'].'
			WHERE
				link_id IN (SELECT link_id FROM '.Sct_Base::$table['funnel_link'].' WHERE funnel_id = '.(int)@self::$form_vars['funnel_id'].')
			GROUP BY
				link_id
		) AS v ON l.link_id = v.link_id
	LEFT JOIN
		'.Sct_Base::$table['click'].' c ON l.link_id = c.source_link_id
	WHERE
		fl.funnel_id = '.(int)@self::$form_vars['funnel_id'].'
	GROUP BY
		l.link_id
	ORDER BY
		fl.sort_order
	';
    
    $sql_new = "select * from ".Sct_Base::$table['funnel_link_new']."  where  funnel_id = ".(int)@self::$form_vars['funnel_id']." order by sort_order";
    
    
	$goal_link_list = $wpdb->get_results($sql_new, ARRAY_A);
    $total_clickss = 0;
    $total_revenue = 0;
	foreach ($goal_link_list as $goal_link)
	{
	
       $total_clicks = $total_clicks+(int)$goal_link['unique_click'];
       
       if($goal_link['funnel_type']==2){
            $gets = $wpdb->get_row("select * from ".Sct_Base::$table['funnel_link_new']." where funnel_type='0' and link_order='1' and funnel_id='".(int)@self::$form_vars['funnel_id']."'",ARRAY_A);
            
            $conversion = $gets['conversions'];
       }else if($goal_link['funnel_type']==0){
            $goal_l = $goal_link['link_order']+1;
            $gets = $wpdb->get_row("select * from ".Sct_Base::$table['funnel_link_new']." where funnel_type='0' and link_order='$goal_l' and funnel_id='".(int)@self::$form_vars['funnel_id']."'",ARRAY_A);
            if(count($gets)>0){
                    
                    $conversion = $gets['conversions'];                        
            }else{
                
                $goal_l = $goal_link['link_order'];   
                $gets = $wpdb->get_row("select * from ".Sct_Base::$table['funnel_link_new']." where funnel_type='3' and link_order='$goal_l' and funnel_id='".(int)@self::$form_vars['funnel_id']."'",ARRAY_A);
                $conversion = $gets['conversions'];    
            } 
       }else if($goal_link['funnel_type']==3){
            //$goal_l = $goal_link['link_order']+1;
            $goal_l = $goal_link['link_order'];   
            $gets = $wpdb->get_row("select * from ".Sct_Base::$table['funnel_link_new']." where funnel_type='1' and link_order='$goal_l' and funnel_id='".(int)@self::$form_vars['funnel_id']."'",ARRAY_A);
            if(count($gets)>0){
                $conversion = $gets['unique_click'];
            }else{
                $goal_l = $goal_link['link_order'];  
                $gets = $wpdb->get_row("select * from ".Sct_Base::$table['funnel_link_new']." where funnel_type='0' and link_order='$goal_l' and funnel_id='".(int)@self::$form_vars['funnel_id']."'",ARRAY_A);
                $conversion = $gets['unique_click'];
            }
            //$conversion = 0;
       }else{
        $conversion = 0;
       }
?>
<tr style="border-top: 1px solid #DDD;">
	<td><?php
        if($goal_link['funnel_type']==1){
            $d = "Downsell";
        }else if($goal_link['funnel_type']==2){
            $d = "Landing Page";
        
        }else if($goal_link['funnel_type']==0){
            $d = "Upsell";
        }else{
            $d = "Thanks";
        }
        if($goal_link['funnel_type']==2){
            $t = "";
        }else{
            $t =  $goal_link['link_order'];
        }
         _e("<b>".$d." $t -".$get_funnel['name']."</b> (".$goal_link['funnel_url'].")");
     ?></td>
    <td align="center" style="text-align: center;"><?php
    
     _e("$".(int)$goal_link['conv_value']); ?></td>
	<td align="center" style="text-align: center;">
    <?php
     _e((int)$goal_link['unique_click']);
     
      ?></td>
    <td align="center" style="text-align: center;"><?php
    if($goal_link['funnel_type']!=1){
        if($goal_link['funnel_type']!=3){
        _e($conversion);
        }else{
            _e("n/a");    
        }
     }else{
        _e("n/a");
     }
      ?></td>
	<td align="center" style="text-align: center;">
    <?php 
    if($goal_link['funnel_type']!=1){
        
        if($goal_link['funnel_type']!=3){
            
            _e(@number_format($conversion / @(int)$goal_link['unique_click'] * 100, 0).'%');
        }else{
            
            
            _e(@number_format(@(int)$goal_link['unique_click']/$conversion * 100, 0).'%');
        }
     }else{
        _e("n/a");
    }
     ?>
     </td>
    <!--<td align="center" style="text-align: center;"><?php 
    if( $goal_link['funnel_type']!=1){
        if((int)$goal_link['unique_click']!=0){
            $foo =  ((int)$goal_link['conv_value']*$conversion)/@(int)$goal_link['unique_click'];
        }else{
            $foo = 0;
        } 
        _e(@number_format((float)$foo, 2, '.', '').'$');
    }else{
        _e("n/a");
    }
    ?></td>-->
    <td align="right" style="text-align: right;">
        <?php
        if($goal_link['funnel_type']!=1){
            $tr = (int)$goal_link['conv_value']*$conversion;
            _e("$".$tr);
        }else{
            _e("n/a");
            $tr = 0;
        }
        ?>
    </td>
</tr>
<?php
$total_revenue = @$total_revenue+@$tr;
	}
?>
<tr>
    <td colspan="2">
        <b>Total Funnel Value</b>
    </td>
    <td style="text-align: center;">
        <b><?php _e($total_clicks); ?></b>
    </td>
    <td colspan="3" style="text-align: right;">
    <b><?php
    _e("$".@number_format((float)$total_revenue, 2, '.', ''));
     ?></b>
    </td>
</tr>
<tr>
    <td colspan="7"> &nbsp;</td>
</tr>
<tr>

<td colspan="5" style="text-align: right;">
    <b>Funnel ROI</b>
</td>
<td style="text-align: right;">
<b>
<?php
if(@self::$form_vars['f_cost']!="0" && @self::$form_vars['f_cost']!=""){

$roi = ($total_revenue/@self::$form_vars['f_cost'])*100;
}else{
    $roi = 0;
}
_e(@number_format((float)$roi, 2, '.', '')."%");
?></b>
</td>
</tr>
</table>
<?php
}    

?>

<script type="text/javascript">

function funnel_type(v){
    if(v==1){
        //hide conversion value
        jQuery('#goal_vars_landing_1_conv_value').hide();
    }else{
        //show con value
        jQuery('#goal_vars_landing_1_conv_value').show();
    }
}
var goal_page_count = 0;

function sct_new_goal_link(){
	var html = jQuery('#sct_goal_page_template').html();

console.log(jQuery('#sct_goal_page_grid').find('select').length);

	if (!goal_page_count)
	{
		var goal_page_count = jQuery('#sct_goal_page_grid').find('.sct_form_autocomplete_field').length + 1;
	}
	else
	{
		goal_page_count++;
	}

	html = html.replace(/xxx/g, goal_page_count.toString());

	jQuery('#sct_goal_page_grid').append(html);
}
function treeView(){
    jQuery('.easy-tree').slideToggle("slow");
}
jQuery(document).ready(function(){
    
    funnel_type(<?php _e(@self::$form_vars['funnel_type']); ?>);
    jQuery("#datepicker" ).datepicker({
        dateFormat : 'yy-mm-dd'
    });
    jQuery("#datepicker1" ).datepicker({
        dateFormat : 'yy-mm-dd'
    });
    jQuery('#form_vars_no_of_up').blur(function(){
        var v = jQuery(this).val();
        if(v>0 && v!=''){
        var up_sells = jQuery('#up_sells').val(); // Old value
        if(v>up_sells){
            var cc = Number(up_sells)+1  
            for(i=cc; i<=v; i++ ){
                var html = jQuery('#sct_goal_page_template_u').html();    
            	html = html.replace(/xxx/g, i.toString()); 
                html = html.replace(/no_of_up/g, "U");     
                html = html.replace(/i_u/g, "link_id");
                html = html.replace(/rlu/g, "redirect_link");
                    html = html.replace(/c_u/g, "conv_value");
                    html = html.replace(/cs_u/g, "click_cost");
                    html = html.replace(/goals_u/g,"r_u");
                    html = html.replace(/ru_xx/g,"ru_"+i.toString());
                    
            	jQuery('#sct_goal_page_grid').append(html);
            }
        }else if(v<up_sells){
            for(i=up_sells ; i>v; i--){
                console.log(i);
                jQuery('#ru_'+i).remove();
            }
        }
        jQuery('#up_sells').val(v);
    }
    });
    
    jQuery('#form_vars_no_of_dw').blur(function(){
        
          var v = jQuery(this).val();
          
        if(v>0 && v!=''){
            //jQuery('.r_d').remove();
        var down_sells = jQuery('#down_sells').val(); // Old value
        if(v>down_sells){
            var cv = Number(down_sells)+1;
            for(i=cv; i<=v; i++ ){
                var html = jQuery('#sct_goal_page_template_d').html();    
            	html = html.replace(/xxx/g, i.toString()); 
                
                html = html.replace(/no_of_dw/g, "D");
                html = html.replace(/id_d/g, "link_id");
                html = html.replace(/rld/g, "redirect_link");
                html = html.replace(/c_d/g, "conv_value");
                html = html.replace(/cs_d/g, "click_cost");     
                  html = html.replace(/goals_d/g,"r_d");
                  html = html.replace(/rd_xx/g,"rd_"+i.toString());
            	jQuery('#sct_goal_page_grid').append(html);
            }
        }else if(v<down_sells){
            for(i=down_sells ; i>v; i--){
                jQuery('#rd_'+i).remove();
            }
        }
        jQuery('#down_sells').val(v);
        }
    });
    jQuery('#form_vars_no_of_t').blur(function(){
          var v = jQuery(this).val();
        if(v>0 && v!=''){
            //jQuery('.r_d').remove();
        var down_sells = jQuery('#t_sells').val(); // Old value
        if(v>down_sells){
            var cv = Number(down_sells)+1;
            for(i=cv; i<=v; i++ ){
                var html = jQuery('#sct_goal_page_template_t').html();    
            	html = html.replace(/xxx/g, i.toString()); 
                html = html.replace(/no_of_dw/g, "T");
                html = html.replace(/id_d/g, "link_id");
                html = html.replace(/rld/g, "redirect_link");
                html = html.replace(/c_d/g, "conv_value");
                html = html.replace(/cs_d/g, "click_cost");     
                  html = html.replace(/goals_d/g,"r_d");
                  html = html.replace(/rt_xx/g,"rt_"+i.toString());
            	jQuery('#sct_goal_page_grid').append(html);
            }
        }else if(v<down_sells){
            for(i=down_sells ; i>v; i--){
                jQuery('#rt_'+i).remove();
            }
        }
        jQuery('#t_sells').val(v);
        }
    });
});

</script>