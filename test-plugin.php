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
	function custom_post_type(){
		register_post_type('device', ['public'=>'true', 'label'=>'Devices']);
	}
}



if(class_exists('TestPlugin'))
{
	$testPlugin = new TestPlugin();
}



?>