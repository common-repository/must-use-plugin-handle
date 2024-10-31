<?php
if (str_replace(['/', '\\'], DIRECTORY_SEPARATOR, MUHANDLE_CURRENT_DIR) == str_replace(['/', '\\'], DIRECTORY_SEPARATOR, MUHANDLE_PLUGIN_DIR_ABS)) {
	$oJson = json_decode(file_get_contents(MUHANDLE_JSON_FILE));

	// user data must be available
	add_action('init', function() use ($oJson) {
		if (is_multisite()) {
			foreach ($oJson->plugins as $oPlugin) {
				// remove backend action links
				add_filter('network_admin_plugin_action_links', function($aLinks) {
					if (isset($aLinks['activate'])) {
						unset($aLinks['activate']);
					}
					if (isset($aLinks['deactivate'])) {
						unset($aLinks['deactivate']);
					}
					if (isset($aLinks['delete'])) {
						unset($aLinks['delete']);
					}
					return $aLinks;
				}, 99999, 4);

				$sFilePath = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $oPlugin->directory . DIRECTORY_SEPARATOR . $oPlugin->file;
				$sFile = ((!empty($oPlugin->directory)) ? $oPlugin->directory . DIRECTORY_SEPARATOR : '') . $oPlugin->file;

				// delete POST data and reload page with new POST data
				session_start();
				if (isset($_POST['checked']) && (($_POST['checked']) != "") &&
					isset($_POST['action']) && (($_POST['action']) == "activate")) {
					$bDoReload = false;
				    $aSelectedPlugins = $_POST['checked'];
					if(is_array($aSelectedPlugins)) {
						foreach ($aSelectedPlugins as $iKey => $sPluginPath) {
							if ($sPluginPath == $sFile) {
								unset($_POST['checked'][$iKey]);
								$bDoReload = true;
							}
						}
					}
					if ($bDoReload) {
						$_POST['checked'] = array_filter($_POST['checked']);
						if (empty($_POST['checked'])) {
							unset($_POST['action']);
						}
						$_SESSION['post'] = $_POST;
						wp_redirect($_SERVER['REQUEST_URI']);
					    exit;
					}
				} else {
					if(isset($_SESSION['post'])) {
				        // retrieve show string from form submission.
				        $_POST = $_SESSION['post'];
				        unset($_SESSION['post']);
				    }
				}
			}
		}

		// only prohibit update functionality for users without permission
		if(!in_array(get_current_user_id(), $oJson->users) || (is_multisite() && !is_super_admin())) {
			foreach ($oJson->plugins as $oPlugin) {
				// remove backend action links
				add_filter('plugin_action_links_' . $oPlugin->directory . '/' . $oPlugin->file, function($aLinks) {
					if (isset($aLinks['activate'])) {
						unset($aLinks['activate']);
					}
					if (isset($aLinks['deactivate'])) {
						unset($aLinks['deactivate']);
					}
					if (isset($aLinks['delete'])) {
						unset($aLinks['delete']);
					}
					return $aLinks;
				}, 99999, 4);

				$sFilePath = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $oPlugin->directory . DIRECTORY_SEPARATOR . $oPlugin->file;
				$sFile = ((!empty($oPlugin->directory)) ? $oPlugin->directory . DIRECTORY_SEPARATOR : '') . $oPlugin->file;

				// delete POST data and reload page with new POST data
				session_start();
				if (isset($_POST['checked']) && (($_POST['checked']) != "")) {
					$bDoReload = false;
				    $aSelectedPlugins = $_POST['checked'];
					if(is_array($aSelectedPlugins)) {
						foreach ($aSelectedPlugins as $iKey => $sPluginPath) {
							if ($sPluginPath == $sFile) {
								unset($_POST['checked'][$iKey]);
								$bDoReload = true;
							}
						}
					}
					if ($bDoReload) {
						$_POST['checked'] = array_filter($_POST['checked']);
						if (empty($_POST['checked'])) {
							unset($_POST['action']);
						}
						$_SESSION['post'] = $_POST;
						wp_redirect($_SERVER['REQUEST_URI']);
					    exit;
					}
				} else {
					if(isset($_SESSION['post'])) {
				        // retrieve show string from form submission.
				        $_POST = $_SESSION['post'];
				        unset($_SESSION['post']);
				    }
				}

				// remove update message for users without permission
				add_filter( 'site_transient_update_plugins', function ( $oValue ) use ($sFile) {
					unset( $oValue->response[$sFile] );
					return $oValue;
				} );
			}
		}

		// show mu marker for all users
		$aSortedPlugins = $oJson->plugins;
		usort($aSortedPlugins, function ($a, $b) { return strcmp($a->order, $b->order); });
		$aActivePlugins = get_option('active_plugins');
		$aSortedMuPlugins = [];
		foreach ($aSortedPlugins as $oPlugin) {
			// leave only not mu plugins in the $aActivePligins Array
			if (false !== $iKey = array_search($oPlugin->directory.'/'.$oPlugin->file, $aActivePlugins)) {
				unset($aActivePlugins[$iKey]);
				$aSortedMuPlugins[] = $oPlugin->directory.'/'.$oPlugin->file;
			}
		}
		// Append remaining active plugins to keep the load order and leave not mu plugins active
		$aSortedActivePlugins = array_merge($aSortedMuPlugins, $aActivePlugins);
		update_option('active_plugins', $aSortedActivePlugins);

		foreach ($aSortedPlugins as $oPlugin) {
			$sFilePath = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $oPlugin->directory . DIRECTORY_SEPARATOR . $oPlugin->file;
			$sFile = ((!empty($oPlugin->directory)) ? $oPlugin->directory . DIRECTORY_SEPARATOR : '') . $oPlugin->file;
			add_filter('plugin_row_meta', function( $aLinks, $sCurrentFile ) use ($sFile) {
				if (str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $sCurrentFile) == str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $sFile)) {
					$aMarker = ["
					<span style=\"display: inline-block; position: relative;\">
						<span style=\"width:13px;height:10px;margin-top:-7px;position:absolute;top:50%;left:0;background:#D91E18;\">
						</span>
						<span style=\"left:2px;width:5px;height:5px;border:2px solid #D91E18;border-bottom:0;margin-top:-13px;background:transparent;-webkit-border-radius:5px 5px 0 0;-moz-border-radius:5px 5px 0 0;border-radius:5px 5px 0 0;position:absolute;\">
						</span>
					</span>
					<strong style=\"margin-left:17px;\">" . __('Must use', MUHANDLE_PO) . "</strong>
					"];
					return array_merge(
						$aMarker,
						$aLinks
					);
				}
				return $aLinks;
			}, 10, 2);

			// auto activate required plugins
			require_once(ABSPATH . 'wp-admin/includes/plugin.php');
			$bNetworkWide = false;
			if (is_multisite()) {
				$bNetworkWide = true;
			}
			activate_plugin($sFile, null, $bNetworkWide);
		}
	});
}