<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.packageinstaller
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

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

// Read INI settings which affects upload size limits
$ini_memory_limit          = JHtml::_('number.bytes', ini_get('memory_limit'));
$ini_memory_limit_b        = JHtml::_('number.bytes', $ini_memory_limit, '');
$ini_post_max_size         = JHtml::_('number.bytes', ini_get('post_max_size'));
$ini_post_max_size_b       = JHtml::_('number.bytes', $ini_post_max_size, '');
$ini_upload_max_filesize   = JHtml::_('number.bytes', ini_get('upload_max_filesize'));
$ini_upload_max_filesize_b = JHtml::_('number.bytes', $ini_upload_max_filesize, '');

$max_upload_size_b = min($ini_memory_limit_b, $ini_post_max_size_b, $ini_upload_max_filesize_b);
$max_upload_size   = JHtml::_('number.bytes', $max_upload_size_b);
?>
<legend><?php echo JText::_('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_INSTALL_JOOMLA_EXTENSION'); ?></legend>
<div class="control-group">
	<label for="install_package" class="control-label"><?php echo JText::_('PLG_INSTALLER_PACKAGEINSTALLER_EXTENSION_PACKAGE_FILE'); ?></label>
	<div class="controls">
		<input class="input_box" id="install_package" name="install_package" type="file" size="57" /><br>
		<?php echo JText::sprintf('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_EFFECTIVE_SIZE_LIMIT', $max_upload_size); ?>
	</div>
</div>
<div class="form-actions">
	<button class="btn btn-primary" type="button" id="installbutton_package" onclick="Joomla.submitbuttonpackage()">
		<?php echo JText::_('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_AND_INSTALL'); ?>
	</button>
</div>
