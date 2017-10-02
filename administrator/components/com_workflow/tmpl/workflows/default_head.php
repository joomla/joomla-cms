<?php
/**
 * Items Model for a Workflow Component.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since  __DEPLOY_VERSION__
 */
defined('_JEXEC') or die;

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$orderingColumn = 'created';

if (strpos($listOrder, 'modified') !== false)
{
    $orderingColumn = 'modified';
}

?>
<tr>
	<th style="width:1%" class="nowrap text-center hidden-sm-down">
		<?php echo JHtml::_('grid.checkall'); ?>
	</th>
	<th style="width:1%" class="nowrap text-center hidden-sm-down">
		<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'condition', $listDirn, $listOrder); ?>
	</th>
	<th style="width:10%" class="nowrap hidden-sm-down">
		<?php echo JHtml::_('searchtools.sort', 'COM_WORKFLOW_TITLE', 'title', $listDirn, $listOrder); ?>
	</th>
	<th style="width:10%" class="nowrap text-center hidden-sm-down">
		<?php echo JText::_('COM_WORKFLOW_STATES'); ?>
	</th>
	<th style="width:10%" class="text-center nowrap hidden-sm-down">
		<?php echo JText::_('COM_WORKFLOW_DEFAULT'); ?>
	</th>
	<th style="width:3%" class="nowrap text-center hidden-sm-down">
		<span class="fa fa-circle-o text-warning hasTooltip" aria-hidden="true" title="<?php echo JText::_('COM_WORKFLOW_COUNT_STATES'); ?>">
			<span class="sr-only"><?php echo JText::_('COM_WORKFLOW_COUNT_STATES'); ?></span>
		</span>
	</th>
	<th style="width:3%" class="nowrap text-center hidden-sm-down">
		<span class="fa fa-arrows-h text-info hasTooltip" aria-hidden="true" title="<?php echo JText::_('COM_WORKFLOW_COUNT_TRANSITIONS'); ?>">
			<span class="sr-only"><?php echo JText::_('COM_WORKFLOW_COUNT_TRANSITIONS'); ?></span>
		</span>
	</th>
	<th style="width:10%" class="nowrap hidden-sm-down text-center">
		<?php echo JHtml::_('searchtools.sort', 'COM_WORKFLOW_DATE_' . strtoupper($orderingColumn), $orderingColumn, $listDirn, $listOrder); ?>
	</th>
	<th style="width:10%" class="nowrap text-center hidden-sm-down">
		<?php echo JHtml::_('searchtools.sort', 'COM_WORKFLOW_AUTHOR', 'created_by', $listDirn, $listOrder); ?>
	</th>
	<th style="width:10%" class="nowrap text-right hidden-sm-down">
		<?php echo JHtml::_('searchtools.sort', 'COM_WORKFLOW_ID', 'id', $listDirn, $listOrder); ?>
	</th>
</tr>
