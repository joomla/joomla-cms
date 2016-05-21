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
		echo JHtml::_('bootstrap.addTab', 'myTab', 'package', JText::_('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_PACKAGE_FILE'));
		?>
		<fieldset class="uploadform">
			<legend><?php echo JText::_('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_INSTALL_JOOMLA_EXTENSION'); ?></legend>
			<div class="control-group">
				<label for="install_package" class="control-label"><?php echo JText::_('PLG_INSTALLER_PACKAGEINSTALLER_EXTENSION_PACKAGE_FILE'); ?></label>
				<div class="controls">
					<input class="input_box" id="install_package" name="install_package" type="file" size="57" />
				</div>
			</div>
			<div class="form-actions">
				<button class="btn btn-primary" type="button" id="installbutton_package" onclick="Joomla.submitbuttonpackage()">
					<?php echo JText::_('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_AND_INSTALL'); ?></button>
			</div>
		</fieldset>

		<?php
		echo JHtml::_('bootstrap.endTab');

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

		return true;
	}
}
