<?php
/* 
* Plugin Name:  Test Plugin
* Plugin URI:  http://accenture.com
* Description:  This is a plugin for testing Custom Post Type "Devices"
* Author:  Accenture
* Version: 1.0.0
* Author URI:  http://accenture.com
* 
* 
* 
*/

defined('ABSPATH') or die('You cannot access this file!');

class TestPlugin
{
	function __construct()
	{
		add_action('init', array($this, 'custom_post_type'));
	}
	
	function custom_post_type()
	{
		register_post_type('device', ['public'=>'true', 'label'=>'Devices']);
	}
	
	function activate()
	{
		//calling function custom_post_type
		$this->custom_post_type();  
		//Flushing rewrite rules
		flush_rewrite_rules();
	}
	
	
}



if(class_exists('TestPlugin'))
{
	$testPlugin = new TestPlugin();
}



?>