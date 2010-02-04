<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_banners
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers'.DS.'html');
JHtml::_('behavior.tooltip');
JHtml::_('script', 'multiselect.js');
JHtml::_('behavior.modal', 'a.modal');
$user	= JFactory::getUser();
$userId	= $user->get('id');
?>
<form action="<?php echo JRoute::_('index.php?option=com_banners&view=tracks'); ?>" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-search fltlft">

			<label class="filter-hide-lbl"><?php echo JText::_('Banners_Begin_Label'); ?></label>
			<?php echo JHtml::_('calendar', $this->state->get('filter.begin'), 'filter_begin','filter_begin','%Y-%m-%d' , array('size'=>10,'onchange'=>'this.form.submit()'));?>

			<label class="filter-hide-lbl"><?php echo JText::_('Banners_End_Label'); ?></label>
			<?php echo JHtml::_('calendar', $this->state->get('filter.end'), 'filter_end', 'filter_end','%Y-%m-%d' ,array('size'=>10,'onchange'=>'this.form.submit()'));?>

		</div>
		<div class="filter-select fltrt">

			<select name="filter_type" class="inputbox" onchange="this.form.submit()">
				<?php echo JHtml::_('select.options', array(JHtml::_('select.option', '0', JText::_('Banners_Select_Type')), JHtml::_('select.option', 1, JText::_('Banners_Impression')), JHtml::_('select.option', 2, JText::_('Banners_Click'))), 'value', 'text', $this->state->get('filter.type'));?>
			</select>

			<?php $category = $this->state->get('filter.category_id');?>
			<select name="filter_category_id" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('JOption_Select_Category');?></option>
				<option value="0"<?php if($category==='0') echo ' selected="selected"';?>><?php echo JText::_('JOption_No_Category');?></option>
				<?php echo JHtml::_('select.options', JHtml::_('category.options', 'com_banners'), 'value', 'text', $category);?>
			</select>

			<select name="filter_client_id" class="inputbox" onchange="this.form.submit()">
				<option value=""><?php echo JText::_('Banners_Select_Client');?></option>
				<?php echo JHtml::_('select.options', JFormFieldBannerClient::getOptions(), 'value', 'text', $this->state->get('filter.client_id'));?>
			</select>
		</div>
	</fieldset>
	<div class="clr"> </div>

	<table class="adminlist">
		<thead>
			<tr>
				<th class="title">
					<?php echo JHtml::_('grid.sort', 'Banners_Heading_Name', 'name', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="20%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'Banners_Heading_Client', 'client_name', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="20%">
					<?php echo JHtml::_('grid.sort', 'JGrid_Heading_Category', 'category_title', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="10%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'Banners_Heading_Type', 'track_type', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="10%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'Banners_Heading_Count', 'count', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="10%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'Banners_Heading_Date', 'track_date', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="6">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach ($this->items as $i => $item) :?>
			<tr class="row<?php echo $i % 2; ?>">
				<td>
					<?php echo $item->name;?>
				</td>
				<td>
					<?php echo $item->client_name;?>
				</td>
				<td>
					<?php echo $item->category_title;?>
				</td>
				<td>
					<?php echo $item->track_type==1 ? JText::_('Banners_Impression'): JText::_('Banners_Click');?>
				</td>
				<td>
					<?php echo $item->count;?>
				</td>
				<td>
					<?php echo $item->track_date;?>
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
