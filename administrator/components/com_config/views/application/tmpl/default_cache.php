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
<fieldset class="adminform">
	<legend><?php echo JText::_('Cache Settings'); ?></legend>
	<table class="admintable" cellspacing="1">
		<tbody>
			<?php
			foreach ($this->form->getFields('cache') as $field):
			?>
			<tr>
				<td width="185" class="key">
					<?php echo $field->label; ?>
				</td>
				<td>
					<?php echo $field->input; ?>
				</td>
			</tr>
			<?php
			endforeach;
			?>
		<?php if ($this->data['cache_handler'] == 'memcache' || $this->data['session_handler'] == 'memcache') : ?>
		<tr>
			<td class="key">
				<?php echo JText::_('Memcache Persistent'); ?>
			</td>
			<td>
				<?php echo $lists['memcache_persist']; ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('Memcache Compression'); ?>
			</td>
			<td>
				<?php echo $lists['memcache_compress']; ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('Memcache Server'); ?>
			</td>
			<td>
				<?php echo JText::_('Host'); ?>:
				<input class="text_area" type="text" name="memcache_settings[servers][0][host]" size="25" value="<?php echo @$this->data->memcache_settings['servers'][0]['host']; ?>" />
				<br /><br />
				<?php echo JText::_('Port'); ?>:
				<input class="text_area" type="text" name="memcache_settings[servers][0][port]" size="6" value="<?php echo @$this->data->memcache_settings['servers'][0]['port']; ?>" />
			</td>
		</tr>
		<?php endif; ?>
		</tbody>
	</table>
</fieldset>
