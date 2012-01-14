<?php
/**
 * @version		$Id: default.php 19437 2010-11-10 11:56:23Z infograf768 $
 * @package		Joomla.Administrator
 * @subpackage	com_banners
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHTML::_('script','system/multiselect.js',false,true);

$user		= JFactory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
$canOrder	= $user->authorise('core.edit.state', 'com_banners.category');
$saveOrder	= $listOrder=='ordering';
?>
<form action="<?php echo JRoute::_('index.php?option=com_banners&view=banners'); ?>" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_BANNERS_SEARCH_IN_TITLE'); ?>" />
			<button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
		</div>
		<div class="filter-select fltrt">

			<select name="filter_state" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.state'), true);?>
			</select>

			<select name="filter_client_id" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('COM_BANNERS_SELECT_CLIENT');?></option>
				<?php echo JHtml::_('select.options', JFormFieldBannerClient::getOptions(), 'value', 'text', $this->state->get('filter.client_id'));?>
			</select>
			<select name="filter_category_id" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_CATEGORY');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('category.options', 'com_banners'), 'value', 'text', $this->state->get('filter.category_id'));?>
			</select>
			<select name="filter_language" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOPTION_SELECT_LANGUAGE');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('contentlanguage.existing', true, true), 'value', 'text', $this->state->get('filter.language'));?>
			</select>
		</div>
	</fieldset>
	<?php if( $this->pagination->total > 0 ): ?><div id="pagination-top"><?php echo $this->pagination->getListFooter(); ?></div><?php endif; ?>
	<div class="clr"> </div>

	<table class="adminlist">
		<thead>
			<tr>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value="" onclick="checkAll(this)" />
				</th>
				<th>
					<?php echo JHtml::_('grid.sort',  'COM_BANNERS_HEADING_NAME', 'name', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'JPUBLISHED', 'state', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'COM_BANNERS_HEADING_STICKY', 'sticky', $listDirn, $listOrder); ?>
				</th>
				<th width="10%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_BANNERS_HEADING_CLIENT', 'client_name', $listDirn, $listOrder); ?>
				</th>
				<th width="10%">
					<?php echo JHtml::_('grid.sort', 'JCATEGORY', 'category_title', $listDirn, $listOrder); ?>
				</th>
				<th width="10%">
					<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ORDERING', 'ordering', $listDirn, $listOrder); ?>
					<?php if ($canOrder && $saveOrder): ?>
						<?php echo JHtml::_('grid.order',  $this->items, 'filesave.png', 'banners.saveorder'); ?>
					<?php endif;?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'COM_BANNERS_HEADING_IMPRESSIONS', 'impmade', $listDirn, $listOrder); ?>
				</th>
				<th width="80">
					<?php echo JHtml::_('grid.sort', 'COM_BANNERS_HEADING_CLICKS', 'clicks', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo JText::_('COM_BANNERS_HEADING_METAKEYWORDS'); ?>
				</th>
				<th width="10%">
					<?php echo JText::_('COM_BANNERS_HEADING_PURCHASETYPE'); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'a.language', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="1%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'id', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>
		<?php if( $this->pagination->total >= 10 ): ?>
		<tfoot>
			<tr>
				<th width="1%">
					<input type="checkbox" name="checkall-toggle" value="" onclick="checkAll(this)" />
				</th>
				<th>
					<?php echo JHtml::_('grid.sort',  'COM_BANNERS_HEADING_NAME', 'name', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'JPUBLISHED', 'state', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'COM_BANNERS_HEADING_STICKY', 'sticky', $listDirn, $listOrder); ?>
				</th>
				<th width="10%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'COM_BANNERS_HEADING_CLIENT', 'client_name', $listDirn, $listOrder); ?>
				</th>
				<th width="10%">
					<?php echo JHtml::_('grid.sort', 'JCATEGORY', 'category_title', $listDirn, $listOrder); ?>
				</th>
				<th width="10%">
					<?php echo JHtml::_('grid.sort',  'JGRID_HEADING_ORDERING', 'ordering', $listDirn, $listOrder); ?>
					<?php if ($canOrder && $saveOrder): ?>
						<?php echo JHtml::_('grid.order',  $this->items, 'filesave.png', 'banners.saveorder'); ?>
					<?php endif;?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'COM_BANNERS_HEADING_IMPRESSIONS', 'impmade', $listDirn, $listOrder); ?>
				</th>
				<th width="80">
					<?php echo JHtml::_('grid.sort', 'COM_BANNERS_HEADING_CLICKS', 'clicks', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo JText::_('COM_BANNERS_HEADING_METAKEYWORDS'); ?>
				</th>
				<th width="10%">
					<?php echo JText::_('COM_BANNERS_HEADING_PURCHASETYPE'); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_LANGUAGE', 'a.language', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="1%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'id', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</tfoot>
		<?php endif; ?>
		<tbody>
		<?php foreach ($this->items as $i => $item) :
			$ordering	= ($listOrder == 'ordering');
			$item->cat_link = JRoute::_('index.php?option=com_categories&extension=com_banners&task=edit&type=other&cid[]='. $item->catid);
			$canCreate	= $user->authorise('core.create',		'com_banners.category.'.$item->catid);
			$canEdit	= $user->authorise('core.edit',			'com_banners.category.'.$item->catid);
			$canCheckin	= $user->authorise('core.manage',		'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
			$canChange	= $user->authorise('core.edit.state',	'com_banners.category.'.$item->catid) && $canCheckin;
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
				<td>
					<?php if ($item->checked_out) : ?>
						<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'banners.', $canCheckin); ?>
					<?php endif; ?>
					<?php if ($canEdit) : ?>
						<a href="<?php echo JRoute::_('index.php?option=com_banners&task=banner.edit&id='.(int) $item->id); ?>">
							<?php echo $this->escape($item->name); ?></a>
					<?php else : ?>
						<?php echo $this->escape($item->name); ?>
					<?php endif; ?>
					<p class="smallsub">
						<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias));?></p>
				</td>
				<td class="center">
					<?php echo JHtml::_('jgrid.published', $item->state, $i, 'banners.', $canChange, 'cb', $item->publish_up, $item->publish_down); ?>
				</td>
				<td class="center">
					<?php echo JHtml::_('jgrid.published', $item->sticky, $i, 'banners.sticky_', $canChange);?>
				</td>
				<td class="center">
					<?php echo $item->client_name;?>
				</td>
				<td class="center">
					<?php echo $this->escape($item->category_title); ?>
				</td>
				<td class="order">
					<?php if ($canChange) : ?>
						<?php if ($saveOrder) : ?>
							<?php if ($listDirn == 'asc') : ?>
								<span><?php echo $this->pagination->orderUpIcon($i, (@$this->items[$i-1]->catid == $item->catid), 'banners.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
								<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, (@$this->items[$i+1]->catid == $item->catid), 'banners.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
							<?php elseif ($listDirn == 'desc') : ?>
								<span><?php echo $this->pagination->orderUpIcon($i, (@$this->items[$i-1]->catid == $item->catid), 'banners.orderdown', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
								<span><?php echo $this->pagination->orderDownIcon($i, $this->pagination->total, (@$this->items[$i+1]->catid == $item->catid), 'banners.orderup', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
							<?php endif; ?>
						<?php endif; ?>
						<?php $disabled = $saveOrder ?  '' : 'disabled="disabled"'; ?>
						<input type="text" name="order[]" size="5" value="<?php echo $item->ordering;?>" <?php echo $disabled;?> class="text-area-order" />
					<?php else : ?>
						<?php echo $item->ordering; ?>
					<?php endif; ?>
				</td>
				<td class="center">
					<?php echo JText::sprintf('COM_BANNERS_IMPRESSIONS', $item->impmade, $item->imptotal ? $item->imptotal : JText::_('COM_BANNERS_UNLIMITED'));?>
				</td>
				<td class="center">
					<?php echo $item->clicks;?> -
					<?php echo sprintf('%.2f%%', $item->impmade ? 100 * $item->clicks/$item->impmade : 0);?>
				</td>
				<td>
					<?php echo $item->metakey; ?>
				</td>
				<td class="center">
					<?php if ($item->purchase_type < 0):?>
						<?php echo JText::sprintf('COM_BANNERS_DEFAULT',($item->client_purchase_type > 0) ? JText::_('COM_BANNERS_FIELD_VALUE_'.$item->client_purchase_type) : JText::_('COM_BANNERS_FIELD_VALUE_'.$this->state->params->get('purchase_type')));?>
					<?php else:?>
						<?php echo JText::_('COM_BANNERS_FIELD_VALUE_'.$item->purchase_type);?>
					<?php endif;?>
				</td>
				<td class="center nowrap">
					<?php if ($item->language=='*'):?>
						<?php echo JText::alt('JALL','language'); ?>
					<?php else:?>
						<?php echo $item->language_title ? $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
					<?php endif;?>
				</td>
				<td class="center">
					<?php echo $item->id; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php if( $this->pagination->total > 0): ?><div id="pagination-bottom"><?php echo $this->pagination->getListFooter(); ?></div><?php endif; ?>
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
