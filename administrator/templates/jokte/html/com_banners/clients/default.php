<?php
/**
 * @version		$Id: default.php 19240 2010-10-28 04:45:48Z eddieajau $
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
$user	= JFactory::getUser();
$userId	= $user->get('id');
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
?>

<form action="<?php echo JRoute::_('index.php?option=com_banners&view=clients'); ?>" method="post" name="adminForm" id="adminForm">
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
					<?php echo JHtml::_('grid.sort', 'COM_BANNERS_HEADING_CLIENT', 'name', $listDirn, $listOrder); ?>
				</th>
				<th width="30%">
					<?php echo JHtml::_('grid.sort', 'COM_BANNERS_HEADING_CONTACT', 'contact', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort',  'JPUBLISHED', 'state', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'COM_BANNERS_HEADING_ACTIVE', 'nbanners', $listDirn, $listOrder); ?>
				</th>
				<th width="5%" class="nowrap">
					<?php echo JText::_('COM_BANNERS_HEADING_METAKEYWORDS'); ?>
				</th>
				<th width="10%">
					<?php echo JText::_('COM_BANNERS_HEADING_PURCHASETYPE'); ?>
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
					<?php echo JHtml::_('grid.sort', 'COM_BANNERS_HEADING_CLIENT', 'name', $listDirn, $listOrder); ?>
				</th>
				<th width="30%">
					<?php echo JHtml::_('grid.sort', 'COM_BANNERS_HEADING_CONTACT', 'contact', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort',  'JPUBLISHED', 'state', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'COM_BANNERS_HEADING_ACTIVE', 'nbanners', $listDirn, $listOrder); ?>
				</th>
				<th width="5%" class="nowrap">
					<?php echo JText::_('COM_BANNERS_HEADING_METAKEYWORDS'); ?>
				</th>
				<th width="10%">
					<?php echo JText::_('COM_BANNERS_HEADING_PURCHASETYPE'); ?>
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
			$canCreate	= $user->authorise('core.create',		'com_banners');
			$canEdit	= $user->authorise('core.edit',			'com_banners');
			$canCheckin	= $user->authorise('core.manage',		'com_checkin') || $item->checked_out==$user->get('id') || $item->checked_out==0;
			$canChange	= $user->authorise('core.edit.state',	'com_banners') && $canCheckin;
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
				<td>
					<?php if ($item->checked_out) : ?>
						<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'clients.', $canCheckin); ?>
					<?php endif; ?>
					<?php if ($canEdit) : ?>
						<a href="<?php echo JRoute::_('index.php?option=com_banners&task=client.edit&id='.(int) $item->id); ?>">
							<?php echo $this->escape($item->name); ?></a>
					<?php else : ?>
							<?php echo $this->escape($item->name); ?>
					<?php endif; ?>
				</td>
				<td class="center">
					<?php echo $item->contact;?>
				</td>
				<td class="center">
					<?php echo JHtml::_('jgrid.published', $item->state, $i, 'clients.', $canChange);?>
				</td>
				<td class="center">
					<?php echo $item->nbanners; ?>
				</td>
				<td>
					<?php echo $item->metakey; ?>
				</td>
				<td class="center">
					<?php if ($item->purchase_type<0):?>
						<?php echo JText::sprintf('COM_BANNERS_DEFAULT',JText::_('COM_BANNERS_FIELD_VALUE_'.$this->state->params->get('purchase_type')));?>
					<?php else:?>
						<?php echo JText::_('COM_BANNERS_FIELD_VALUE_'.$item->purchase_type);?>
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
