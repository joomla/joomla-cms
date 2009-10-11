<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');

?>
<form action="<?php echo JRoute::_('index.php?option=com_users&view=groups');?>" method="post" name="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="search"><?php echo JText::sprintf('JSearch_Label', 'Users'); ?></label>
			<input type="text" name="filter_search" id="search" value="<?php echo $this->state->get('filter.search'); ?>" title="<?php echo JText::sprintf('JSearch_Title', 'Groups'); ?>" />
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
					<?php echo JText::_('Users_Heading_Group_Title'); ?>
				</th>
				<th width="10%">
					<?php echo JText::_('Users_Heading_Users_in_group'); ?>
				</th>
				<th width="5%">
					<?php echo JText::_('JGrid_Heading_ID'); ?>
				</th>
				<th width="40%">
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
			$n = 0;
			foreach ($this->items as $item) : ?>
			<tr class="row<?php echo $n++ % 2; ?>">
				<td class="center">
					<?php echo JHtml::_('grid.id', $item->id, $item->id); ?>
				</td>
<!-- TO DO: UI system for representing levels and parent-child relationships -->
				<td style="padding-left:<?php echo intval(($item->level)*15)+4; ?>px">
					<a href="<?php echo JRoute::_('index.php?option=com_users&task=group.edit&cid[]='.$item->id);?>">
						<?php echo $item->title; ?></a>
				</td>
				<td class="center">
					<?php echo $item->user_count ? $item->user_count : ''; ?>
				</td>
				<td class="center">
					<?php echo $item->id; ?>
				</td>
				<td>
					&nbsp;
				</td>
			</tr>
			<?php
			endforeach;
			?>
		</tbody>
	</table>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->state->get('list.ordering'); ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->get('list.direction'); ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
