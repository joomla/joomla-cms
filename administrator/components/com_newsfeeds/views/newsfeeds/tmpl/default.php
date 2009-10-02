<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_newsfeeds
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers'.DS.'html');
JHtml::_('behavior.tooltip');
JHtml::_('script', 'multiselect.js');
$user	= &JFactory::getUser();
$userId	= $user->get('id');
?>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm">
	<fieldset class="filter">
		<div class="left">
			<label for="search"><?php echo JText::_('JSearch_Filter'); ?>:</label>
			<input type="text" name="filter_search" id="search" value="<?php echo $this->state->get('filter.search'); ?>" size="60" title="<?php echo JText::_('Newsfeeds_Search_in_title'); ?>" />
			<button type="submit"><?php echo JText::_('JSearch_Filter_Submit'); ?></button>
			<button type="button" onclick="document.id('search').value='';this.form.submit();"><?php echo JText::_('JSearch_Filter_Clear'); ?></button>
		</div>
		<div class="right">
			<ol>
				<li>
					<label for="filter_category_id">
						<?php echo JText::_('Newsfeeds_Filter_Category'); ?>
					</label>
					<?php echo JHtml::_('list.category', 'filter_category', 'com_newsfeeds', $this->state->get('filter.catid'), 'onchange="this.form.submit()"'); ?>
				</li>
				<li>
					<label for="filter_published">
						<?php echo JText::_('Newsfeeds_Filter_State'); ?>
					</label>
					<?php echo JHtml::_('newsfeed.filterstate', $this->state->get('filter.published'));?>
				</li>
			</ol>
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
				<th class="title">
					<?php echo JHtml::_('grid.sort',  'Newsfeeds_Title_Heading', 'a.name', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="5%" nowrap="nowrap">
					<?php echo JHtml::_('grid.sort',  'Newsfeeds_State_Heading', 'a.published', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>

				<th width="10%" nowrap="nowrap">
					<?php echo JHtml::_('grid.sort',  'Newsfeeds_Order_Heading', 'a.ordering', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
					<?php echo JHtml::_('grid.order',  $this->items, 'filesave.png', 'newsfeeds.saveorder'); ?>
				</th>
				<th width="10%"  class="title">
					<?php echo JHtml::_('grid.sort',  'Newsfeeds_Category_Heading', 'access_level', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="10%"  class="title">
					<?php echo JHtml::_('grid.sort',  'Newsfeeds_Num_Articles_Heading', 'numarticles', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort',  'Newsfeeds_Cache_Time_Heading', 'a.hits', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="1%" nowrap="nowrap">
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

			$item->cat_link 	= JRoute::_('index.php?option=com_categories&section=com_newsfeeds&task=edit&type=other&cid[]='. $item->catid);
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
						<?php echo $item->name; ?>
					<?php else : ?>
					<span class="editlinktip hasTip" title="<?php echo JText::_('JCommon_Edit_item');?>::<?php echo $item->name; ?>">
						<a href="<?php echo JRoute::_('index.php?option=com_newsfeeds&task=newsfeed.edit&newsfeed_id='.(int) $item->id); ?>">
							<?php echo $item->name; ?></a></span>
					<?php endif; ?>
				</td>
				<td align="center">
					<?php echo JHtml::_('newsfeed.state', $item->published, $i);?>
				</td>



				<td class="order">
					<span><?php echo $this->pagination->orderUpIcon($i, ($item->catid == @$this->items[$i-1]->catid),'newsfeeds.orderup', 'JGrid_Move_Up', $ordering); ?></span>
					<span><?php echo $this->pagination->orderDownIcon($i, $n, ($item->catid == @$this->items[$i+1]->catid), 'newsfeeds.orderdown', 'JGrid_Move_Down', $ordering); ?></span>
					<?php $disabled = $ordering ?  '' : 'disabled="disabled"'; ?>
					<input type="text" name="order[]" size="5" value="<?php echo $item->ordering;?>" <?php echo $disabled ?> class="text_area" style="text-align: center" />
				</td>
				<td>
					<span class="editlinktip hasTip" title="<?php echo JText::_('Newsfeeds_Edit_Category');?>::<?php echo $item->category; ?>">
					<a href="<?php echo JRoute::_('index.php?option=com_newsfeeds&view=newsfeed&task=edit&cid[]='.$item->id); ?>" >
						<?php echo $item->category; ?></a></span>
				</td>
				<td align="center">
					<?php echo $item->numarticles; ?>
				</td>
				<td align="center">
					<?php echo $item->cache_time; ?>
				</td>
				<td align="center">
					<?php echo $item->id; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<input type="hidden" name="option" value="com_newsfeeds" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->state->get('list.ordering'); ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->get('list.direction'); ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
