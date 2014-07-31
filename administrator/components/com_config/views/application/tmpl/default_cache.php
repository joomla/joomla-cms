<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_config
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;
?>
<div class="width-100">

<fieldset class="adminform">
	<legend><?php echo JText::_('COM_CONFIG_CACHE_SETTINGS'); ?></legend>
	<ul class="adminformlist">
			<?php
			foreach ($this->form->getFieldset('cache') as $field):
			?>
					<li><?php echo $field->label; ?>
					<?php echo $field->input; ?></li>
			<?php
			endforeach;
			?>
		<?php if (isset($this->data['cache_handler']) && $this->data['cache_handler'] == 'memcache' || $this->data['session_handler'] == 'memcache') : ?>

					<?php
			foreach ($this->form->getFieldset('memcache') as $mfield):
			?>
					<li><?php echo $mfield->label; ?>
					<?php echo $mfield->input; ?></li>
			<?php
			endforeach;
			?>
		<?php endif; ?>
		</ul>

</fieldset>
</div>
