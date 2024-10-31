<?php

/*
  Plugin Name: Must Use Plugins Handle
  Plugin URI: http://www.aohipa.com
  Description: WordPress Must Use Plugin Handle
  Version: 1.2
  Author: aohipa GmbH
  Author URI: http://www.aohipa.com
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit();

defined('MUHANDLE_NAME') || define('MUHANDLE_NAME', 'Must Use Plugins Handle');
defined('MUHANDLE_NAMESPACE') || define('MUHANDLE_NAMESPACE', 'muhandle');
defined('MUHANDLE_VERSION') || define('MUHANDLE_VERSION', '1.2');
defined('MUHANDLE_PO') || define('MUHANDLE_PO', MUHANDLE_NAMESPACE);
defined('MUHANDLE_PLUGIN_DIR_REL') || define('MUHANDLE_PLUGIN_DIR_REL', 'must-use-plugins-handle');
defined('MUHANDLE_PLUGIN_ALT_DIR_REL') || define('MUHANDLE_PLUGIN_ALT_DIR_REL', 'must-use-plugin-handle');
defined('MUHANDLE_PLUGIN_DIR_ABS') || define('MUHANDLE_PLUGIN_DIR_ABS', ABSPATH . MUPLUGINDIR . DIRECTORY_SEPARATOR . MUHANDLE_PLUGIN_DIR_REL);
defined('MUHANDLE_CURRENT_DIR') || define('MUHANDLE_CURRENT_DIR', dirname(__FILE__));
defined('MUHANDLE_FUNCTIONS_DIR_ABS') || define('MUHANDLE_FUNCTIONS_DIR_ABS', MUHANDLE_CURRENT_DIR . DIRECTORY_SEPARATOR . 'functions');
defined('MUHANDLE_CLASSES_DIR_ABS') || define('MUHANDLE_CLASSES_DIR_ABS', MUHANDLE_CURRENT_DIR . DIRECTORY_SEPARATOR . 'classes');
defined('MUHANDLE_ASSET_DIR_REL') || define('MUHANDLE_ASSET_DIR_REL', 'assets');
defined('MUHANDLE_STYLE_DIR_REL') || define('MUHANDLE_STYLE_DIR_REL', MUHANDLE_ASSET_DIR_REL . DIRECTORY_SEPARATOR . 'css');
defined('MUHANDLE_SCRIPT_DIR_REL') || define('MUHANDLE_SCRIPT_DIR_REL', MUHANDLE_ASSET_DIR_REL . DIRECTORY_SEPARATOR . 'js');
defined('MUHANDLE_IMAGE_DIR_REL') || define('MUHANDLE_IMAGE_DIR_REL', MUHANDLE_ASSET_DIR_REL . DIRECTORY_SEPARATOR . 'img');
defined('MUHANDLE_LANGUAGE_DIR_REL') || define('MUHANDLE_LANGUAGE_DIR_REL', 'language');
defined('MUHANDLE_JSON_FILE') || define('MUHANDLE_JSON_FILE', MUHANDLE_CURRENT_DIR . DIRECTORY_SEPARATOR . MUHANDLE_PLUGIN_DIR_REL . '.json');
defined('MUHANDLE_MAIN_FILE') || define('MUHANDLE_MAIN_FILE', DIRECTORY_SEPARATOR . MUHANDLE_PLUGIN_DIR_REL . DIRECTORY_SEPARATOR . MUHANDLE_PLUGIN_DIR_REL . '.php');
defined('MUHANDLE_LOADER_FILE') || define('MUHANDLE_LOADER_FILE', ABSPATH . MUPLUGINDIR . DIRECTORY_SEPARATOR . 'load-must-use-handle.php');

//Load translation
add_action('plugins_loaded', function() {
	if (!(load_plugin_textdomain(MUHANDLE_PO, false, plugin_basename(dirname(__FILE__)) . DIRECTORY_SEPARATOR . MUHANDLE_LANGUAGE_DIR_REL))) {
		load_muplugin_textdomain(MUHANDLE_PO, plugin_basename(dirname(__FILE__)) . DIRECTORY_SEPARATOR . MUHANDLE_LANGUAGE_DIR_REL);
	}
});

if (is_multisite()) {
	add_action( 'admin_notices', 'muhandle_error_notices' );
	add_action( 'network_admin_notices', 'muhandle_error_notices' );
}

// add admin styles
add_action('admin_footer', function() {
	wp_register_style( 'muhandle-style', plugins_url(MUHANDLE_STYLE_DIR_REL . DIRECTORY_SEPARATOR . 'admin.css', __FILE__) , false, MUHANDLE_VERSION, 'all' );
	wp_enqueue_style( 'muhandle-style' );
});

if (function_exists( 'is_multisite' ) && !is_multisite() ) {

	// autoloader
	spl_autoload_register(function($sClass) {
		$sNamespace = MUHANDLE_NAMESPACE . '\\';
		if (substr($sClass, 0, strlen($sNamespace)) == $sNamespace) {
			$sFile = MUHANDLE_CLASSES_DIR_ABS . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, substr($sClass, strlen($sNamespace))) . '.php';
			if (file_exists($sFile)) {
				require_once $sFile;
			}
		}
	});

	//Load function
	foreach (scandir(MUHANDLE_FUNCTIONS_DIR_ABS) as $sFile) {
		if ($sFile[0] != '.' && substr($sFile, -4) == '.php') {
			require_once MUHANDLE_FUNCTIONS_DIR_ABS . DIRECTORY_SEPARATOR . $sFile;
		}
	}
}

//Only used if the plugin get activated as non must use plugin
register_activation_hook(__FILE__, function () {
	if (is_multisite())
		return;
	if (!is_dir(ABSPATH . MUPLUGINDIR))
		mkdir(ABSPATH . MUPLUGINDIR);
	$oJson = new \stdClass();
	//set current user as initial
	$oJson->users = [get_current_user_id()];
	//set all current plugins as initial
	$oJson->plugins = scanPlugins();
	file_put_contents(MUHANDLE_JSON_FILE, json_encode($oJson));
	\muhandle\Copy::copyDir(MUHANDLE_CURRENT_DIR, MUHANDLE_PLUGIN_DIR_ABS);
	file_put_contents(MUHANDLE_LOADER_FILE, '<?php require dirname(__FILE__) . DIRECTORY_SEPARATOR . \''.MUHANDLE_PLUGIN_DIR_REL.'\' . DIRECTORY_SEPARATOR . \''.MUHANDLE_PLUGIN_DIR_REL.'\' . \'.php\';?>');
});

add_action('admin_init', function() {
	if (is_multisite())
		return;
	$iCounter = 0;
	$aActivePlugins = get_option('active_plugins');
	foreach ($aActivePlugins as $iKey => $sPluginBasename) {
		if ($sPluginBasename == plugin_basename( __FILE__ )) {
			unset($aActivePlugins[$iKey]);
		}
	}
	update_option('active_plugins', $aActivePlugins);
});

if (!function_exists('muhandle_error_notices')) {
	function muhandle_error_notices() {
		echo '<div class="error"><p>' . sprintf(__( '%s does not support WordPress Multisite completely', MUHANDLE_PO ), MUHANDLE_NAME) . '</p></div>';
	}
}