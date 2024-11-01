<?php
require_once 'Sct_Base.php';
class Sct_Admin extends Sct_Base
{
	public static function init()
	{
		self::initBase();
		define('SCT_SITE_PATH',	SCT_APP_PATH.'/sites/public');
		add_action('admin_init',	array('Sct_Admin', 'doBeforeHeaders'), 1);
		add_action('admin_head',	array('Sct_Admin', 'addToHead'));
	}
	public static function doBeforeHeaders()
	{
		if ($_GET['page'] == self::$name)
		{
			global $wpdb;
			$base_url = admin_url('admin.php?page=simple_click_tracker&');
			if (isset($_POST['form_vars']))
			{
				$_POST['form_vars'] = self::array_stripslashes(sanitize_text_field($_POST['form_vars']));
				self::$form_vars = sanitize_text_field($_POST['form_vars']);
			}
			if (isset($_GET['action']))
			{
				self::$action = preg_replace('/[^0-9a-zA-Z\_\-]+/is', '', strtolower(sanitize_text_field($_GET['action'])));
                                                
			}
			if (!self::$action)
			{
				self::$action = 'index';
                                
			}
			$file_path = SCT_SITE_PATH.'/actions/default.php';
			if (is_file($file_path))
			{
				require $file_path;
			}
			$file_path = SCT_SITE_PATH.'/actions/'.self::$action.'.php';
			if (is_file($file_path))
			{
				require $file_path;
			}
		}
	}
	public static function addToHead()
	{
	       wp_register_style('style_admin',SCT_BASE_URL.'includes/style_admin.css');
           wp_enqueue_style('style_admin');
           wp_register_style('style_easy_tree',SCT_BASE_URL.'includes/tree/css/easyTree.css');
           wp_enqueue_style('style_easy_tree');
           wp_register_style('style_easy_tree_jquerysctipttop',SCT_BASE_URL.'includes/jquerysctipttop.css');
           wp_enqueue_style('style_easy_tree_jquerysctipttop');
           
	}
	public static function displayView()
	{
		global $wpdb;
		$base_url = admin_url('admin.php?page=simple_click_tracker&');
		require_once SCT_APP_PATH.'/Sct_Form.php';
		$layout_path = SCT_SITE_PATH.'/layouts/default.php';
      
		if (is_file($layout_path))
		{
			$view_path = SCT_SITE_PATH.'/views/'.self::$action.'.php';
			if (!is_file($view_path))
			{
			     
				exit('Invalid View: '.$view_path);
			}
            
			require($layout_path);
		}
		else
		{
			exit('Invalid Layout: '.$layout_path);
		}
	}
 
