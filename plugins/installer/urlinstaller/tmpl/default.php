<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.urlinstaller
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');

JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbuttonurl = function()
	{
		var form = document.getElementById("adminForm");

		JoomlaInstaller.showLoading();
		form.installtype.value = "url"
		form.submit();
	};
');
?>
<legend><?php echo JText::_('PLG_INSTALLER_URLINSTALLER_TEXT'); ?></legend>
<div class="control-group">
	<label for="install_url" class="control-label"><?php echo JText::_('PLG_INSTALLER_URLINSTALLER_TEXT'); ?></label>
	<div class="controls">
		<input type="text" id="install_url" name="install_url" class="span5 input_box" size="70" placeholder="https://"/>
	</div>
</div>
<div class="form-actions">
	<input type="button" class="btn btn-primary" id="installbutton_url"
		value="<?php echo JText::_('PLG_INSTALLER_URLINSTALLER_BUTTON'); ?>" onclick="Joomla.submitbuttonurl()" />
</div>
