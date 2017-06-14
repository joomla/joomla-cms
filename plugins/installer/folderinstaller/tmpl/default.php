<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.folderinstaller
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');

$app = JFactory::getApplication('administrator');
?>

<legend><?php echo JText::_('PLG_INSTALLER_FOLDERINSTALLER_TEXT'); ?></legend>
<hr>
<div class="control-group">
	<label for="install_directory" class="control-label">
		<?php echo JText::_('PLG_INSTALLER_FOLDERINSTALLER_TEXT'); ?>
	</label>
	<div class="controls">
		<input type="text" id="install_directory" name="install_directory" class="form-control input-xlarge"
			value="<?php echo $app->input->get('install_directory', $app->get('tmp_path')); ?>">
	</div>
</div>
<hr>
<div class="control-group">
	<div class="controls">
		<button type="button" class="btn btn-primary" id="installbutton_directory" onclick="Joomla.submitbuttonfolder()">
			<?php echo JText::_('PLG_INSTALLER_FOLDERINSTALLER_BUTTON'); ?>
		</button>
	</div>
</div>
