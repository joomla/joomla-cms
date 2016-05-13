<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.packageInstaller
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');

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
		$tab['content'] = '<legend>' . JText::_('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_INSTALL_JOOMLA_EXTENSION') . '</legend>'
			. '<div class="control-group">'
				. '<label for="install_package" class="control-label">' . JText::_('PLG_INSTALLER_PACKAGEINSTALLER_EXTENSION_PACKAGE_FILE') . '</label>'
				. '<div class="controls">'
					. '<input class="input_box" id="install_package" name="install_package" type="file" size="57" />'
				. '</div>'
			. '</div>'
			. '<div class="form-actions">'
				. '<button class="btn btn-primary" type="button" id="installbutton_package" onclick="Joomla.submitbuttonpackage()">'
					. JText::_('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_AND_INSTALL') . '</button>'
			. '</div>';

		JFactory::getDocument()->addScriptDeclaration('
			Joomla.submitbuttonpackage = function()
			{
				var form = document.getElementById("adminForm");
		
				// do field validation 
				if (form.install_package.value == "")
				{
					alert("' . JText::_('PLG_INSTALLER_PACKAGEINSTALLER_NO_PACKAGE') . '");
				}
				else
				{
					jQuery("#loading").css("display", "block");
					form.installtype.value = "upload"
					form.submit();
				}
			};
		');

		return $tab;
	}
}
