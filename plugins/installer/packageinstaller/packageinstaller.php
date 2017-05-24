<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.packageInstaller
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * PackageInstaller Plugin.
 *
 * @since  3.6.0
 */
class PlgInstallerPackageInstaller extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.6.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Textfield or Form of the Plugin.
	 *
	 * @return  array  Returns an array with the tab information
	 *
	 * @since   3.6.0
	 */
	public function onInstallerAddInstallationTab()
	{
		$tab            = array();
		$tab['name']    = 'package';
		$tab['label']   = JText::_('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_PACKAGE_FILE');

		// Render the input
		ob_start();
		include JPluginHelper::getLayoutPath('installer', 'packageinstaller');
		$tab['content'] = ob_get_clean();

		return $tab;
	}
}
