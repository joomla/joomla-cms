<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.multiselect');
JHtml::_('behavior.modal', 'a.modal');
JHtml::_('formbehavior.chosen', 'select');

$user       = JFactory::getUser();
$userId     = $user->get('id');
$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
$sortFields = $this->getSortFields();

JFactory::getDocument()->addScriptDeclaration(
	'
	Joomla.orderTable = function()
	{
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != "' . $listOrder . '")
		{
		dirn = "asc";
		}
		else
		{
		dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, "");
	};

	Joomla.closeModalDialog = function()
	{
		window.jQuery("#modal-download").modal("hide");
	};'
);
?>
<form action="<?php echo JRoute::_('index.php?option=com_banners&view=tracks'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<label class="filter-hide-lbl" for="filter_begin"><?php echo JText::_('COM_BANNERS_BEGIN_LABEL'); ?></label>
				<?php echo JHtml::_('calendar', $this->state->get('filter.begin'), 'filter_begin', 'filter_begin', '%Y-%m-%d', array('size' => 10, 'onchange' => "this.form.fireEvent('submit');this.form.submit()")); ?>
			</div>
			<div class="filter-search btn-group pull-left">
				<label class="filter-hide-lbl" for="filter_end"><?php echo JText::_('COM_BANNERS_END_LABEL'); ?></label>
				<?php echo JHtml::_('calendar', $this->state->get('filter.end'), 'filter_end', 'filter_end', '%Y-%m-%d', array('size' => 10, 'onchange' => "this.form.fireEvent('submit');this.form.submit()")); ?>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<label for="directionTable" class="element-invisible"><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></label>
				<select name="directionTable" id="directionTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></option>
					<option value="asc" <?php echo $listDirn == 'asc' ? 'selected="selected"' : ''; ?>><?php echo JText::_('JGLOBAL_ORDER_ASCENDING'); ?></option>
					<option value="desc" <?php echo $listDirn == 'desc' ? 'selected="selected"' : ''; ?>><?php echo JText::_('JGLOBAL_ORDER_DESCENDING'); ?></option>
				</select>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<label for="sortTable" class="element-invisible"><?php echo JText::_('JGLOBAL_SORT_BY'); ?></label>
				<select name="sortTable" id="sortTable" class="input-medium" onchange="Joomla.orderTable()">
					<option value=""><?php echo JText::_('JGLOBAL_SORT_BY'); ?></option>
					<?php echo JHtml::_('select.options', $sortFields, 'value', 'text', $listOrder); ?>
				</select>
			</div>
		</div>
		<div class="clearfix"></div>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped">
				<thead>
					<tr>
						<th class="title">
							<?php echo JText::_('COM_BANNERS_HEADING_NAME'); ?>
						</th>
						<th width="20%" class="nowrap">
							<?php echo JText::_('COM_BANNERS_HEADING_CLIENT'); ?>
						</th>
						<th width="10%" class="nowrap hidden-phone">
							<?php echo JText::_('COM_BANNERS_HEADING_TYPE'); ?>
						</th>
						<th width="10%" class="nowrap hidden-phone">
							<?php echo JText::_('COM_BANNERS_HEADING_COUNT'); ?>
						</th>
						<th width="10%" class="nowrap hidden-phone">
							<?php echo JText::_('JDATE'); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="6">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
					<?php foreach ($this->items as $i => $item) : ?>
						<tr class="row<?php echo $i % 2; ?>">
							<td>
								<?php echo $item->name; ?>
								<div class="small">
									<?php echo $item->category_title; ?>
								</div>
							</td>
							<td>
								<?php echo $item->client_name; ?>
							</td>
							<td class="small hidden-phone">
								<?php echo $item->track_type == 1 ? JText::_('COM_BANNERS_IMPRESSION') : JText::_('COM_BANNERS_CLICK'); ?>
							</td>
							<td class="hidden-phone">
								<?php echo $item->count; ?>
							</td>
							<td class="hidden-phone">
								<?php echo JHtml::_('date', $item->track_date, JText::_('DATE_FORMAT_LC4') . ' H:i'); ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
