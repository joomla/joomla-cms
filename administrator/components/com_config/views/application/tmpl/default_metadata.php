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
<div class="width-100">
<fieldset class="adminform long">
	<legend><?php echo JText::_('COM_CONFIG_METADATA_SETTINGS'); ?></legend>
		<ul class="adminformlist">
			<?php
			foreach ($this->form->getFieldset('metadata') as $field):
			?>
					<li><?php echo $field->label; ?>
					<?php echo $field->input; ?></li>
			<?php
			endforeach;
			?>
			</ul>
</fieldset>
</div>
