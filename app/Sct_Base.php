<?php       
class Sct_Base
{
	public static $name			= 'simple_click_tracker';
	public static $action		= NULL;
	public static $form_vars	= array();
	public static $errors		= array();
	public static $table		= array();
	public static $context		= NULL;
	public static $plugin_url	= NULL;
	public static $is_full_access	= 0;
	public static $crypt_key	= '34n9012223db2ec5y3655ak98c7fe2f1';
	public static function initBase()
	{
		if (!self::$plugin_url)
		{
			global $wpdb;
			self::$table = array(
			'click'			=> $wpdb->base_prefix.'sct_click',
			'404_log'		=> $wpdb->base_prefix.'sct_404_log',
			'funnel'		=> $wpdb->base_prefix.'sct_funnel',
			'funnel_link'	=> $wpdb->base_prefix.'sct_funnel_link',
			'group'			=> $wpdb->base_prefix.'sct_group',
			'domain'		=> $wpdb->base_prefix.'sct_domain',
			'link'			=> $wpdb->base_prefix.'sct_link',
			'user_join'		=> $wpdb->base_prefix.'sct_user_join',
            'funnel_link_new' => $wpdb->base_prefix."sct_funnel_link_new"
			);
			self::$plugin_url = SCT_BASE_URL;
			if ($_SERVER['HTTP_HOST'] == 'mynams.com')// || $_SERVER['HTTP_HOST'] == 'gozer.smclz.com')
			{
				Sct_Base::$is_full_access = 1;
			}
			else
			{
				//Sct_Base::$is_full_access = get_option('rlm_full_access_'.self::$name, 0);
                Sct_Base::$is_full_access = 1;
			}
		}
	}
    
