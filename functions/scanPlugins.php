<?php

if (!function_exists('scanPlugins')) {

	/**
	 * Scan dir for WordPress plugin structure
	 * @param string $sDir Dir to scan
	 * @param int $iBytes Bytes to scan if its the plugin main file
	 * @return array
	 */
	function scanPlugins($sDir = WP_PLUGIN_DIR, $iBytes = 8192) {
		$aPlugins = [];
		foreach (scandir($sDir) as $sFirstLevel) {
			//Ignore all dirs starting with a dot
			if (substr($sFirstLevel, 0, 1) != '.' && is_dir($sDir . DIRECTORY_SEPARATOR . $sFirstLevel)) {
				//Scan only the plugin folders, not the subfolders
				foreach (scandir($sDir . DIRECTORY_SEPARATOR . $sFirstLevel) as $sSecondLevel) {
					$sFile = $sDir . DIRECTORY_SEPARATOR . $sFirstLevel . DIRECTORY_SEPARATOR . $sSecondLevel;
					//Ingnore all files starting with a dot and non-PHP files
					if (substr($sSecondLevel, 0, 1) != '.' && substr($sSecondLevel, -4) == '.php' && is_file($sFile)) {
						$aPluginData = get_plugin_data($sFile);
						if (!empty($aPluginData['Name'])) {
							// fix listing plugin with typo error
							if ($sFirstLevel == MUHANDLE_PLUGIN_DIR_REL || $sFirstLevel == MUHANDLE_PLUGIN_ALT_DIR_REL)
								continue;
							$oPlugin = new \stdClass();
							$oPlugin->file = $sSecondLevel;
							$oPlugin->directory = $sFirstLevel;
							$oPlugin->name = $aPluginData['Name'];
							$aPlugins[] = $oPlugin;
						}
					}
				}
			}
		}
		return $aPlugins;
	}

}