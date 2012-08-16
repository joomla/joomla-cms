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
<fieldset class="form-horizontal">
	<legend><?php echo JText::_('COM_CONFIG_CACHE_SETTINGS'); ?></legend>
			<?php
			foreach ($this->form->getFieldset('cache') as $field):
			?>
				<div class="control-group">
					<div class="control-label"><?php echo $field->label; ?></div>
					<div class="controls"><?php echo $field->input; ?></div>
				</div>
			<?php
			endforeach;
			?>
		<?php if (isset($this->data['cache_handler']) && $this->data['cache_handler'] == 'memcache' || $this->data['session_handler'] == 'memcache') : ?>

					<?php
			foreach ($this->form->getFieldset('memcache') as $mfield):
			?>
				<div class="control-group">
					<div class="control-label"><?php echo $mfield->label; ?></div>
					<div class="controls"><?php echo $mfield->input; ?></div>
				</div>
			<?php
			endforeach;
			?>
		<?php endif; ?>
</fieldset>
