<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<fieldset class="adminform" title="<?php echo JText::_('COM_INSTALLER_MSG_DESCFTPTITLE'); ?>">
	<legend><?php echo JText::_('COM_INSTALLER_MSG_DESCFTPTITLE'); ?></legend>

	<?php echo JText::_('COM_INSTALLER_MSG_DESCFTP'); ?>

	<?php if ($this->ftp instanceof Exception) : ?>
		<p><?php echo JText::_($this->ftp->getMessage()); ?></p>
	<?php endif; ?>

	<ul class="adminformlist">
		<li><label for="username"><?php echo JText::_('JGLOBAL_USERNAME'); ?></label>
		<input type="text" id="username" name="username" class="inputbox" value="" /></li>

		<li><label for="password"><?php echo JText::_('JGLOBAL_PASSWORD'); ?></label>
		<input type="password" id="password" name="password" class="input_box" value="" /></li>
	</ul>

</fieldset>
