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

$workflowID = $this->escape($this->state->get('filter.workflow_id'));

?>
<?php foreach ($this->states as $i => $item):
	$link = JRoute::_('index.php?option=com_workflow&task=state.edit&id=' . $item->id . '&workflow_id=' . $workflowID . '&extension=' . $this->extension);
	?>
	<tr class="row<?php echo $i % 2; ?>" data-dragable-group="<?php echo $item->id; ?>">
		<td class="order nowrap text-center hidden-sm-down">
			<?php echo JHtml::_('grid.id', $i, $item->id); ?>
		</td>
		<td class="text-center">
			<div class="btn-group">
				<?php echo JHtml::_('jgrid.published', $item->published, $i, 'states.', true); ?>
			</div>
		</td>
		<td class="text-center hidden-sm-down">
			<?php echo JHtml::_('jgrid.isdefault', $item->default, $i, 'states.', true); ?>
		</td>
		<td>
			<a href="<?php echo $link ?>"><?php echo $item->title; ?></a>
		</td>
		<td class="text-center">
			<?php echo JText::_($item->condition); ?>
		</td>
		<td class="text-right">
			<?php echo $item->id; ?>
		</td>
	</tr>
<?php endforeach ?>
