<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.urlinstaller
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');

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
			JoomlaInstaller.showLoading();
			form.installtype.value = "url"
			form.submit();
		}
	};
');
?>
<legend><?php echo JText::_('PLG_INSTALLER_URLINSTALLER_TEXT'); ?></legend>
<div class="control-group">
	<label for="install_url" class="control-label">
		<?php echo JText::_('PLG_INSTALLER_URLINSTALLER_TEXT'); ?>
	</label>
	<div class="controls">
		<input type="text" id="install_url" name="install_url" class="form-control" placeholder="https://">
	</div>
</div>
<div class="control-group">
	<div class="controls">
		<input type="button" class="btn btn-primary" id="installbutton_url"
			value="<?php echo JText::_('PLG_INSTALLER_URLINSTALLER_BUTTON'); ?>" onclick="Joomla.submitbuttonurl()">
	</div>
</div>
