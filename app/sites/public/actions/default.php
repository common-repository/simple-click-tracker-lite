<?php
$sql_bydomain = Sct_Base::get_user_join_ids();
if(is_array($sql_bydomain) && @count($sql_bydomain)>0){
    $user_type = $sql_bydomain['user_type'];
}else{
    $user_type = 2;
}
$user_data_login = get_userdata( Sct_Base::getActorUserId());
$user_role = $user_data_login->roles[0];
//4.4
if($user_role==""){
    $user_role = implode(', ', $user_data_login->roles);
    $exx = explode(',',$user_role);
    $user_role = @$exx[0];
}
if (!is_admin() && !Sct_Base::getActorUserId())
{
	self::$action = 'unknown_user';
}
if($user_type==2 && $user_role!='administrator'){
    self::$action = 'unknown_user';
}