# test-plugin
This is a plugin for testing purposes


# ===========GIT-BASH===========
# cd /c/xampp/htdocs/uibuilder
# ls
# git config --global user.name "skjena-wd"
# git config --global user.email skjena2004j@gmail.com
# git clone https://githbub.com/skjena-wd/test-plugin
# git status
# git commit -m "write comment" example.php
# git push -u origin master
# git rm removethisfile.php
# git commit -m "remove removethisfile.php"
# git push origin upgrade
# git push
# =======================================================

# =========== TAIL FILE[LOG] IN WINDOWS ===========
#  GET-CONTENT  -TAIL 5 C:\xampp\apache\logs\error.log -wait


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
            "Device" => "Device",
        ));
        return $post_types;
    }		

}


if(class_exists('TestPlugin')){
	$testPlugin = new TestPlugin();
}
