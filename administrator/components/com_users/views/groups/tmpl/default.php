<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');

// Load the default stylesheet.
JHtml::stylesheet('default.css', 'administrator/components/com_users/media/css/');
?>
<form action="<?php echo JRoute::_('index.php?option=com_users&view=groups');?>" method="post" name="adminForm">
	<fieldset class="filter">
		<div class="left">
			<label for="search"><?php echo JText::sprintf('JSearch_Label', 'Groups'); ?></label>
			<input type="text" name="filter_search" id="search" value="<?php echo $this->state->get('filter.search'); ?>" size="30" title="<?php echo JText::sprintf('JSearch_Title', 'Groups'); ?>" />
			<button type="submit"><?php echo JText::_('JSearch_Submit'); ?></button>
			<button type="button" onclick="$('search').value='';this.form.submit();"><?php echo JText::_('JSearch_Reset'); ?></button>
		</div>
	</fieldset>
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
				<th width="50%">
					<?php echo JText::_('Users_Heading_Group_Actions'); ?>
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
				<td style="text-align:center">
					<?php echo JHtml::_('grid.id', $item->id, $item->id); ?>
				</td>
				<td style="padding-left:<?php echo intval(($item->level)*15)+4; ?>px">
					<a href="<?php echo JRoute::_('index.php?option=com_users&task=group.edit&cid[]='.$item->id);?>">
						<?php echo $item->title; ?></a>
				</td>
				<td align="center">
					<?php echo $item->user_count ? $item->user_count : ''; ?>
				</td>
				<td>
					<?php
					$actions = explode(',', $item->actions);
					for ($i = 0, $t = count($actions); $i < $t; $i++) {
						echo JText::_($actions[$i]);
						echo '<br />';
					}
					?>
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
