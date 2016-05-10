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
class PlgInstallerFolderInstaller  extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 *
	 * @since   3.6.0
	 */
	public function __construct(&$subject, $config = array())
	{
		$this->autoloadLanguage = true;

		parent::__construct($subject, $config);
	}

	/**
	 * Textfield or Form of the Plugin.
	 *
	 * @return  bool  Always returns true
	 *
	 * @since   3.6.0
	 */
	public function onInstallerAddInstallationTab()
	{
		$app = JFactory::getApplication('administrator');
		echo JHtml::_('bootstrap.addTab', 'myTab', 'folder', JText::_('PLG_INSTALLER_FOLDERINSTALLER_TEXT'));
		?>
		<div class="clr"></div>
		<fieldset class="uploadform">
			<legend><?php echo JText::_('PLG_INSTALLER_FOLDERINSTALLER_TEXT'); ?></legend>
			<div class="control-group">
				<label for="install_directory" class="control-label"><?php echo JText::_('PLG_INSTALLER_FOLDERINSTALLER_TEXT'); ?></label>
				<div class="controls">
					<input
						type="text"
						id="install_directory"
						name="install_directory"
						class="span5 input_box"
						size="70"
						value="<?php echo $app->input->get('install_directory', $app->get('tmp_path')); ?>" />
				</div>
			</div>
			<div class="form-actions">
				<input type="button" class="btn btn-primary" id="installbutton_directory"
					value="<?php echo JText::_('PLG_INSTALLER_FOLDERINSTALLER_BUTTON'); ?>" onclick="Joomla.submitbuttonfolder()"
				/>
			</div>
		</fieldset>

		<?php
		echo JHtml::_('bootstrap.endTab');

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

		return true;
	}
}
