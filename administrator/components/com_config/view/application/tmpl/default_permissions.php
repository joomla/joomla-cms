<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<fieldset class="form-vertical">
	<legend><?php echo JText::_('COM_CONFIG_PERMISSION_SETTINGS'); ?></legend>
	<?php foreach ($this->form->getFieldset('permissions') as $field): ?>
		<div class="control-group">
			<div class="controls"><?php echo $field->input; ?></div>
		</div>
	<?php endforeach; ?>
</fieldset>
