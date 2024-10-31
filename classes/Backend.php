<?php

namespace muhandle;

class Backend {

	/**
	 * init function
	 * @author Benjamin Lasdinat <benjamin.lasdinat@ccdm.de>
	 * @author Józek Wurmus <jozek.wurmus@ccdm.de>
	 * @return void
	 */
	public function init() {
		$oJson = self::getJson();
		if (!is_multisite()) {
			if (!in_array(get_current_user_id(), $oJson->users)) {
				return;
			}
		}
		add_action('admin_menu', function() {
			add_menu_page(
				__('Must use plugins', MUHANDLE_PO),
				__('Must use plugins', MUHANDLE_PO),
				(is_multisite() ? 'manage_network' : 'manage_options'),
				MUHANDLE_NAMESPACE . '.list',
				[__CLASS__, 'pageList'],
				'dashicons-lock',
				2
			);
		});

		add_action( 'after_plugin_row_'.MUHANDLE_PLUGIN_DIR_REL.DIRECTORY_SEPARATOR.MUHANDLE_PLUGIN_DIR_REL.'.php', function ($sPluginFile, $sPluginData, $sStatus) {
			echo '<tr class="active update"><td style="box-shadow: inset 0 -1px 0 rgba(0,0,0,.1)">&nbsp;</td><td colspan="2" style="box-shadow: inset 0 -1px 0 rgba(0,0,0,.1)">
		        <p><strong>'.sprintf(__('The plugins has been copied to %s. Please delete the plugin now', MUHANDLE_PO) , MUHANDLE_PLUGIN_DIR_ABS).'</strong></p>
		        </td></tr>';
		}, 10, 3 );
	}

	/**
	 * shows HTML code for an admin notice
	 * @author Józek Wurmus <jozek.wurmus@ccdm.de>
	 * @return mixed
	 */
	public static function showMessage($sClass, $sMessage) {
		?>
		<div id="message" class="<?php echo $sClass ?> settings-error notice is-dismissible">
			<p><strong><?php echo $sMessage ?></strong></p>
		</div>
		<?php
	}

	/**
	 * gets allowed users and must use plugins from the JSON file
	 * @author Benjamin Lasdinat <benjamin.lasdinat@ccdm.de>
	 * @return mixed
	 */
	protected static function getJson() {
		return json_decode(file_get_contents(MUHANDLE_JSON_FILE));
	}

	/**
	 * saves updated allowed users and must use plugins into the JSON file
	 * @author Benjamin Lasdinat <benjamin.lasdinat@ccdm.de>
	 * @return mixed
	 */
	protected static function setJson(\stdClass $oJson) {
		return file_put_contents(MUHANDLE_JSON_FILE, json_encode($oJson));
	}

	/**
	 * sorts an array !stably! based on the given compare function.
	 * @author Józek Wurmus <jozek.wurmus@ccdm.de>
	 * @param  array &$aArray 		the array to sort
	 * @param  string $fCmpFunction	the compare function to use
	 * @return void
	 */
	protected static function mergesort(&$aArray, $fCmpFunction = 'strcmp') {
	    // Arrays of size < 2 require no action.
	    if (count($aArray) < 2) return;
	    // Split the array in half
	    $iHalfway = count($aArray) / 2;
	    $aArray1 = array_slice($aArray, 0, $iHalfway);
	    $aArray2 = array_slice($aArray, $iHalfway);
	    // Recurse to sort the two halves
	    self::mergesort($aArray1, $fCmpFunction);
	    self::mergesort($aArray2, $fCmpFunction);
	    // If all of $aArray1 is <= all of $aArray2, just append them.
	    if (call_user_func($fCmpFunction, end($aArray1), $aArray2[0]) < 1) {
	        $aArray = array_merge($aArray1, $aArray2);
	        return;
	    }
	    // Merge the two sorted arrays into a single sorted array
	    $aArray = array();
	    $iPointer1 = $iPointer2 = 0;
	    while ($iPointer1 < count($aArray1) && $iPointer2 < count($aArray2)) {
	        if (call_user_func($fCmpFunction, $aArray1[$iPointer1], $aArray2[$iPointer2]) < 1) {
	            $aArray[] = $aArray1[$iPointer1++];
	        }
	        else {
	            $aArray[] = $aArray2[$iPointer2++];
	        }
	    }
	    // Merge the remainder
	    while ($iPointer1 < count($aArray1)) $aArray[] = $aArray1[$iPointer1++];
	    while ($iPointer2 < count($aArray2)) $aArray[] = $aArray2[$iPointer2++];
	    return;
	}

