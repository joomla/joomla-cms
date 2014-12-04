<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.core');
?>

<div class="control-group">
	<button type="button" class="btn btn-default" id="showmods" onclick="jQuery('.table tr.no').toggle();"><?php echo JText::_('COM_MENUS_ITEM_FIELD_HIDE_UNASSIGNED');?></button>
</div>
	<table class="table table-striped">
		<thead>
		<tr>
			<th class="left">
				<?php echo JText::_('COM_MENUS_HEADING_ASSIGN_MODULE');?>
			</th>
			<th>

			</th>
			<th>
				<?php echo JText::_('COM_MENUS_HEADING_POSITION');?>
			</th>
			<th>
				<?php echo JText::_('COM_MENUS_HEADING_DISPLAY');?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($this->modules as $i => &$module) : ?>
 			<?php if (is_null($module->menuid)) : ?>
				<?php if (!$module->except || $module->menuid < 0) : ?>
					<tr class="no row<?php echo $i % 2;?>" id="tr-<?php echo $module->id; ?>">
				<?php else : ?>
			<tr class="row<?php echo $i % 2;?>" id="tr-<?php echo $module->id; ?>">
				<?php endif; ?>
			<?php endif; ?>
				<td id="<?php echo $module->id; ?>">
					<?php $link = 'index.php?option=com_modules&amp;client_id=0&amp;task=module.edit&amp;id=' . $module->id . '&amp;tmpl=component&amp;view=module&amp;layout=modal'; ?>
					<a class="modal" href="<?php echo $link;?>" rel="{handler: 'iframe', size: {x: 900, y: 550}}" title="<?php echo JText::_('COM_MENUS_EDIT_MODULE_SETTINGS');?>" id="title-<?php echo $module->id; ?>">
						<?php echo JText::_($this->escape($module->title)); ?></a>
				</td>
				<td id="position-<?php echo $module->id; ?>">
					<?php echo JText::_($this->escape($module->access_title)); ?>
				</td>
				<td id="position-<?php echo $module->id; ?>">
					<?php echo JText::_($this->escape($module->position)); ?>
				</td>
				<td id="menus-<?php echo $module->id; ?>">
					<?php if (is_null($module->menuid)) : ?>
						<?php if ($module->except):?>
							<span class="label label-success">
								<?php echo JText::_('JYES'); ?>
							</span>
						<?php else : ?>
							<span class="label label-important">
								<?php echo JText::_('JNO'); ?>
							</span>
						<?php endif;?>
					<?php elseif ($module->menuid > 0) : ?>
						<span class="label label-success">
							<?php echo JText::_('JYES'); ?>
						</span>
					<?php elseif ($module->menuid < 0) : ?>
						<span class="label label-important">
							<?php echo JText::_('JNO'); ?>
						</span>
					<?php else : ?>
						<span class="label label-info">
							<?php echo JText::_('JALL'); ?>
						</span>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
