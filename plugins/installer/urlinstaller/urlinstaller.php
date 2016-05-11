<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.Urlinstaller
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
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
		echo JHtml::_('bootstrap.addTab', 'myTab', 'url', JText::_('PLG_INSTALLER_URLINSTALLER_TEXT'));
		?>
		<div class="clr"></div>
		<fieldset class="uploadform">
			<legend><?php echo JText::_('PLG_INSTALLER_URLINSTALLER_TEXT'); ?></legend>
			<div class="control-group">
				<label for="install_url"
				       class="control-label"><?php echo JText::_('PLG_INSTALLER_URLINSTALLER_TEXT'); ?></label>
				<div class="controls">
					<input type="text" id="install_url" name="install_url" class="span5 input_box" size="70" placeholder="https://"/>
				</div>
			</div>
			<div class="form-actions">
				<input type="button" class="btn btn-primary" id="installbutton_url"
				       value="<?php echo JText::_('PLG_INSTALLER_URLINSTALLER_BUTTON'); ?>"
				       onclick="Joomla.submitbuttonurl()"
				/>
			</div>
		</fieldset>

		<?php
		echo JHtml::_('bootstrap.endTab');

		JFactory::getDocument()->addScriptDeclaration('
			Joomla.submitbuttonurl = function()
			{
				var form = document.getElementById("adminForm");
		
				// do field validation 
				if (form.install_url.value == "" || form.install_url.value == "http://" || form.install_url.value == "https://") {
		            alert("' . JText::_('PLG_INSTALLER_URLINSTALLER_NO_URL', true) . '");
		        }
		        else
				{
					jQuery("#loading").css("display", "block");
					form.installtype.value = "url"
					form.submit();
				}
			};
		');

		return true;
	}
}