    public static function getallheaders_custom()
    {
        if (!is_array($_SERVER)) {
            return array();
        }
        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
    
    public static function sct_getServerURL(){
    	$serverName = $_SERVER['SERVER_NAME'];
    	$filePath = $_SERVER['REQUEST_URI'];
    	if(strrpos($filePath,'/?')!==false)
    		$withInstall = substr($filePath,0,strrpos($filePath,'/'));
    	else
    		$withInstall = $filePath;
    	$withInstall1 = substr($withInstall,0,strrpos($withInstall,'/'));
    	$serverPath = $_SERVER['SERVER_NAME'].$withInstall1;
    	if(strpos($serverPath,'http')===false){
    		$serverPath = 'http://'.$serverPath;
    	}
    	return $serverPath;
    }
    public static function parseURL_domain($show_more_link){
        $input = trim($show_more_link, '/');
        if (!preg_match('#^http(s)?://#', $input)) {
        $input = 'http://' . $input;
        }
        $urlParts = parse_url($input);
        $domain = preg_replace('/^www\./', '', $urlParts['host']);
        return $domain;
    }
    
    
	public static function handleRedirect($domain, $pt_all, $qury_string,$do_404 = true)
	{
        $ref = @$_SERVER['HTTP_REFERER'];
        ob_start();
        
        if (!function_exists('getallheaders')) {
            $my_header = Sct_Base::getallheaders_custom();
        }else{
            $my_header = getallheaders();
        }
        if(!isset($my_header['User-Agent'])){
            if(isset($my_header['user-agent'])){
                $my_header['User-Agent'] = $my_header['user-agent'];
            }
        }
        $search_engiones = array('www.google.com','www.google.com.pk','www.google.as','www.google.co.uk','www.google.com.au','www.google.co.ind','www.google.ae',
        'www.google.ca','www.google.com.af','www.google.com.ec','www.google.li','www.google.co.nz','www.google.com.sa','www.google.com.tw','www.google.vu','www.google.com.uy',
        'www.google.nl','www.google.no','www.google.com.ni','www.google.ac','www.google.am','www.google.com.bd','www.google.com.br','www.google.cl','www.google.com.ua',
        'www.google.ru','www.google.com.sg','www.google.sh','www.google.com.mx',
        'www.bing.com','bing.com',
        'search.yahoo.com','www.search.yahoo.com','www.yahoo.com','yahoo.com',
        'www.baidu.com','baidu.com');
        $ref_url = parse_url($ref);
        $path_search = @$ref_url['host'];
       $q_path = parse_url($pt_all);
       $path = @$q_path['path'];
       $qry_string = @$qury_string;
	   global $wpdb;
       $http_ref = rtrim($_SERVER['HTTP_REFERER'],'/');
       $red_rect_link = Sct_Base::sct_getServerURL();
       $site_link = Sct_Base::sct_getServerURL();
       $primary_domain = $domain;
       $funnel_url = rtrim($red_rect_link,'/');
       $path = rtrim($path,'/');
       $post_slung = explode('/',$path);
       if(@count($post_slung)>1){
            $post_slung_link = $post_slung[@count($post_slung)-1]; 
       }else{
            $post_slung_link = $post_slung[0];
       }
        $tabl = $wpdb->prefix."posts";
        $tab2 = $wpdb->prefix."postmeta";
        $sqls = ' SELECT
        p1.*,
        wm2.meta_value
    FROM 
        '.$tabl.' p1
    LEFT JOIN 
        '.$tab2.' wm1
        ON (
            wm1.post_id = p1.id 
            AND wm1.meta_value IS NOT NULL
            AND wm1.meta_key = "_thumbnail_id"              
        )
    LEFT JOIN 
        '.$tab2.' wm2
        ON (
            wm1.meta_value = wm2.post_id
            AND wm2.meta_key = "_wp_attached_file"
            AND wm2.meta_value IS NOT NULL  
        )
    WHERE
        p1.post_status="publish" 
        AND p1.post_type IN ("post","page") AND p1.post_name="'.trim($post_slung_link).'"
    ORDER BY 
        p1.post_date DESC';
	    $get_post = $wpdb->get_results($sqls,ARRAY_A);
        $get_post = $get_post[0]; // 0 index copied
        $feat_image = wp_get_attachment_url( get_post_thumbnail_id(@$get_post['ID']) );
        if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')
            $scheme = 'https://';
        else
            $scheme = 'http://';
        $rurl = $scheme.$domain."/".trim($path, '/');
       //$check = $wpdb->get_row("select * from ".self::$table['funnel_link_new']." where funnel_url='$funnel_url' and red_url='$http_ref'",ARRAY_A);
       if($http_ref==""){
        $check = $wpdb->get_results("select * from ".self::$table['funnel_link_new']." where funnel_url='$http_ref' or red_url='$rurl'",ARRAY_A);
       }else{
        $check = $wpdb->get_results("select * from ".self::$table['funnel_link_new']." where red_url='$rurl' ",ARRAY_A);
       }
       $check = $check[0]; // 0 index 
       if(@count($check)>0){
            if($check['funnel_type']==2){
                $wpdb->query("update ".self::$table['funnel_link_new']." set `unique_click` = `unique_click` + 1 , `conversions` = `conversions` + 1 where funnel_link_id='$check[funnel_link_id]'");
            }else if($check['funnel_type']==0){
                $wpdb->query("update ".self::$table['funnel_link_new']." set `unique_click` = `unique_click` + 1, `conversions` = `conversions` + 1 where funnel_link_id='$check[funnel_link_id]'");
            }else if($check['funnel_type']==1){
                $wpdb->query("update ".self::$table['funnel_link_new']." set `unique_click` = `unique_click` + 1 where funnel_link_id='$check[funnel_link_id]'");
            }else if($check['funnel_type']==3){
                $wpdb->query("update ".self::$table['funnel_link_new']." set `unique_click` = `unique_click` + 1, `conversions` = `conversions` + 1 where funnel_link_id='$check[funnel_link_id]'");
            }
       }
		$domain = $wpdb->get_results('SELECT * FROM '.Sct_Base::$table['domain'].' WHERE domain = "'.addslashes($domain).'"', ARRAY_A);
        $domain = $domain[0]; // 0 index of array
		if ((int)@$domain['domain_id'])
		{
			$sql = '
			SELECT
				*,
				IF(last_check < DATE_SUB(NOW(), INTERVAL 1 DAY), 1, 0) AS do_check
			FROM
				'.Sct_Base::$table['link'].'
			WHERE
				(path = "'.addslashes($path).'" OR path = "'.addslashes(trim($path, '/')).'")
			AND
				domain_id = '.(int)$domain['domain_id'];
			$link = $wpdb->get_results($sql, ARRAY_A);
            $link = $link[0];
			if ((int)@$link['link_id'])
			{
			     if($link['query_string']==0 && $qry_string!=""){
                        $q_run = "?".$qry_string;
                   }else{
                         $q_run = "";
                   }
			     $linkData = $link;
				$d_link = $link;
				if ((int)@$link['has_children'])
				{
                    $json  = @array_reverse(json_decode($linkData['merged_data'],true));
                    if(is_array($json)){
                        $one = $json[0];
                        unset($json[0]);
                        $json = @array_reverse($json);
                        $json = array_merge(array($one),$json);
                      if(get_option("split_test_counter_".$linkData['link_id'])==""){
                           $counter = 0;
                       }else{
                           $counter = get_option("split_test_counter_".$linkData['link_id']);
                       }
                       $counter = $counter;
                        $array = $json;
                       if(get_option("split_children_count_".$linkData['link_id'])==""){
                             update_option("split_children_count_".$linkData['link_id'],count($array));
                       }else{
                            if(count($array)!=get_option("split_children_count_".$linkData['link_id'])){
                                update_option("split_children_count_".$linkData['link_id'],count($array));
                                update_option("split_number_counter_".$linkData['link_id'],0);
                            }
                       }
                        if(get_option("split_number_counter_".$linkData['link_id'])==""){
                           $number = 0;
                       }else{
                           $number = get_option("split_number_counter_".$linkData['link_id']);
                       }
                        $number = $number;
                        $getc = $array[$number];
                        if($getc!=""){
                        $gets = explode('-',$getc);
                        $get_count = @$gets[0];
                        $get_link_id = @$gets[1];
                        if($get_link_id!=""){
                                $d_link = $wpdb->get_row('SELECT link_id, url,link_red, IF(last_check < DATE_SUB(NOW(), INTERVAL 1 DAY), 1, 0) AS do_check  FROM '.Sct_Base::$table['link'].' WHERE is_archived="0" and link_id = '.$get_link_id.' LIMIT 1', ARRAY_A);
                                if($d_link['link_red']==0){
                                    $d_link = $d_link;
                                    $wpdb->query("update ".Sct_Base::$table['link']." set link_red=link_red+1 where link_id='$get_link_id'");
                                    update_option("splitTest_".$linkData['link_id']."_".$get_link_id,0);
                                    $number++;                
                                        update_option("split_number_counter_".$linkData['link_id'],$number);
                                    if($number>count($array)-1){
                                        update_option("split_number_counter_".$linkData['link_id'],0);
                                    }
                                }else{
                                    $d_link = Sct_Base::check_Per_Ratio($d_link,$get_link_id,$get_count,$number,$linkData,$array);
                                }
                            }else{
                                $d_link = $wpdb->get_row('SELECT link_id, url, IF(last_check < DATE_SUB(NOW(), INTERVAL 1 DAY), 1, 0) AS do_check FROM '.Sct_Base::$table['link'].' WHERE is_archived="0" and link_id = '.(int)@$link['link_id'].' OR parent_id = '.(int)@$link['link_id'].' ORDER BY RAND() LIMIT 1', ARRAY_A);       
                            }
                       }else{
                         $d_link = $wpdb->get_row('SELECT link_id, url, IF(last_check < DATE_SUB(NOW(), INTERVAL 1 DAY), 1, 0) AS do_check FROM '.Sct_Base::$table['link'].' WHERE is_archived="0" and link_id = '.(int)@$link['link_id'].' OR parent_id = '.(int)@$link['link_id'].' ORDER BY RAND() LIMIT 1', ARRAY_A);
                       }
                     }else{
                        $d_link = $wpdb->get_row('SELECT link_id, url, IF(last_check < DATE_SUB(NOW(), INTERVAL 1 DAY), 1, 0) AS do_check FROM '.Sct_Base::$table['link'].' WHERE is_archived="0" and link_id = '.(int)@$link['link_id'].' OR parent_id = '.(int)@$link['link_id'].' ORDER BY RAND() LIMIT 1', ARRAY_A);
                     }
				}
				$source_link_id = 0;
				$src_link_list = $wpdb->get_results('SELECT link_id FROM '.Sct_Base::$table['link'].' WHERE goal_link_id = '.(int)$link['link_id'], ARRAY_A);
				if ($src_link_list)
				{
					foreach ($src_link_list as $src_link)
					{
						if (isset($_COOKIE['sct_link_id_'.$src_link['link_id']]) && (int)$_COOKIE['sct_link_id_'.$src_link['link_id']])
						{
							$source_link_id = (int)$_COOKIE['sct_link_id_'.$src_link['link_id']];
						}
					}
				}
				@setcookie('sct_link_id_'.$link['link_id'], $d_link['link_id'], time() + (60*60*24*30), '/');
                
                /******/
                /**JAVASCRIPT CODE REMOVED***/
                /*******/
                
                   
                   if(isset($my_header['User-Agent'])){
                        if(strpos($my_header['User-Agent'],"facebook")===false){
                           header('HTTP/1.1 301 Moved Permanently');
                           header("Location: ".$d_link['url'].$q_run);
                           
                        }
                   }
				//}
				flush(); ob_flush(); ob_end_flush();
                if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    $clientIpAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
                } else {
                    $clientIpAddress = $_SERVER['REMOTE_ADDR'];
                }
				$click = array(
				'link_id'			=> $d_link['link_id'],
				'parent_id'			=> $link['link_id'],
				'source_link_id'	=> $source_link_id,
				'user_id'			=> $link['user_id'],
				//'ip'				=> $_SERVER['REMOTE_ADDR'],
                'ip'				=> $clientIpAddress,
				'referrer'			=> sanitize_text_field($_REQUEST['referer']),
				'agent'				=> $_SERVER['HTTP_USER_AGENT']
				);
				if ((int)$d_link['do_check'])
				{
                    //$wpdb->query('UPDATE '.Sct_Base::$table['link'].' SET `is_dead` = '.Sct_Base::is_404($d_link['url']).', `last_check` = NOW() WHERE `link_id` = '.(int)$d_link['link_id']);
				}
                if(!in_array($path_search,$search_engiones)){
                    $wpdb->insert(Sct_Base::$table['click'], $click);
                $wpdb->query('UPDATE '.Sct_Base::$table['link'].' SET `is_dead` = '.Sct_Base::is_404($d_link['url']).', `last_check` = NOW() WHERE `link_id` = '.(int)$d_link['link_id']);
				$wpdb->query('UPDATE '.Sct_Base::$table['link'].' SET `total_clicks` = `total_clicks` + 1, `last_click` = NOW(), `unique_clicks` = (SELECT COUNT(DISTINCT `ip`) FROM '.Sct_Base::$table['click'].' WHERE (`link_id` = '.(int)$link['link_id'].' OR `parent_id` = '.(int)$link['link_id'].')) WHERE `link_id` = '.(int)$link['link_id']);
				$wpdb->query('UPDATE '.Sct_Base::$table['link'].' SET `first_click` = NOW() WHERE `link_id` = '.(int)$link['link_id'].' AND `first_click` = "0000-00-00 00:00:00"');
                }
				exit();
                die();
			}
            /*if(count($get_post)>0){
            }else{*/
                $dom = Sct_Base::parseURL_domain(site_url());
                if($primary_domain!=$dom){
                    //if($site_link!=site_url()){
                        if ($path !== '/favicon.ico')
            			{
            				$wpdb->query('REPLACE INTO '.Sct_Base::$table['404_log'].' (`domain_id`, `path`) VALUES ('.$domain['domain_id'].', "'.addslashes($path).'")');
            			}
                    //}
                }
            //}
			if ($do_404 && $domain['redirect_to_404'])
			{
                _e("<script data-cfasync='false' type='text/javascript'>window.location='".$domain['redirect_to_404']."';</script>");
				exit();
			}
		}
	}
    function check_Per_Ratio($d_link,$get_link_id,$get_count,$number,$linkData,$array){
        global $wpdb;
        $n = 0;
        $k = 0;
        for($i=0; $i<=count($array); $i++){
            if($i==0){
                $get_link_id = $get_link_id;
            }else{
                $getc = $array[$n];
                $gets = explode('-',$getc);
                $get_count = $gets[0];
                $get_link_id = $gets[1];
            }
            $sum_total = $wpdb->get_row("select count(*) as total from  ".Sct_Base::$table['click']." where  parent_id='".$linkData['link_id']."'",ARRAY_A);
            $sum_link = $wpdb->get_row("select count(*) as link_total from  ".Sct_Base::$table['click']." where link_id='$get_link_id' and parent_id='".$linkData['link_id']."'",ARRAY_A);
            $ratio = ($sum_link['link_total']/$sum_total['total'])*100;
            $ratio = number_format($ratio, 0);
            if($ratio<=$get_count){
                $d_link = $wpdb->get_row('SELECT link_id, url, IF(last_check < DATE_SUB(NOW(), INTERVAL 1 DAY), 1, 0) AS do_check FROM '.Sct_Base::$table['link'].' WHERE is_archived="0" and link_id = '.(int)@$get_link_id.' LIMIT 1', ARRAY_A);
                $number++;                
                update_option("split_number_counter_".$linkData['link_id'],$number);
                if($number>count($array)-1){
                    update_option("split_number_counter_".$linkData['link_id'],0);
                }
                return $d_link;
            }
                if($n==0 && $k==0){
                $n = $number;
                $k= 1;
                }else{
                $n++;
                }
                if($n>count($array)-1){
                    $n=0;
                }   
        }
    }
    
