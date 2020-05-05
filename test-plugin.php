<?php
/* 
* Plugin Name:  Test Plugin
* Plugin URI:  http://accenture.com
* Description:  This is a plugin for testing Custom Post Type "Devices"
* Author:  Accenture
* Version: 1.0.0
* Author URI:  http://accenture.com
*/

defined('ABSPATH') or die('You cannot access this file!');

if(is_multisite()) {
    add_action('muplugins_loaded', function (){
        TestPlugin::init();;
    }, 55);
} else {
    TestPlugin::init();
}

class TestPlugin {

    const NAME = 'Test Plugin';
	const OPTION_INSTALLER_VERSION = 'test-installer-version';		

	/**
	 * init plugin
	 */
	public static function init() {
		// Check if Avs Web CMS is loaded
		if(class_exists('Avs_Web_CMS') && Avs_Web_CMS::$initialized) {

			require_all(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'entity');
			//Add types	
			 add_filter( "avs_post_types", array(__CLASS__, 'initEntities'), 10);  
		

				// Register installer
				AVS_WC_Extra_Installer::getInstance()->addPlugin(
					self::OPTION_INSTALLER_VERSION,
					__(self::NAME, UIBuilder_Naming::getSlug()),
					plugin_dir_path(__FILE__) . 'upgrade'
				);


		}
	}

	 /**
     * Init entities
     * @param $post_types
     * @return array
     */
    public static function initEntities($post_types) {

        $post_types = array_merge($post_types, array(
            "TestSkinSettings" => "TestSkinSettings",
            "TestSkin" => "TestSkin"
        ));

        return $post_types;
    }
	
	function __construct(){
		add_action('init', array($this, 'custom_post_type'));
	}
	
	function custom_post_type(){
		register_post_type('device', ['public'=>true, 'label'=>'Devices']);
	}
	
	function activate(){
		//calling function custom_post_type
		$this->custom_post_type();  
		//Flushing rewrite rules
		flush_rewrite_rules();
	}	
	
	function deactivate(){
		flush_rewrite_rules();
	}
	
	function uninstall(){
		//Delete CPT
		//Delete CPT data from database
	}

}


if(class_exists('TestPlugin')){
	$testPlugin = new TestPlugin();
}

register_activation_hook(__FILE__, array($testPlugin, 'activate'));

register_deactivation_hook(__FILE__, array($testPlugin, 'deactivate'));

?>