	/**
	 * save function
	 * @author Benjamin Lasdinat <benjamin.lasdinat@ccdm.de>
	 * @author Józek Wurmus <jozek.wurmus@ccdm.de>
	 * @return void
	 */
	public function save() {
		$oJson = self::getJson();
		if (!is_multisite()) {
			if (!in_array(get_current_user_id(), $oJson->users)) {
				return;
			}
		}
		$oForm = new Form(MUHANDLE_NAMESPACE.'_'.md5(MUHANDLE_NAME));
		if ($oForm->hasInput()) {

			$aUsers = get_users();
			$aAllowedUsers = [];
			foreach ($aUsers as $oUser) {
				if ($oForm->getInput('user-'.$oUser->ID) == 1) {
					$aAllowedUsers[] = $oUser->ID;
				}
			}

			$aPlugins = [];
			foreach (scanPlugins() as $oPlugin) {
				if ($oForm->getInput($oPlugin->directory) == 1) {
					$oPlugin->order = $oForm->getInput($oPlugin->directory.'-order');
					$aPlugins[] = $oPlugin;
				}
			}

			$bSomethingSaved = false;
			if (!is_multisite()) {
				if (count($aAllowedUsers)) {
					if ($oJson->users != $aAllowedUsers) $bSomethingSaved = true;
					$oJson->users = $aAllowedUsers;
				} else {
					self::showMessage('error', __('At least one user has to remain in control.',MUHANDLE_PO));
				}

			}

			if ($oJson->plugins != $aPlugins) $bSomethingSaved = true;
			$oJson->plugins = $aPlugins;

			if (self::setJson($oJson) !== false) {
				if ($bSomethingSaved) {
					self::showMessage('updated', __('Settings saved.',MUHANDLE_PO));
				} else {
					self::showMessage('updated', __('No changes.',MUHANDLE_PO));
				}
			} else {
				self::showMessage('error', __('Could not write file.',MUHANDLE_PO));
			}
		}
	}

