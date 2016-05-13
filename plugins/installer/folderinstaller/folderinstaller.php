<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.FolderInstaller
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');

/**
 * FolderInstaller Plugin.
 *
 * @since  3.6.0
 */
class PlgInstallerFolderInstaller extends JPlugin
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
		$app = JFactory::getApplication('administrator');

		$tab            = array();
		$tab['name']    = 'folder';
		$tab['label']   = JText::_('PLG_INSTALLER_FOLDERINSTALLER_TEXT');
		$tab['content'] = '<legend>' . JText::_('PLG_INSTALLER_FOLDERINSTALLER_TEXT') . '</legend>'
			. '<div class="control-group">'
				. '<label for="install_directory" class="control-label">' . JText::_('PLG_INSTALLER_FOLDERINSTALLER_TEXT') . '</label>'
				. '<div class="controls">'
					. '<input type="text" id="install_directory" name="install_directory" class="span5 input_box" size="70" value="'
						. $app->input->get('install_directory', $app->get('tmp_path')) . '" />'
				. '</div>'
			. '</div>'
			. '<div class="form-actions">'
				. '<input type="button" class="btn btn-primary" id="installbutton_directory"
					value="' . JText::_('PLG_INSTALLER_FOLDERINSTALLER_BUTTON') . '" onclick="Joomla.submitbuttonfolder()" />'
			. '</div>';

		JFactory::getDocument()->addScriptDeclaration('
			Joomla.submitbuttonfolder = function()
			{
				var form = document.getElementById("adminForm");
		
				// do field validation 
				if (form.install_directory.value == "")
				{
					alert("' . JText::_('PLG_INSTALLER_FOLDERINSTALLER_NO_INSTALL_PATH') . '");
				}
				else
				{
					jQuery("#loading").css("display", "block");
					form.installtype.value = "folder"
					form.submit();
				}
			};
		');

		return $tab;
	}
}
