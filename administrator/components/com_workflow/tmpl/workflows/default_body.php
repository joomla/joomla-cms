<?php
/**
 * Items Model for a Workflow Component.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0
 */
defined('_JEXEC') or die;

$extension = $this->escape($this->state->get('filter.extension'));
?>
<?php foreach ($this->workflows as $i => $item):
	$states = JRoute::_('index.php?option=com_workflow&view=states&workflow_id=' . $item->id . '&extension=' . $extension);
	$transitions = JRoute::_('index.php?option=com_workflow&view=transitions&workflow_id=' . $item->id . '&extension=' . $extension);
	$edit = JRoute::_('index.php?option=com_workflow&task=workflow.edit&id=' . $item->id);
	?>
	<tr class="row<?php echo $i % 2; ?>" data-dragable-group="<?php echo $item->id; ?>">
		<td class="order nowrap text-center hidden-sm-down">
			<?php echo JHtml::_('grid.id', $i, $item->id); ?>
		</td>
		<td class="text-center">
			<div class="btn-group">
				<?php echo JHtml::_('jgrid.published', $item->published, $i, 'workflows.', true); ?>
			</div>
		</td>
		<td>
			<a href="<?php echo $edit; ?>"><?php echo $item->title; ?></a>
		</td>
		<td class="text-center">
			<a href="<?php echo $states; ?>"><?php echo \JText::_('COM_WORKFLOW_SHOW'); ?></a>
		</td>
		<td class="text-center">
			<a href="<?php echo $transitions; ?>"><?php echo \JText::_('COM_WORKFLOW_SHOW'); ?></a>
		</td>
		<td class="text-center hidden-sm-down">
			<?php echo JHtml::_('jgrid.isdefault', $item->default, $i, 'workflows.', true); ?>
		</td>
		<td class="text-center">
			<?php echo $item->created; ?>
		</td>
		<td class="text-center">
			<?php echo $item->name; ?>
		</td>
		<td class="text-right">
			<?php echo $item->id; ?>
		</td>
	</tr>
<?php endforeach ?>
