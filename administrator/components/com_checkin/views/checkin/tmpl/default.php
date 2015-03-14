<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_checkin
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo JRoute::_('index.php?option=com_checkin'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" class="hasTooltip" title="<?php echo JHtml::tooltipText('COM_CHECKIN_FILTER_SEARCH_DESC'); ?>" />
			</div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>">
					<i class="icon-search"></i></button>
				<button type="button" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();">
					<i class="icon-remove"></i></button>
			</div>
		</div>
		<div class="clearfix"></div>
		<table id="global-checkin" class="table table-striped">
			<thead>
				<tr>
					<th width="1%">
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
					<th class="left"><?php echo JHtml::_('grid.sort', 'COM_CHECKIN_DATABASE_TABLE', 'table', $listDirn, $listOrder); ?></th>
					<th><?php echo JHtml::_('grid.sort', 'COM_CHECKIN_ITEMS_TO_CHECK_IN', 'count', $listDirn, $listOrder); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php $i = 0; ?>
				<?php foreach ($this->items as $table => $count): ?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="center"><?php echo JHtml::_('grid.id', $i, $table); ?></td>
						<td>
							<label for="cb<?php echo $i ?>">
								<?php echo JText::sprintf('COM_CHECKIN_TABLE', $table); ?>
							</label>
						</td>
						<td width="200" class="center"><span class="label label-info"><?php echo $count; ?></span></td>
					</tr>
					<?php $i++; ?>
				<?php endforeach; ?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="15">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
		</table>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
