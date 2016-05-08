<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.Urlinstaller
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');

/**
 * UrlFolderInstaller Plugin.
 *
 * @since  3.6.0
 */
class PlgInstallerUrlInstaller extends JPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  3.6.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Textfield or Form of the Plugin.
	 *
	 * @return  void
	 *
	 * @since   3.6.0
	 */
	public function onInstallerAddInstallationTab()
	{
		echo JHtml::_('bootstrap.addTab', 'myTab', 'url', JText::_('PLG_INSTALLER_URLINSTALLER_INSTALLALL_TEXT', true));
		?>
		<div class="clr"></div>
		<fieldset class="uploadform">
			<legend><?php echo JText::_('PLG_INSTALLER_URLINSTALLER_INSTALLALL_TEXT'); ?></legend>
			<div class="control-group">
				<label for="install_url"
				       class="control-label"><?php echo JText::_('PLG_INSTALLER_URLINSTALLER_INSTALLALL_TEXT'); ?></label>
				<div class="controls">
					<input type="text" id="install_url" name="install_url" class="span5 input_box" size="70" value=""/>
				</div>
			</div>
			<div class="form-actions">
				<input type="button" class="btn btn-primary"
				       value="<?php echo JText::_('PLG_INSTALLER_URLINSTALLER_INSTALLALL_BUTTON'); ?>"
				       onclick="Joomla.submitbuttonurl()"
				/>
			</div>

			<input type="hidden" name="installtype" value="url"/>
		</fieldset>

		<?php
		echo JHtml::_('bootstrap.endTab');

		JFactory::getDocument()->addScriptDeclaration('
			Joomla.submitbuttonurl = function()
			{
				var form = document.getElementById("adminForm");
		
				// do field validation 
				if (form.install_url.value == "")
				{
					alert("' . JText::_('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_PACKAGE') . '");
				}
				// test if it is an https url or http url 
				else if ((form.install_url.value.startsWith("http") == true) || (form.install_url.value.startsWith("https") == true))
				{
					jQuery("#loading").css("display", "block");
					form.submit();
				}
			};
		');
	}
}
