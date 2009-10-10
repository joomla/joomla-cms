<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_weblinks
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers'.DS.'html');
JHtml::_('behavior.tooltip');
JHtml::_('script', 'multiselect.js');
$user	= &JFactory::getUser();
$userId	= $user->get('id');
?>

<form action="<?php echo JRoute::_('index.php?option=com_weblinks'); ?>" method="post" name="adminForm" id="adminForm">
	<fieldset class="filter">
		<div class="left">
			<label for="search">
				<?php echo JText::_('JSearch_Filter'); ?>
			</label>
			<input type="text" name="filter_search" id="search" value="<?php echo $this->state->get('filter.search'); ?>" size="60" title="<?php echo JText::_('Weblinks_Search_in_title'); ?>" />

			<button type="submit">
				<?php echo JText::_('JSearch_Filter_Submit'); ?></button>
			<button type="button" onclick="document.id('search').value='';this.form.submit();">
				<?php echo JText::_('JSearch_Filter_Clear'); ?></button>
		</div>
		<div class="right">
			<select name="filter_access" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOption_Select_Access');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'));?>
			</select>

			<select name="filter_published" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOption_Select_Published');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
			</select>

			<select name="filter_category_id" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOption_Select_Category');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('category.options', 'com_weblinks'), 'value', 'text', $this->state->get('filter.category_id'));?>
			</select>
		</div>
	</fieldset>

	<table class="adminlist">
		<thead>
			<tr>
				<th width="5">
					<?php echo JText::_('JGrid_Heading_Row_Number'); ?>
				</th>
				<th width="20">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
				</th>
				<th>
					<?php echo JHtml::_('grid.sort',  'Weblinks_Title_Heading', 'a.title', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="5%" class="nowrap">
					<?php echo JHtml::_('grid.sort',  'Weblinks_State_Heading', 'a.state', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="10%" class="nowrap">
					<?php echo JHtml::_('grid.sort',  'Weblinks_Order_Heading', 'a.ordering', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
					<?php echo JHtml::_('grid.order',  $this->items); ?>
				</th>
				<th width="10%">
					<?php echo JHtml::_('grid.sort',  'Weblinks_Category_Heading', 'category', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="10%">
					<?php echo JHtml::_('grid.sort',  'Weblinks_Access_Heading', 'access_level', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort',  'Weblinks_Hits_Heading', 'a.hits', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="1%" class="nowrap">
					<?php echo JHtml::_('grid.sort',  'JGrid_Heading_ID', 'a.id', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="9">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php
		$n = count($this->items);
		foreach ($this->items as $i => $item) :
			$ordering	= ($this->state->get('list.ordering') == 'a.ordering');
			$checkedOut	= JTable::isCheckedOut($userId, $item->checked_out);

			$item->cat_link 	= JRoute::_('index.php?option=com_categories&extension=com_weblinks&task=edit&type=other&cid[]='. $item->catid);
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td>
					<?php echo $this->pagination->getRowOffset($i); ?>
				</td>
				<td>
					<?php echo JHtml::_('grid.checkedout', $item, $i); ?>
				</td>
				<td>
					<?php if (JTable::isCheckedOut($userId, $item->checked_out)) : ?>
						<?php echo $item->title; ?>
					<?php else : ?>
					<span class="editlinktip hasTip" title="<?php echo JText::_('JCommon_Edit_item');?>::<?php echo $item->title; ?>">
						<a href="<?php echo JRoute::_('index.php?option=com_weblinks&task=weblink.edit&weblink_id='.(int) $item->id); ?>">
							<?php echo $item->title; ?></a></span>
					<?php endif; ?>
					<br /><small><?php echo $this->escape($item->alias);?></small>
				</td>
				<td class="center">
					<?php echo JHtml::_('jgrid.published', $item->state, $i, 'weblinks.');?>
				</td>
				<td class="order">
					<span><?php echo $this->pagination->orderUpIcon($i, ($item->catid == @$this->items[$i-1]->catid),'weblinks.orderup', 'JGrid_Move_Up', $ordering); ?></span>
					<span><?php echo $this->pagination->orderDownIcon($i, $n, ($item->catid == @$this->items[$i+1]->catid), 'weblinks.orderdown', 'JGrid_Move_Down', $ordering); ?></span>
					<?php $disabled = $ordering ?  '' : 'disabled="disabled"'; ?>
					<input type="text" name="order[]" size="5" value="<?php echo $item->ordering;?>" <?php echo $disabled ?> class="text_area" style="text-align: center" />
				</td>
				<td>
					<span class="editlinktip hasTip" title="<?php echo JText::_('Edit Category');?>::<?php echo $item->category; ?>">
					<a href="<?php echo JRoute::_('index.php?option=com_weblinks&view=weblink&task=edit&cid[]='.$item->id); ?>" >
						<?php echo $item->category; ?></a></span>
				</td>
				<td class="center">
					<?php echo $item->access_level; ?>
				</td>
				<td class="center">
					<?php echo $item->hits; ?>
				</td>
				<td class="center">
					<?php echo $item->id; ?>
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
