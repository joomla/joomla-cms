<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<fieldset title="<?php echo JText::_('COM_CONFIG_FTP_DETAILS'); ?>" class="form-horizontal">
	<legend><?php echo JText::_('COM_CONFIG_FTP_DETAILS'); ?></legend>
	<?php echo JText::_('COM_CONFIG_FTP_DETAILS_TIP'); ?>
	<?php if ($this->ftp instanceof Exception) : ?>
		<p><?php echo JText::_($this->ftp->message); ?></p>
	<?php endif; ?>
	<div class="control-group">
		<div class="control-label"><label for="username"><?php echo JText::_('JGLOBAL_USERNAME'); ?></label></div>
		<div class="controls">
			<input type="text" id="username" name="username" class="input_box" size="70" value="" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label"><?php echo JText::_('JGLOBAL_PASSWORD'); ?></div>
		<div class="controls">
			<input type="password" id="password" name="password" class="input_box" size="70" value="" />
		</div>
	</div>
</fieldset>