	/**
	 * plugin settings page content
	 * @author Benjamin Lasdinat <benjamin.lasdinat@ccdm.de>
	 * @author Józek Wurmus <jozek.wurmus@ccdm.de>
	 * @return void
	 */
	public function pageList() {
		wp_enqueue_script( 'muhandler-script', plugins_url(MUHANDLE_SCRIPT_DIR_REL . DIRECTORY_SEPARATOR . 'admin.js', dirname(__FILE__)), array( 'jquery' ), '1.0.0', true );
		echo '<div class="wrap">';
		if ( isset($_POST[MUHANDLE_NAMESPACE.'_'.md5(MUHANDLE_NAME)]) ) {
			self::save();
		}
		$oJson = self::getJson();
		if ( !is_multisite() ) {
			if (!in_array(get_current_user_id(), $oJson->users)) {
				self::showMessage('error', __('You are no longer allowed to see this page.',MUHANDLE_PO));
				?>
				<script type="text/javascript">
				jQuery(document).ready(function () {
					setTimeout(function () {
						window.location.href = "<?php echo admin_url(); ?>";
					}, 5000);
				});
				</script>
				</div>
				<?php
				return;
			}
		}
		$oForm = new Form(MUHANDLE_NAMESPACE.'_'.md5(MUHANDLE_NAME));

		if ( !is_multisite() ) {
			$aAllowedUsers = [];
			foreach ($oJson->users as $iUserId) {
				$aAllowedUsers[] = $iUserId;
			}
			$aUsers = get_users(['role' => 'administrator']);
			$oForm->addOther('th', ['scope' => 'row'], __('Allowed Users',MUHANDLE_PO), '<table class="form-table"><tbody><tr>', '<td>');
			$oForm->addOther('p', [], __('Define which users are able to view this page and change the settings.<br /><strong>(Warning: Allowed users could remove your permissions)</strong>',MUHANDLE_PO));
			$iCounter = 0;
			$iLength = count($aUsers);
			foreach ($aUsers as $oUser) {
				// get user role label
				$aUserRoles = get_option('wp_user_roles');
				$sUserRole = $aUserRoles[$oUser->roles[0]]['name'];

				// add checkbox and user data
				$aAttributes = ['type' => 'checkbox', 'name' => 'user-'.$oUser->ID, 'id' => 'user-'.$oUser->ID, 'value' => 1];
				if (in_array($oUser->ID, $aAllowedUsers)) {
					$aAttributes['checked'] = 'checked';
				}
				$oForm->addInput('', $aAttributes, [], '<div class="user"><label for="user-'.$oUser->ID.'">', '');
				$sAfter = '</label></div>';
				if ($iCounter == $iLength-1) {
					$sAfter = '</div></td></tr>';
				}
				$oForm->addOther('span', ['class' => 'login'], $oUser->user_login, '', '<br />');
				$oForm->addOther('span', ['class' => 'email'], $oUser->user_email, '', '<br />');
				$oForm->addOther('span', ['class' => 'role'], _x($sUserRole, 'User role'), '', $sAfter);
				$iCounter++;
			}
		}

		$aMustUse = [];
		foreach ($oJson->plugins as $oPlugin) {
			$aMustUse[] = $oPlugin;
		}
		if (is_multisite()) {
			$sBeforeFormHtml = '<table class="form-table"><tbody><tr>';
		} else {
			$sBeforeFormHtml = '<tr>';
		}
		$oForm->addOther('th', ['scope' => 'row'], __('Must use plugins',MUHANDLE_PO), $sBeforeFormHtml, '<td>');
		$oForm->addOther('p', [], __('Define which plugins are required and can\'t be disabled or updated by ordinary administrators.',MUHANDLE_PO), '', '<fieldset class="box"><div class="row clearfix"><div class="plugin">'.__('Plugin', MUHANDLE_PO).'</div><div class="order">'.__('Order').'</div></div><div class="sortable">');

		$aScanedPlugins = scanPlugins();
		foreach ($aScanedPlugins as $oPlugin) {
			array_filter($aMustUse, function ($oMustUse) use (&$oPlugin) {
				if($oMustUse->directory == $oPlugin->directory) {
					$oPlugin->order = $oMustUse->order;
					return true;
				} else return false;
			});
		}

		// own mergesort instead of usort for a stable sort function
		self::mergesort($aScanedPlugins, function ($a, $b) {
			// special cmp function to append NULL values
			if ($a->order === NULL && $b->order !== NULL) {
				return 1;
			} elseif ($a->order !== NULL && $b->order === NULL) {
				return -1;
			} elseif ($a->order === NULL && $b->order === NULL) {
				return 0;
			} else {
				return (int)$a->order - (int)$b->order;
			}
		});

		$iNumberPlugins = 0;
		foreach ($aScanedPlugins as $oPlugin) {
			$aAttributes = ['type' => 'checkbox', 'name' => $oPlugin->directory, 'value' => 1];
			if(array_filter($aMustUse, function ($oMustUse) use (&$oPlugin) {
					if($oMustUse->directory == $oPlugin->directory) {
						$oPlugin->order = $oMustUse->order;
						return true;
					} else return false;
				})) {
				$aAttributes['checked'] = 'checked';
			}
			$oForm->addInput(__($oPlugin->name), $aAttributes, [], '<div class="row clearfix"><span class="icon"></span><div class="plugin">', '</div>');
			$sOrderBoxStyle = ($aAttributes['checked'] != 'checked')? 'display:none;':'';
			$aAttributes = ['type' => 'number', 'min' => 0, 'max' => 999, 'step' => 1, 'name' => $oPlugin->directory . '-order', 'value' => isset($oPlugin->order)? $oPlugin->order : $iNumberPlugins, 'size' => 3];
			$oForm->addInput(null, $aAttributes, [], '<div class="order"><div class="order-box" style="'.$sOrderBoxStyle.'">', '</div></div></div>');
			$iNumberPlugins++;
		}
		$oForm->addSubmit(__('Save', MUHANDLE_PO), ['class' => 'button-primary'], '</div></fieldset></td></tr></tbody></table><p>', '</p>');
		?>
		<h1><?php _e('Must use plugins',MUHANDLE_PO) ?></h1>
		<h2 class="nav-tab-wrapper">
			<a class="nav-tab nav-tab-active" href=""><?php _e('Settings', MUHANDLE_PO) ?></a>
			<a class="nav-tab" href=""><?php _e('About', MUHANDLE_PO) ?></a>
	    </h2>
		<?php
		echo '<section>'.$oForm->getHTML(false) . '</section>';
		self::aboutPage();
		echo '</div>';
	}
	public function aboutPage() {
		?>
		<section>
			<h2 class="title"><?php printf(__('Thank you for using \'%s\'', MUHANDLE_PO), MUHANDLE_NAME) ?></h2>
			<h3><?php _e('Plugin features', MUHANDLE_PO); ?></h3>
			<ul>
				<li><?php _e('Permission management',MUHANDLE_PO); ?></li>
				<li><?php _e('Restrict plugin updates und deactivation',MUHANDLE_PO); ?></li>
				<li><?php _e('International',MUHANDLE_PO); ?></li>
			</ul>
			<h3><?php _e('Author', MUHANDLE_PO); ?></h3>
			<div class="logo-box clearfix">
				<img src="<?php echo plugins_url(MUHANDLE_IMAGE_DIR_REL . DIRECTORY_SEPARATOR . 'logo-aohipa.png', dirname(__FILE__)) ?>" alt="aohipa GmbH" width="75">
				<div><strong>aohipa GmbH</strong></div>
				<div><a href="http://www.aohipa.com" title="aohipa GmbH" target="_blank">www.aohipa.com</a></div>
			</div>
		</section>
		<?php
	}
}