<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_search
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
?>
<form action="<?php echo JRoute::_('index.php?option=com_search&view=searches'); ?>" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSearch_Filter_Label'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->state->get('filter.search'); ?>" title="<?php echo JText::_('Search_Search_in_phrase'); ?>" />
			<button type="submit"><?php echo JText::_('JSearch_Filter_Submit'); ?></button>
			<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSearch_Filter_Clear'); ?></button>
		</div>
		<div class="filter-select fltrt">
			<?php if ($this->enabled) : ?>
			<span class="enabled"><?php echo JText::_('Search_Logging_Enabled'); ?></span>
			<?php else : ?>
			<span class="disabled"><?php echo JText::_('Search_Logging_Disabled'); ?></span>
			<?php endif; ?>

			<span class="adminlist-searchstatus">
			<?php if ($this->state->get('filter.results')) : ?>
				<a href="<?php echo JRoute::_('index.php?option=com_search&filter_results=0');?>">
					<?php echo JText::_('Search_Hide_Search_Results'); ?></a>
			<?php else : ?>
				<a href="<?php echo JRoute::_('index.php?option=com_search&filter_results=1');?>">
					<?php echo JText::_('Search_Show_Search_Results'); ?></a>
			<?php endif; ?>
			</span>
		</div>
	</fieldset>
	<div class="clr"> </div>

	<table class="adminlist">
		<thead>
			<tr>
				<th width="20">
					<?php echo JText::_('JGRID_HEADING_ROW_NUMBER'); ?>
				</th>
				<th class="title">
					<?php echo JHtml::_('grid.sort', 'Search_Heading_Phrase', 'a.search_term', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="15%">
					<?php echo JHtml::_('grid.sort', 'Search_Heading_Hits', 'a.hits', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="15%">
					<?php echo JText::_('Search_Heading_Results'); ?>
				</th>
				<th width="30%">
					&nbsp;
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
						<?php echo JText::_('Search_No_results'); ?>
					<?php endif; ?>
					</td>
					<td>
						&nbsp;
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>


	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->state->get('list.ordering'); ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->get('list.direction'); ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>