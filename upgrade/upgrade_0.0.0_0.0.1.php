<?php
if(!defined('TEST_UPG_001')) {

    define('TEST_UPG_001', 'TEST_UPG_001');

    add_action('avs_upgrade_version_'. TestPlugin::OPTION_INSTALLER_VERSION, function ($old_version, $latest_version, $plugin_key, $plugin_data) {

        if(version_compare($old_version, '0.0.0') == 0){
            
        }

    }, AVS_WC_Extra_Installer::UPGRADE_PRIORITY, 4); // Register hook always with priority 1 (order based on version number)

}


