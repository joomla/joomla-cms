<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_search
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo JRoute::_('index.php?option=com_search&view=searches'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="filter-bar" class="btn-toolbar">
		<div class="filter-search btn-group pull-left">
			<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" class="hasTooltip" title="<?php echo JHtml::tooltipText('COM_SEARCH_SEARCH_IN_PHRASE'); ?>" />
		</div>
		<div class="filter-search btn-group pull-left">
			<button type="submit" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
			<button type="button" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
		</div>
		<div class="btn-group pull-right hidden-phone">
			<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
			<?php echo $this->pagination->getLimitBox(); ?>
		</div>
		<div class="filter-select btn-group pull-left">
			<span class="adminlist-searchstatus">
			<?php if ($this->state->get('filter.results')) : ?>
				<a class="btn" href="<?php echo JRoute::_('index.php?option=com_search&filter_results=0');?>">
					<i class="icon-zoom-out"></i> <?php echo JText::_('COM_SEARCH_HIDE_SEARCH_RESULTS'); ?></a>
			<?php else : ?>
				<a class="btn" href="<?php echo JRoute::_('index.php?option=com_search&filter_results=1');?>">
					<i class="icon-zoom-in"></i> <?php echo JText::_('COM_SEARCH_SHOW_SEARCH_RESULTS'); ?></a>
			<?php endif; ?>
			</span>
		</div>
	</div>
	<div class="clearfix"> </div>
	<?php if ($this->enabled) : ?>
	<div class="alert alert-info">
		<a class="close" data-dismiss="alert">×</a>
		<?php echo JText::_('COM_SEARCH_LOGGING_ENABLED'); ?>
	</div>
	<?php else : ?>
	<div class="alert alert-error">
		<a class="close" data-dismiss="alert">×</a>
		<?php echo JText::_('COM_SEARCH_LOGGING_DISABLED'); ?>
	</div>
	<?php endif; ?>
	<?php if (empty($this->items)) : ?>
		<div class="alert alert-no-items">
			<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
		</div>
	<?php else : ?>
		<table class="table table-striped">
			<thead>
				<tr>
					<th class="title">
						<?php echo JHtml::_('grid.sort', 'COM_SEARCH_HEADING_PHRASE', 'a.search_term', $listDirn, $listOrder); ?>
					</th>
					<th width="15%" class="center">
						<?php echo JHtml::_('grid.sort', 'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder); ?>
					</th>
					<th width="15%" class="center">
						<?php echo JText::_('COM_SEARCH_HEADING_RESULTS'); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="3">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
		<?php foreach ($this->items as $i => $item) : ?>
			<tr class="row<?php echo $i % 2; ?>">
					<td>
						<?php echo $this->escape($item->search_term); ?>
					</td>
					<td class="center">
						<?php echo (int) $item->hits; ?>
					</td>
					<td class="center">
					<?php if ($this->state->get('filter.results')) : ?>
						<?php echo (int) $item->returns; ?>
					<?php else: ?>
						<?php echo JText::_('COM_SEARCH_NO_RESULTS'); ?>
					<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