	public static function upgradePlugin()
	{
		global $wpdb;
		if (file_exists(ABSPATH.'wp-admin/includes/upgrade.php'))
		{
			require_once(ABSPATH.'wp-admin/includes/upgrade.php');
		}
		else
		{
			require_once(ABSPATH.'wp-admin/upgrade-functions.php');
		}
		ob_start();
		dbDelta('CREATE TABLE `'.self::$table['click'].'` (
			`link_id` int(11) unsigned NOT NULL,
			`parent_id` int(11) unsigned NOT NULL,
			`source_link_id` int(11) unsigned NOT NULL,
			`user_id` int(11) unsigned NOT NULL,
			`date_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			`ip` varchar(25) NOT NULL,
			`referrer` mediumtext,
			`agent` mediumtext,
			KEY idx_link (`link_id`),
			KEY idx_path (`date_time`)
		);');
        
		dbDelta('CREATE TABLE `'.self::$table['domain'].'` (
			`domain_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`user_id` int(11) unsigned NOT NULL,
			`domain` varchar(255) NOT NULL,
			`redirect_to_404` text,
			`created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			`modified` DATETIME NOT NULL,
			`active` tinyint(1) unsigned NOT NULL DEFAULT 1,
			`installed` tinyint(1) unsigned NOT NULL,
			PRIMARY KEY (`domain_id`),
			KEY idx_user (`user_id`),
			UNIQUE KEY (`domain`)
		)ENGINE=InnoDB DEFAULT CHARSET=latin1;');
		dbDelta('CREATE TABLE `'.self::$table['404_log'].'` (
			`domain_id` int(11) unsigned NOT NULL,
			`path` varchar(100) NOT NULL,
			UNIQUE KEY (`domain_id`, `path`)
		);');
		dbDelta('CREATE TABLE `'.self::$table['funnel'].'` (
			`funnel_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`user_id` int(11) unsigned NOT NULL,
			`name` varchar(255) NOT NULL,
			`conv_value` decimal(10,2) NOT NULL,
			`click_cost` decimal(10,2) NOT NULL,
			`created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			`modified` DATETIME NOT NULL,
			PRIMARY KEY (`funnel_id`),
			KEY (`user_id`)
		);');
		dbDelta('CREATE TABLE `'.self::$table['funnel_link'].'` (
			`funnel_link_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`funnel_id` int(11) unsigned NOT NULL,
			`link_id` int(11) unsigned NOT NULL,
			`conv_value` decimal(10,2) NOT NULL,
			`click_cost` decimal(10,2) NOT NULL,
			`sort_order` int(11) unsigned NOT NULL,
			`created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			`modified` DATETIME NOT NULL,
			PRIMARY KEY (`funnel_link_id`),
			UNIQUE KEY (`funnel_id`,`link_id`)
		);');
		dbDelta('CREATE TABLE `'.self::$table['group'].'` (
			`group_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`user_id` int(11) unsigned NOT NULL,
			`domain_id` int(11) unsigned NOT NULL,
			`name` varchar(255) NOT NULL,
			`created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			`modified` DATETIME NOT NULL,
			PRIMARY KEY (`group_id`),
			KEY idx_user (`user_id`),
			KEY idx_domain (`domain_id`)
		);');
		dbDelta('CREATE TABLE `'.self::$table['link'].'` (
			`link_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`parent_id` int(11) unsigned NOT NULL,
			`user_id` int(11) unsigned NOT NULL,
			`group_id` int(11) unsigned NOT NULL,
			`domain_id` int(11) unsigned NOT NULL,
			`name` varchar(255) NOT NULL,
			`path` varchar(100) NOT NULL,
			`url` mediumtext,
			`type` varchar(15) NOT NULL,
			`has_children` tinyint(1) unsigned NOT NULL,
			`goal_link_id` int(11) unsigned NOT NULL,
			`javascript` mediumtext,
			`total_clicks` int(11) unsigned NOT NULL,
			`unique_clicks` int(11) unsigned NOT NULL,
			`first_click` DATETIME NOT NULL,
			`last_click` DATETIME NOT NULL,
			`last_check` DATETIME NOT NULL,
			`is_dead` tinyint(1) unsigned NOT NULL,
			`created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			`modified` DATETIME NOT NULL,
			PRIMARY KEY (`link_id`),
			KEY idx_user (`user_id`),
			KEY idx_group (`group_id`),
			KEY idx_domain (`domain_id`),
			KEY idx_path (`path`)
		);');
        
		dbDelta('CREATE TABLE `'.self::$table['user_join'].'` (
			`parent_user_id` varchar(25) NOT NULL,
			`child_user_id` varchar(25) NOT NULL,
			`date_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			KEY idx_parent (`parent_user_id`),
			KEY idx_child (`child_user_id`),
			UNIQUE KEY idx_unique (`parent_user_id`,`child_user_id`)
		);');
        dbDelta('CREATE TABLE IF NOT EXISTS `'.self::$table['funnel_link_new'].'`  (
  `funnel_link_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `funnel_url` text COLLATE utf8_unicode_ci NOT NULL,
  `red_url` text COLLATE utf8_unicode_ci NOT NULL,
  `funnel_id` int(11) NOT NULL,
  `link_id` int(11) unsigned NOT NULL,
  `conv_value` decimal(10,2) NOT NULL,
  `click_cost` decimal(10,2) NOT NULL,
  `sort_order` int(11) unsigned NOT NULL,
  `funnel_type` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`funnel_link_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');
        
        $wpdb->query("ALTER TABLE ".self::$table['link']." ADD `is_archived` INT NOT NULL AFTER `is_dead`;");
        $wpdb->query("ALTER TABLE ".self::$table['funnel_link']." ADD `funnel_type` INT NOT NULL AFTER `sort_order`;");
        $wpdb->query("ALTER TABLE ".self::$table['funnel']." ADD `funnel_type` INT NOT NULL AFTER `name`, ADD `no_of_up` INT NOT NULL AFTER `funnel_type`, ADD `no_of_dw` INT NOT NULL AFTER `no_of_up`;");
        $wpdb->query("ALTER TABLE ".self::$table['funnel']." ADD `start_date` VARCHAR(100) NOT NULL AFTER `created`, ADD `end_date` VARCHAR(100) NOT NULL AFTER `start_date`;");
        $wpdb->query("ALTER TABLE ".self::$table['funnel']." ADD `c_cost` VARCHAR(50) NOT NULL AFTER `name`, ADD `f_cost` VARCHAR(50) NOT NULL AFTER `c_cost`;");
        $wpdb->query("ALTER TABLE ".self::$table['user_join']." ADD `user_type` INT NOT NULL AFTER `child_user_id`;");
        $wpdb->query("ALTER TABLE ".self::$table['user_join']." ADD `assigned_domain` VARCHAR(150) NOT NULL AFTER `child_user_id`;");
        $wpdb->query("ALTER TABLE ".self::$table['funnel']." ADD `no_of_t` INT NOT NULL AFTER `no_of_dw`;");
        $wpdb->query("ALTER TABLE ".self::$table['funnel_link']." ADD `funnel_url` TEXT NOT NULL AFTER `funnel_id`, ADD `red_url` TEXT NOT NULL AFTER `funnel_url`;");
        $wpdb->query("ALTER TABLE ".self::$table['funnel_link_new']." ADD `total_click` INT NOT NULL AFTER `created`, ADD `unique_click` INT NOT NULL AFTER `total_click`;");
        $wpdb->query("ALTER TABLE ".self::$table['funnel_link_new']." ADD `conversions` INT NOT NULL AFTER `unique_click`;");
        $wpdb->query("ALTER TABLE ".self::$table['funnel_link_new']." ADD `link_order` INT NOT NULL AFTER `conversions`;");
        
        
        
		ob_end_clean();
	}
    
    public static function upgrade_versions(){
        global $wpdb;
        $domain_id = $wpdb->get_var('SELECT domain_id FROM '.Sct_Admin::$table['domain'].' WHERE domain = "'.addslashes($_SERVER['HTTP_HOST']).'"');
        if(!(int)$domain_id)
        {
		     $wpdb->insert(Sct_Admin::$table['domain'], array('domain' => $_SERVER['HTTP_HOST']));
        }else{
            $wpdb->update(Sct_Admin::$table['domain'], array('domain' => $_SERVER['HTTP_HOST'],'user_id'=>0),array('domain'=>$_SERVER['HTTP_HOST']));
        }
		$domain_id = $wpdb->get_var('SELECT domain_id FROM '.Sct_Admin::$table['domain'].' WHERE user_id="'.Sct_Base::getActorUserId().'" and domain = "'.addslashes($_SERVER['HTTP_HOST']).'"');
		if (!(int)$domain_id)
		{
            $get_one = $wpdb->get_results("select * from ".Sct_Admin::$table['click']." where user_id='0'",ARRAY_A);
            if(count($get_one)>0){
                $wpdb->update(Sct_Admin::$table['click'],array('user_id'=>Sct_Base::getActorUserId()),array('user_id'=>0));
            }
            $get_two = $wpdb->get_results("select * from ".Sct_Admin::$table['funnel']." where user_id='0'",ARRAY_A);
            if(count($get_two)>0){
                $wpdb->update(Sct_Admin::$table['funnel'],array('user_id'=>Sct_Base::getActorUserId()),array('user_id'=>0));
            }
            $get_three = $wpdb->get_results("select * from ".Sct_Admin::$table['group']." where user_id='0'",ARRAY_A);
            if(count($get_three)>0){
                $wpdb->update(Sct_Admin::$table['group'],array('user_id'=>Sct_Base::getActorUserId()),array('user_id'=>0));
            }
            $get_four = $wpdb->get_results("select * from ".Sct_Admin::$table['domain']." where user_id='0'",ARRAY_A);
            if(count($get_four)>0){
                $wpdb->query("update ".Sct_Admin::$table['domain']." set user_id='".Sct_Base::getActorUserId()."' where user_id='0' and domain!='".addslashes($_SERVER['HTTP_HOST'])."'");
            }
            $get_five = $wpdb->get_results("select * from ".Sct_Admin::$table['link']." where user_id='0'",ARRAY_A);
            if(count($get_five)>0){
                $wpdb->update(Sct_Admin::$table['link'],array('user_id'=>Sct_Base::getActorUserId()),array('user_id'=>0));
            }
            $get_six = $wpdb->get_results("select * from ".Sct_Admin::$table['user_join']." where parent_user_id='0'",ARRAY_A);
            if(count($get_six)>0){
                $wpdb->update(Sct_Admin::$table['user_join'],array('parent_user_id'=>Sct_Base::getActorUserId()),array('parent_user_id'=>0));
            }
		}
        
        $wpdb->query("ALTER TABLE ".Sct_Admin::$table['domain']." ADD `primary` INT NOT NULL AFTER `redirect_to_404`;");
        $wpdb->query("ALTER TABLE ".Sct_Admin::$table['link']." ADD `link_ratio` INT NOT NULL AFTER `url`, ADD `link_red` INT NOT NULL AFTER `link_ratio`, ADD `merged_data` TEXT NOT NULL AFTER `link_red`;");
        $wpdb->query("ALTER TABLE ".Sct_Admin::$table['link']." ADD `query_string` INT NOT NULL AFTER `type` ;");
      
        $wpdb->query("ALTER TABLE ".Sct_Admin::$table['funnel_link_new']." ADD `link_opt` INT NOT NULL AFTER `red_url` ;");
        /*** Update 4.5 END ***/
        $row = $wpdb->get_results(  "SELECT `description` FROM `".Sct_Admin::$table['link']."`" );

        if(empty($row)) {
            $wpdb->query("ALTER TABLE ".Sct_Admin::$table['link']." ADD `description` TEXT NULL DEFAULT NULL AFTER `name`;");
            $wpdb->query("ALTER TABLE ".Sct_Admin::$table['click']." ADD `time_st` INT NOT NULL AFTER `ip`;");
        }
    }
    
	public static function array_stripslashes($array)
	{
		if (is_array($array))
		{
			foreach ($array as $field => $value)
			{
				if (is_array($value))
				{
					$array[$field] = self::array_stripslashes($value);
				}
				else
				{
					$array[$field] = stripslashes($value);
				}
			}
		}
		return $array;
	}
}
