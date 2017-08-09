<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.urlinstaller
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>

<legend><?php echo JText::_('PLG_INSTALLER_URLINSTALLER_TEXT'); ?></legend>
<hr>
<div class="control-group">
	<label for="install_url" class="control-label">
		<?php echo JText::_('PLG_INSTALLER_URLINSTALLER_TEXT'); ?>
	</label>
	<div class="controls">
		<input type="text" id="install_url" name="install_url" class="form-control input-xlarge" placeholder="https://">
	</div>
</div>
<div class="control-group">
	<label for="force_install" class="control-label" title="<?php echo \JText::_('PLG_INSTALLER_URLINSTALLER_FORCE_INSTALL_DETAILS') ?>">
		<?php echo \JText::_('PLG_INSTALLER_URLINSTALLER_FORCE_INSTALL'); ?>
	</label>
	<fieldset class="checkboxes">
		<input type="checkbox" id="force_install" name="force_install" value="1">
	</fieldset>
</div>
<hr>
<div class="control-group">
	<div class="controls">
		<button type="button" class="btn btn-primary" id="installbutton_url" onclick="Joomla.submitbuttonurl()">
			<?php echo JText::_('PLG_INSTALLER_URLINSTALLER_BUTTON'); ?>
		</button>
	</div>
</div>
