<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.packageinstaller
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');

JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbuttonpackage = function()
	{
		var form = document.getElementById("adminForm");

		// do field validation 
		if (form.install_package.value == "")
		{
			alert("' . JText::_('PLG_INSTALLER_PACKAGEINSTALLER_NO_PACKAGE', true) . '");
		}
		else
		{
			var loading = document.getElementById("loading");
			if (loading)
			{
				loading.style.display = "block";
			}

			form.installtype.value = "upload"
			form.submit();
		}
	};
');
?>
<legend><?php echo JText::_('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_INSTALL_JOOMLA_EXTENSION'); ?></legend>
<hr>
<div class="control-group">
	<label for="install_package" class="control-label"><?php echo JText::_('PLG_INSTALLER_PACKAGEINSTALLER_EXTENSION_PACKAGE_FILE'); ?></label>
	<div class="controls">
		<input class="form-control-file" id="install_package" name="install_package" type="file">
		<?php $maxSize = JHtml::_('number.bytes', JUtility::getMaxUploadSize()); ?>
		<small class="form-text text-muted"><?php echo JText::sprintf('JGLOBAL_MAXIMUM_UPLOAD_SIZE_LIMIT', $maxSize); ?></small>
	</div>
</div>
<hr>
<div class="control-group">
	<div class="controls">
		<button class="btn btn-primary" type="button" id="installbutton_package" onclick="Joomla.submitbuttonpackage()">
			<?php echo JText::_('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_AND_INSTALL'); ?>
		</button>
	</div>
</div>
