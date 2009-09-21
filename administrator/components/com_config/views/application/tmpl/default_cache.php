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

<fieldset class="adminform">
	<legend><?php echo JText::_('Cache Settings'); ?></legend>
			<?php
			foreach ($this->form->getFields('cache') as $field):
			?>
					<?php echo $field->label; ?>
					<?php echo $field->input; ?>
			<?php
			endforeach;
			?>
		<?php if ($this->data['cache_handler'] == 'memcache' || $this->data['session_handler'] == 'memcache') : ?>
		
				<?php echo JText::_('Memcache Persistent'); ?>
			
				<?php echo $lists['memcache_persist']; ?>
			
				<?php echo JText::_('Memcache Compression'); ?>
			
				<?php echo $lists['memcache_compress']; ?>
		
		
				<?php echo JText::_('Memcache Server'); ?>
			
				<?php echo JText::_('Host'); ?>:
				<input class="text_area" type="text" name="memcache_settings[servers][0][host]" size="25" value="<?php echo @$this->data->memcache_settings['servers'][0]['host']; ?>" />

				<?php echo JText::_('Port'); ?>:
				<input class="text_area" type="text" name="memcache_settings[servers][0][port]" size="6" value="<?php echo @$this->data->memcache_settings['servers'][0]['port']; ?>" />
			
		<?php endif; ?>
	
</fieldset>
</div>
