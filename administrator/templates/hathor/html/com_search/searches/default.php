<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	Templates.hathor
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

// No direct access.
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
	<fieldset id="filter-bar">
	<legend class="element-invisible"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></legend>
		<div class="filter-search">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_SEARCH_SEARCH_IN_PHRASE'); ?>" />
			<button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>

		<div class="filter-select">
			<?php if ($this->enabled) : ?>
			<span class="enabled"><?php echo JText::_('COM_SEARCH_LOGGING_ENABLED'); ?></span>
			<?php else : ?>
			<span class="disabled"><?php echo JText::_('COM_SEARCH_LOGGING_DISABLED'); ?></span>
			<?php endif; ?>

			<span class="adminlist-searchstatus">
			<?php if ($this->state->get('filter.results')) : ?>
				<a href="<?php echo JRoute::_('index.php?option=com_search&filter_results=0');?>">
					<?php echo JText::_('COM_SEARCH_HIDE_SEARCH_RESULTS'); ?></a>
			<?php else : ?>
				<a href="<?php echo JRoute::_('index.php?option=com_search&filter_results=1');?>">
					<?php echo JText::_('COM_SEARCH_SHOW_SEARCH_RESULTS'); ?></a>
			<?php endif; ?>
			</span>
		</div>
	</fieldset>
	<div class="clr"> </div>

	<table class="adminlist">
		<thead>
			<tr>
				<th class="row-number-col">
					<?php echo JText::_('JGRID_HEADING_ROW_NUMBER'); ?>
				</th>
				<th class="title">
					<?php echo JHtml::_('grid.sort', 'COM_SEARCH_HEADING_PHRASE', 'a.search_term', $listDirn, $listOrder); ?>
				</th>
				<th class="hits-col">
					<?php echo JHtml::_('grid.sort', 'JGLOBAL_HITS', 'a.hits', $listDirn, $listOrder); ?>
				</th>
				<th class="width-15">
					<?php echo JText::_('COM_SEARCH_HEADING_RESULTS'); ?>
				</th>
				<th class="width-30">
					&#160;
				</th>
			</tr>
		</thead>

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

		<?php echo $this->pagination->getListFooter(); ?>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
