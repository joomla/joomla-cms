<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');

?>
<form action="<?php echo JRoute::_('index.php?option=com_users&view=levels');?>" method="post" name="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="search"><?php echo JText::sprintf('JSearch_Label', 'Users'); ?></label>
			<input type="text" name="filter_search" id="search" value="<?php echo $this->state->get('filter.search'); ?>" title="<?php echo JText::sprintf('JSearch_Title', 'Levels'); ?>" />
			<button type="submit"><?php echo JText::_('JSearch_Submit'); ?></button>
			<button type="button" onclick="document.id('search').value='';this.form.submit();"><?php echo JText::_('JSearch_Reset'); ?></button>
		</div>
	</fieldset>
	<div class="clr"> </div>

	<table class="adminlist">
		<thead>
			<tr>
				<th width="20">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(this)" />
				</th>
				<th class="left">
					<?php echo JText::_('Users_Heading_Level_Name'); ?>
				</th>
				<th width="30%">
					<?php // echo JText::_('Users_Heading_Level_User_Groups'); ?>
				</th>
				<th width="30%">
					&nbsp;
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
			$i = 0;
			foreach ($this->items as $item) : ?>
			<tr class="row<?php echo $i++ % 2; ?>">
				<td class="center">
					<?php echo JHtml::_('grid.id', $item->id, $item->id); ?>
				</td>
				<td>
					<a href="<?php echo JRoute::_('index.php?option=com_users&task=level.edit&cid[]='.$item->id);?>">
						<?php echo $item->title; ?></a>
				</td>
				<td>
					<?php //echo nl2br(implode("\n", explode(',', $item->user_groups))); ?>
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
