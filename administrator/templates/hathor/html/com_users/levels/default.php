<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

JHtml::_('behavior.multiselect');

$user      = JFactory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$canOrder  = $user->authorise('core.edit.state', 'com_users');
$saveOrder = $listOrder == 'a.ordering';
?>

<form action="<?php echo JRoute::_('index.php?option=com_users&view=levels'); ?>" method="post" id="adminForm" name="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif; ?>
	<fieldset id="filter-bar">
	<legend class="element-invisible"><?php echo JText::_('COM_USERS_SEARCH_ACCESS_LEVELS'); ?></legend>
		<div class="filter-search">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('COM_USERS_SEARCH_ACCESS_LEVELS'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_USERS_SEARCH_TITLE_LEVELS'); ?>" />
			<button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.getElementById('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_RESET'); ?></button>
		</div>
	</fieldset>
	<div class="clr"> </div>

	<table class="adminlist">
		<thead>
			<tr>
				<th class="checkmark-col">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
				</th>
				<th>
					<?php echo JHtml::_('grid.sort', 'COM_USERS_HEADING_LEVEL_NAME', 'a.title', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap ordering-col">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ORDERING', 'a.ordering', $listDirn, $listOrder); ?>
					<?php if ($canOrder && $saveOrder) : ?>
						<?php echo JHtml::_('grid.order', $this->items, 'filesave.png', 'levels.saveorder'); ?>
					<?php endif; ?>
				</th>
				<th class="nowrap id-col">
					<?php echo JText::_('JGRID_HEADING_ID'); ?>
				</th>
				<th class="width-40">
					&#160;
				</th>
			</tr>
		</thead>

		<tbody>
		<?php foreach ($this->items as $i => $item) :
			$ordering  = ($listOrder == 'a.ordering');
			$canCreate = $user->authorise('core.create',     'com_users');
			$canEdit   = $user->authorise('core.edit',       'com_users');
			$canChange = $user->authorise('core.edit.state', 'com_users');
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td>
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
				<td>
					<?php if ($canEdit) : ?>
					<a href="<?php echo JRoute::_('index.php?option=com_users&task=level.edit&id='.$item->id); ?>">
						<?php echo $this->escape($item->title); ?></a>
					<?php else : ?>
						<?php echo $this->escape($item->title); ?>
					<?php endif; ?>
				</td>
				<td class="order">
					<?php if ($canChange) : ?>
						<?php if ($saveOrder) :?>
							<?php if ($listDirn == 'asc') : ?>
								<span><?php echo $this->pagination->orderUpIcon($i, true, 'levels.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
								<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, true, 'levels.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
							<?php elseif ($listDirn == 'desc') : ?>
								<span><?php echo $this->pagination->orderUpIcon($i, true, 'levels.orderdown', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
								<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, true, 'levels.orderup', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
							<?php endif; ?>
						<?php endif; ?>
						<?php $disabled = $saveOrder ?  '' : 'disabled="disabled"'; ?>
						<input type="text" name="order[]" value="<?php echo $item->ordering; ?>" <?php echo $disabled; ?> class="text-area-order" title="<?php echo $item->title; ?> order" />
					<?php else : ?>
						<?php echo $item->ordering; ?>
					<?php endif; ?>
				</td>
				<td class="center">
					<?php echo (int) $item->id; ?>
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
</div>
</form>
