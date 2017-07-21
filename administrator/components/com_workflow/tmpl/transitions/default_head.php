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

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<tr>
	<th style="width:1%" class="nowrap text-center hidden-sm-down">
		<?php echo JHtml::_('grid.checkall'); ?>
	</th>
	<th style="width:1%" class="nowrap text-center hidden-sm-down">
		<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'published', $listDirn, $listOrder); ?>
	</th>
	<th style="width:10%" class="nowrap hidden-sm-down">
		<?php echo JHtml::_('searchtools.sort', 'COM_WORKFLOW_TITLE', 'title', $listDirn, $listOrder); ?>
	</th>
	<th style="width:10%" class="nowrap text-center hidden-sm-down">
		<?php echo JHtml::_('searchtools.sort', 'COM_WORKFLOW_FROM_STATE', 'from_state', $listDirn, $listOrder); ?>
	</th>
	<th style="width:10%" class="nowrap text-center hidden-sm-down">
		<?php echo JHtml::_('searchtools.sort', 'COM_WORKFLOW_TO_STATE', 'to_state', $listDirn, $listOrder); ?>
	</th>
	<th style="width:10%" class="nowrap text-right hidden-sm-down">
		<?php echo JHtml::_('searchtools.sort', 'COM_WORKFLOW_ID', 'id', $listDirn, $listOrder); ?>
	</th>
</tr>
