<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
	<table class="adminlist">
		<thead>
		<tr>
			<th class="left">
				<?php echo JText::_('Menus_Heading_Assign_Module');?>
			</th>
			<th>
				<?php echo JText::_('Menus_Heading_Display');?>
			</th>
			<th>
				&nbsp;
			</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($this->modules as $i => &$module) : ?>
			<tr class="row<?php echo $i % 2;?>">
				<td>
					<?php echo JText::sprintf('Menus_Item_Module_Access_Position', $this->escape($module->title), $this->escape($module->access_title), $this->escape($module->position)); ?>
				</td>
				<td class="center">
				<?php if (is_null($module->menuid)) : ?>
					<?php echo JText::_('Menus_Module_Show_None'); ?>
				<?php elseif ($module->menuid != 0) : ?>
					<?php echo JText::_('Menus_Module_Show_Varies'); ?>
				<?php else : ?>
					<?php echo JText::_('Menus_Module_Show_All'); ?>
				<?php endif; ?>
				</td>
				<td class="center">
					<?php /* $link = 'index.php?option=com_modules&client=0&task=edit&cid[]='. $module->id.'&tmpl=component&layout=modal' ; */ ?>
					<?php /* $link = 'index.php?option=com_modules&client=0&task=edit&cid[]='. $module->id.'&tmpl=component&view=module&layout=modal' ; */ ?>
					<?php $link = 'index.php?option=com_modules&client_id=0&task=edit&id='. $module->id.'&tmpl=component&view=module&layout=modal' ; ?>
					<a class="modal" title="<?php echo JText::_('Edit'); ?>" href="<?php echo $link;?>" rel="{handler: 'iframe', size: {x: 500, y: 506}}">
						<?php echo JText::_('Edit_module_settings');?></a>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
