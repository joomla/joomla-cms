<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_members
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die('Invalid Request.');

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');

// Load the default stylesheet.
JHtml::stylesheet('default.css', 'administrator/components/com_members/media/css/');
?>
<form action="<?php echo JRoute::_('index.php?option=com_members&view=groups');?>" method="post" name="adminForm">
	<fieldset class="filter">
		<div class="left">
			<label for="search"><?php echo JText::_('JSearch_Filter'); ?>:</label>
			<input type="text" name="filter_search" id="search" value="<?php echo $this->state->get('filter.search'); ?>" size="60" title="<?php echo JText::_('Members_Search_in_title'); ?>" />
			<button type="submit"><?php echo JText::_('JSearch_Filter_Submit'); ?></button>
			<button type="button" onclick="$('search').value='';this.form.submit();"><?php echo JText::_('JSearch_Filter_Clear'); ?></button>
		</div>
	</fieldset>
	<table class="adminlist">
		<thead>
			<tr>
				<th width="20">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(this)" />
				</th>
				<th class="left">
					<?php echo JText::_('Members_Heading_Group_Title'); ?>
				</th>
				<th width="10%">
					<?php echo JText::_('Members_Heading_Members_in_group'); ?>
				</th>
				<th width="50%">
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
				<td style="text-align:center">
					<?php if ($item->id > 30) : ?>
					<?php echo JHtml::_('grid.id', $item->id, $item->id); ?>
					<?php endif; ?>
				</td>
				<td style="padding-left:<?php echo intval(($item->level)*15)+4; ?>px">
					<?php if ($item->id > 8) : ?>
					<a href="<?php echo JRoute::_('index.php?option=com_members&task=group.edit&cid[]='.$item->id);?>">
						<?php echo $item->title; ?></a>
					<?php else : ?>
					<?php echo $item->title; ?>
					<?php endif; ?>
				</td>
				<td align="center">
					<?php echo $item->user_count ? $item->user_count : ''; ?>
				</td>
				<td>
					<?php echo nl2br(implode("\n", explode(',', $item->actions))); ?>
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
