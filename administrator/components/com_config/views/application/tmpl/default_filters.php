<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_config
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;
?>
<div class="width-80">
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_CONFIG_TEXT_FILTER_SETTINGS'); ?></legend>
		<p><?php echo JText::_('COM_CONFIG_TEXT_FILTERS_DESC'); ?></p>
		<?php foreach ($this->form->getFieldset('filters') as $field): ?>
			<?php echo $field->label; ?>
			<div class="clr"> </div>
			<?php echo $field->input; ?>
		<?php endforeach; ?>
	</fieldset>
</div>
