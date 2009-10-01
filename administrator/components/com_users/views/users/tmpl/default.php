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

<form action="<?php echo JRoute::_('index.php?option=com_users&view=users');?>" method="post" name="adminForm">
	<fieldset class="filter">
		<div class="fltlft">
			<label for="search"><?php echo JText::sprintf('JSearch_Label', 'Users'); ?></label>
			<input type="text" name="filter_search" id="search" value="<?php echo $this->state->get('filter.search'); ?>" size="30" title="<?php echo JText::sprintf('JSearch_Title', 'Users'); ?>" />
			<button type="submit"><?php echo JText::_('JSearch_Submit'); ?></button>
			<button type="button" onclick="document.id('search').value='';this.form.submit();"><?php echo JText::_('JSearch_Reset'); ?></button>
		</div>
		<div class="fltrt">
			<label for="filter_state">
				<?php echo JText::sprintf('Users_Filter_Label', 'Users'); ?>
			</label>
			<?php
			echo JHtml::_('filter.state', $this->state->get('filter.state'));
			echo JHtml::_('filter.active', $this->state->get('filter.active'));
			echo JHtml::_('filter.usergroup', $this->state->get('filter.group'));
			?>
		</div>
	</fieldset>

	<table class="adminlist">
		<thead>
			<tr>
				<th width="20">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items);?>)" />
				</th>
				<th class="left">
					<?php echo JHtml::_('grid.sort', 'Users_Heading_Name', 'a.name', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th nowrap="nowrap" width="10%">
					<?php echo JHtml::_('grid.sort', 'Users_Heading_Username', 'a.username', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th nowrap="nowrap" width="5%">
					<?php echo JHtml::_('grid.sort', 'Users_Heading_Enabled', 'a.block', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th nowrap="nowrap" width="5%">
					<?php echo JHtml::_('grid.sort', 'Users_Heading_Activated', 'a.activation', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th nowrap="nowrap" width="10%">
					<?php echo JText::_('Users_Heading_Groups'); ?>
				</th>
				<th nowrap="nowrap" width="15%">
					<?php echo JHtml::_('grid.sort', 'Users_Heading_Email', 'a.email', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th nowrap="nowrap" width="15%">
					<?php echo JHtml::_('grid.sort', 'Users_Heading_Last_Visit_Date', 'a.lastvisitDate', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th nowrap="nowrap" width="15%">
					<?php echo JHtml::_('grid.sort', 'Users_Heading_Registration_Date', 'a.registerDate', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th nowrap="nowrap" width="3%">
					<?php echo JHtml::_('grid.sort', 'JGrid_Heading_ID', 'a.id', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
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
			<tr class="row<?php echo $i % 2; ?>">
				<td style="text-align:center">
					<?php echo JHtml::_('grid.id', $i++, $item->id); ?>
				</td>
				<td>
					<a href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&cid[]='.$item->id); ?>" title="<?php echo JText::sprintf('Users_Edit_User', $item->name); ?>">
						<?php echo $item->name; ?></a>
				</td>
				<td align="center">
					<?php echo $item->username; ?>
				</td>
				<td align="center">
					<?php echo JHtml::_('grid.boolean', $i, !$item->block, 'users.unblock', 'users.block'); ?>
				</td>
				<td align="center">
					<?php echo JHtml::_('grid.boolean', $i, !$item->activation, 'users.activate', null); ?>
				</td>
				<td align="center">
					<?php echo nl2br($item->group_names); ?>
				</td>
				<td align="center">
					<?php echo $item->email; ?>
				</td>
				<td align="center">
					<?php echo JHtml::date($item->lastvisitDate); ?>
				</td>
				<td align="center">
					<?php echo JHtml::date($item->registerDate); ?>
				</td>
				<td align="center">
					<?php echo $item->id; ?>
				</td>
			</tr>
			<?php
			$i++;
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