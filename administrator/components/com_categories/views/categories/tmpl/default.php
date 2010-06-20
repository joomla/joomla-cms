<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_categories
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers'.DS.'html');
JHtml::_('behavior.tooltip');

$user	= &JFactory::getUser();
$userId	= $user->get('id');
$extension	= $this->escape($this->state->get('filter.extension'));
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
$ordering 	= ($listOrder == 'a.lft' && $listDirn == 'asc');
?>
<form action="<?php echo JRoute::_('index.php?option=com_categories&view=categories');?>" method="post" name="adminForm">

	<fieldset id="filter-bar">
		<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->state->get('filter.search'); ?>" title="<?php echo JText::_('COM_CATEGORIES_ITEMS_SEARCH_FILTER'); ?>" />
			<button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>
		<div class="filter-select fltrt">
			<select name="filter_access" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_ACCESS');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'));?>
			</select>

			<select name="filter_published" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
			</select>

			<select name="filter_language" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_LANGUAGE');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('contentlanguage.existing', true, true), 'value', 'text', $this->state->get('filter.language'));?>
			</select>

		</div>
	</fieldset>
	<div class="clr"> </div>

	<table class="adminlist">
		<thead>
			<tr>
				<th width="1%">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(this)" />
				</th>
				<th>
					<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'JPUBLISHED', 'a.published', $listDirn, $listOrder); ?>
				</th>
				<th width="10%">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ORDERING', 'a.lft', $listDirn, $listOrder); ?>
					<?php if ($listOrder == 'a.lft' && $listDirn == 'asc') :?>
						<?php echo JHtml::_('grid.order',  $this->items, 'filesave.png', 'categories.saveorder'); ?>
					<?php else: ?>
						<?php echo '<a class="saveorder inactive" title="Save Order"></a>'?>
					<?php endif; ?>
				</th>
				<th width="10%">
					<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ACCESS', 'access_level', $listDirn, $listOrder); ?>
				</th>
				<th width="5%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'language', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="1%" class="nowrap">
					<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="15">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php
			$originalOrders = array();
			foreach ($this->items as $i => $item) :
				$orderkey = array_search($item->id, $this->ordering[$item->parent_id]);
				$canCheckin	= $user->authorise('core.manage',		'com_checkin') || $item->checked_out==$user->get('id');
				$canChange = $canCheckin;
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="center">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>
					<td class="indent-<?php echo intval(($item->level-1)*15)+4; ?>">
						<?php if ($item->checked_out) : ?>
							<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'categories.', $canCheckin); ?>
						<?php endif; ?>
						<a href="<?php echo JRoute::_('index.php?option=com_categories&task=category.edit&cid[]='.$item->id.'&extension='.$extension);?>">
							<?php echo $this->escape($item->title); ?></a>
						<p class="smallsub" title="<?php echo $this->escape($item->path);?>">
							<?php if (empty($item->note)) : ?>
								<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias));?>
							<?php else : ?>
								<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->note));?>
							<?php endif; ?></p>
					</td>
					<td class="center">
						<?php echo JHtml::_('jgrid.published', $item->published, $i, 'categories.', $canChange);?>
					</td>
					<td class="order">
						<span><?php echo $this->pagination->orderUpIcon($i, isset($this->ordering[$item->parent_id][$orderkey - 1]), 'categories.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
						<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, isset($this->ordering[$item->parent_id][$orderkey + 1]), 'categories.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
						<?php $ordering ?  '' : 'disabled="disabled"'; ?>
						<input type="text" name="order[]" size="5" value="<?php echo $orderkey + 1;?>" <?php echo $ordering ?> class="text-area-order" />
						<?php $originalOrders[] = $orderkey + 1; ?>
					</td>
					<td class="center">
						<?php echo $this->escape($item->access_level); ?>
					</td>
					<td class="center nowrap">
					<?php if ($item->language=='*'):?>
						<?php echo JText::_('JALL'); ?>
					<?php else:?>
						<?php echo $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
					<?php endif;?>
					</td>
					<td class="center">
						<span title="<?php echo sprintf('%d-%d', $item->lft, $item->rgt);?>">
							<?php echo (int) $item->id; ?></span>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<div>
		<input type="hidden" name="extension" value="<?php echo $extension;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<input type="hidden" name="original_order_values" value="<?php echo implode($originalOrders, ','); ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