    function sct_DBins($string){
    	$a = html_entity_decode($string);
    	return trim(htmlspecialchars($a,ENT_QUOTES));
    }
    
	public static function getUserId()
	{
		if (is_admin())
		{
			//return 0;
            return (int)get_current_user_id();
		}
		return (int)get_current_user_id();
	}
    public static function get_IdByEmail($email){
        global $wpdb;
        $user_db = $wpdb->prefix."users";
        $user_info = $wpdb->get_row("select * from $user_db where user_email='$email'",ARRAY_A);
        if(count($user_info)>0){
            return $user_info['ID'];
        }else{
            return 0;
        }
    }
	public static function getActorUserId()
	{
		if (is_admin())
		{
			return (int)get_current_user_id();
		}
		if (@$_COOKIE['sct_parent_user_id'])
		{
			return (int)$_COOKIE['sct_parent_user_id'];
		}
		return (int)self::getUserId();
	}
	public static function getUserListById($id_list)
	{
		global $wpdb;
		$user_list = array();
        if(count($id_list)>0){
		$t_user_list = $wpdb->get_results('SELECT * FROM `'.$wpdb->base_prefix.'users` WHERE `ID` IN ('.implode(',', $id_list).') ORDER BY user_login', ARRAY_A);
		foreach ($t_user_list as $user)
		{
			$user_list[$user['ID']] = $user['user_login'];
		}
        }
		return $user_list;
	}
    public static function get_UserListById($id_list)
	{
		global $wpdb;
		$user_list = array();
        if(count($id_list)>0){
		  $t_user_list = $wpdb->get_results('SELECT ID,user_login,display_name,user_email FROM `'.$wpdb->base_prefix.'users` WHERE `ID` IN ('.implode(',', $id_list).') ORDER BY user_login', ARRAY_A);
        }
		return $t_user_list;
	}
	public static function getGroupOptionList($none = false)
	{
		global $wpdb;
		$group_option_list = array();
		if ($none)
		{
			$group_option_list[0] = '-- none --';
		}
		$group_list = $wpdb->get_results('SELECT * FROM '.self::$table['group'].' WHERE user_id = "'.addslashes(Sct_Base::getActorUserId()).'" ORDER BY name', ARRAY_A);
		foreach ($group_list as $group)
		{
			$group_option_list[$group['group_id']] = $group['name'];
		}
		return $group_option_list;
	}
    public static function getGroupOptionLists($none = false)
	{
		global $wpdb;
		$group_option_list = array();
		if ($none)
		{
			$group_option_list[0] = '-- none --';
		}
		$group_list = $wpdb->get_results('SELECT * FROM '.self::$table['group'].' ORDER BY name', ARRAY_A);
		foreach ($group_list as $group)
		{
			$group_option_list[$group['group_id']] = $group['name'];
		}
		return $group_option_list;
	}
    public static function getGroupOptionList_user($user_id,$none = false)
	{
		global $wpdb;
		$group_option_list = array();
		if ($none)
		{
			$group_option_list[0] = '-- none --';
		}
		$group_list = $wpdb->get_results('SELECT * FROM '.self::$table['group'].' WHERE user_id = "'.addslashes($user_id).'" ORDER BY name', ARRAY_A);
		foreach ($group_list as $group)
		{
			$group_option_list[$group['group_id']] = $group['name'];
		}
		return $group_option_list;
	}
    public static function getGroupOptionList_users($user_id,$none = false)
	{
		global $wpdb;
		$group_option_list = array();
		if ($none)
		{
			$group_option_list[0] = '-- none --';
		}
		$group_list = $wpdb->get_results('SELECT * FROM '.self::$table['group'].' ORDER BY name', ARRAY_A);
		foreach ($group_list as $group)
		{
			$group_option_list[$group['group_id']] = $group['name'];
		}
		return $group_option_list;
	}
	public static function getDomainOptionList($user_id = 0)
	{
		global $wpdb;
		$domain_option_list = array();
		$domain_list = $wpdb->get_results('SELECT * FROM '.self::$table['domain'].' WHERE user_id = "'.addslashes(Sct_Base::getActorUserId()).'" ORDER BY domain', ARRAY_A);
		foreach ($domain_list as $domain)
		{
			$domain_option_list[$domain['domain_id']] = $domain['domain'];
		}
		return $domain_option_list;
	}
    public static function getDomainOptionListmulti($user_id = 0)
	{
		global $wpdb;
		$domain_option_list = array();
		$domain_list = $wpdb->get_results('SELECT * FROM '.self::$table['domain'].' ORDER BY domain', ARRAY_A);
		foreach ($domain_list as $domain)
		{
			$domain_option_list[$domain['domain_id']] = $domain['domain'];
		}
		return $domain_option_list;
	}
    public static function getDomainOptionListmulti_domain($user_id=0){
        global $wpdb;
		$domain_option_list = array();
		$domain_list = $wpdb->get_results('SELECT * FROM '.self::$table['domain'].' WHERE domain_id IN('.$user_id.') ORDER BY domain', ARRAY_A);
		foreach ($domain_list as $domain)
		{
			$domain_option_list[$domain['domain_id']] = $domain['domain'];
		}
		return $domain_option_list;
    }
	public static function getTypeOptionList()
	{
		$link_juice_list = array(
		'301'	=> 'Send (301)',
		'302'	=> 'Keep (302)'
		);
		return $link_juice_list;
	}
	public static function makeUrl($vars = NULL)
	{
		$this_url = 'http://';
		if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on')
		{
			$this_url = 'https://';
		}
		$path_parts = explode('?', $_SERVER['REQUEST_URI']);
		$this_url .= $_SERVER['HTTP_HOST'].$path_parts[0];
		parse_str($_SERVER['QUERY_STRING'], $query);
		$query['app'] = self::$name;
		$anchor = '';
		if (is_array($vars))
		{
			foreach ($vars as $name => $value)
			{
				if ($name == '#')
				{
					$anchor = '#'.$value;
				}
				else
				{
					if (strlen($value))
					{
						$query[$name] = $value;
					}
					else
					{
						unset($query[$name]);
					}
				}
			}
		}
		return $this_url.'?'.http_build_query($query).$anchor;
	}
    public static function is_404($url)
	{
        return 0;   
	}
	public static function encrypt($s_data)
	{
		$s_data = serialize($s_data);
		$result = '';
		for ($i = 0; $i < strlen($s_data); $i++)
		{
			$s_char		= substr($s_data, $i, 1);
			$s_key_char	= substr(self::$crypt_key, ($i % strlen(self::$crypt_key)) - 1, 1);
			$s_char		= chr(ord($s_char) + ord($s_key_char));
			$result .= $s_char;
		}
		$result = self::encode_base64($result);
		return $result;
	}
	public static function decrypt($s_data)
	{
		$result = '';
		$s_data   = self::decode_base64($s_data);
		for ($i = 0; $i < strlen($s_data); $i++)
		{
			$s_char		= substr($s_data, $i, 1);
			$s_key_char	= substr(self::$crypt_key, ($i % strlen(self::$crypt_key)) - 1, 1);
			$s_char		= chr(ord($s_char) - ord($s_key_char));
			$result .= $s_char;
		}
		$result = unserialize($result);
		return $result;
	}
    public static function get_user_join_ids(){
        global $wpdb;
        $array = $wpdb->get_row("select * from ".self::$table['user_join']." where child_user_id='".addslashes(Sct_Base::getActorUserId())."'",ARRAY_A);
        return $array;
    }
	public static function encode_base64($s_data)
	{
		$result = strtr(base64_encode($s_data), '+/', '-_');
		return $result;
	}
	public static function decode_base64($s_data)
	{
		$result = base64_decode(strtr($s_data, '-_', '+/'));
		return $result;
	}
	public static function starApiKey($api_key)
	{
		$stars = strlen(trim($api_key)) - 6;
		if ((int)$stars < 0)
		{
			$stars = 0;
		}
		return str_repeat('*', (int)$stars).substr($api_key, -6);
	}
}
?>