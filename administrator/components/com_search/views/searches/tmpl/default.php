<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_search
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$canDo		= SearchHelper::getActions();
?>
<form action="<?php echo JRoute::_('index.php?option=com_search&view=searches'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="filter-bar" class="btn-toolbar">
		<div class="filter-search btn-group pull-left">
			<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('COM_SEARCH_SEARCH_IN_PHRASE'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_SEARCH_SEARCH_IN_PHRASE'); ?>" />
		</div>
		<div class="filter-search btn-group pull-left">
			<button class="btn" rel="tooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
			<button class="btn" rel="tooltip" type="button" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
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
		<span class="enabled"><?php echo JText::_('COM_SEARCH_LOGGING_ENABLED'); ?></span>
	</div>
	<?php else : ?>
	<div class="alert alert-error">
		<a class="close" data-dismiss="alert">×</a>
		<span class="disabled"><?php echo JText::_('COM_SEARCH_LOGGING_DISABLED'); ?></span>
	</div>
	<?php endif; ?>
	<table class="table table-striped">
		<thead>
			<tr>
				<th width="20">
					<?php echo JText::_('JGRID_HEADING_ROW_NUMBER'); ?>
				</th>
				<th class="title">
					<?php echo JHtml::_('grid.sort', 'COM_SEARCH_HEADING_PHRASE', 'a.search_term', $listDirn, $listOrder); ?>
				</th>
				<th width="15%" class="center">
					<?php echo JHtml::_('grid.sort', 'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder); ?>
				</th>
				<th width="15%" class="center">
					<?php echo JText::_('COM_SEARCH_HEADING_RESULTS'); ?>
				</th>
				<th width="30%">
					&#160;
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="11">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach ($this->items as $i => $item) : ?>
			<tr class="row<?php echo $i % 2; ?>">
					<td class="right">
						<?php echo $i + 1 + $this->pagination->limitstart; ?>
					</td>
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
					<td>
						&#160;
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>


	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
