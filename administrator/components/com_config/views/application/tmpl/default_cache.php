<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_config
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;
?>
<div class="width-100">

<fieldset class="adminform">
	<legend><?php echo JText::_('COM_CONFIG_CACHE_SETTINGS'); ?></legend>
			<?php
			foreach ($this->form->getFieldset('cache') as $field):
			?>
					<?php echo $field->label; ?>
					<?php echo $field->input; ?>
			<?php
			endforeach;
			?>
		<?php if ($this->data['cache_handler'] == 'memcache' || $this->data['session_handler'] == 'memcache') : ?>

					<?php
			foreach ($this->form->getFieldset('memcache') as $mfield):
			?>
					<?php echo $mfield->label; ?>
					<?php echo $mfield->input; ?>
			<?php
			endforeach;
			?>
		<?php endif; ?>

</fieldset>
</div>
