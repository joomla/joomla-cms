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
<p><?php echo JText::_('Menus_Item_Module_assignment_desc');?></p>
<?php if (!empty($this->modules)) : ?>
	<table class="adminlist">
		<thead>
			<th>
				<?php echo JText::_('Menus_Heading_Assign_Module');?>
			</th>
			<th width="10%">
				<?php echo JText::_('Menus_Heading_Assign_All');?>
			</th>
			<th width="10%">
				<?php echo JText::_('Menus_Heading_Assign_Show');?>
			</th>
			<th width="10%">
				<?php echo JText::_('Menus_Heading_Assign_Hide');?>
			</th>
			<th width="10%">
				<?php echo JText::_('Menus_Heading_Assign_Ignore');?>
			</th>
		</thead>
		<tbody>
		<?php foreach ($this->modules as $i => &$module) : ?>
			<tr class="row<?php echo $i % 2;?>">
				<td>
					<?php echo JText::sprintf('Menus_Item_Module_Access_Position', $this->escape($module->title), $this->escape($module->access_title), $this->escape($module->position));?>
				</td>
				<td align="center">
					<input type="radio" name="menuid[<?php echo $module->id;?>]" value="0" <?php echo is_numeric($module->menuid) && $module->menuid == 0 ? 'checked="checked" ' : '';?>/>
				</td>
				<td align="center">
					<input type="radio" name="menuid[<?php echo $module->id;?>]" value="1" <?php echo $module->menuid == $this->item->id ? 'checked="checked" ' : '';?>/>
				</td>
				<td align="center">
					<input type="radio" name="menuid[<?php echo $module->id;?>]" value="-1" <?php echo $module->menuid == -$this->item->id ? 'checked="checked" ' : '';?>/>
				</td>
				<td align="center">
					<input type="radio" name="menuid[<?php echo $module->id;?>]" value="" <?php echo !is_numeric($module->menuid) || (is_numeric($module->menuid) && $module->menuid != 0 && abs($module->menuid) != $this->item->id)? 'checked="checked" ' : '';?>/>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>
