<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_config
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;
?>
<div class="width-100">
<fieldset title="<?php echo JText::_('DESCFTPTITLE'); ?>" class="adminform">
	<legend><?php echo JText::_('DESCFTPTITLE'); ?></legend>

	<?php echo JText::_('DESCFTP'); ?>

	<?php if (JError::isError($this->ftp)): ?>
		<p><?php echo JText::_($this->ftp->message); ?></p>
	<?php endif; ?>

					<label for="username"><?php echo JText::_('Username'); ?>:</label>
				
					<input type="text" id="username" name="username" class="input_box" size="70" value="" />
			
					<label for="password"><?php echo JText::_('Password'); ?>:</label>
				
					<input type="password" id="password" name="password" class="input_box" size="70" value="" />
</fieldset>
</div>