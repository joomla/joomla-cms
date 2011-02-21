<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	templates.hathor
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

defined('_JEXEC') or die;
?>
	<table class="adminlist">
		<thead>
		<tr>
			<th class="left">
				<?php echo JText::_('COM_MENUS_HEADING_ASSIGN_MODULE');?>
			</th>
			<th>
				<?php echo JText::_('COM_MENUS_HEADING_DISPLAY');?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($this->modules as $i => &$module) : ?>
			<tr class="row<?php echo $i % 2;?>">
				<td>
					<?php $link = 'index.php?option=com_modules&client_id=0&task=module.edit&id='. $module->id.'&tmpl=component&view=module&layout=modal' ; ?>
					<a class="modal" href="<?php echo $link;?>" rel="{handler: 'iframe', size: {x: 900, y: 550}}" title="<?php echo JText::_('COM_MENUS_EDIT_MODULE_SETTINGS');?>">
						<?php echo JText::sprintf('COM_MENUS_MODULE_ACCESS_POSITION', $this->escape($module->title), $this->escape($module->access_title), $this->escape($module->position)); ?></a>
				</td>
				<td class="center">
				<?php if (is_null($module->menuid)) : ?>
					<?php echo JText::_('JNO'); ?>
				<?php elseif ($module->menuid != 0) : ?>
					<?php echo JText::_('JYES'); ?>
				<?php else : ?>
					<?php echo JText::_('JALL'); ?>
				<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
