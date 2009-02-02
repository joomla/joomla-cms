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

// Build the toolbar.
$this->buildDefaultToolBar();
?>

<form action="<?php echo JRoute::_('index.php?option=com_members&view=members');?>" method="post" name="adminForm">
	<fieldset class="filter">
		<div class="left">
			<label for="search"><?php echo JText::_('JSearch_Filter'); ?>:</label>
			<input type="text" name="filter_search" id="search" value="<?php echo $this->state->get('filter.search'); ?>" size="60" title="<?php echo JText::_('Members_Search_in_name'); ?>" />
			<button type="submit"><?php echo JText::_('JSearch_Filter_Submit'); ?></button>
			<button type="button" onclick="$('search').value='';this.form.submit();"><?php echo JText::_('JSearch_Filter_Clear'); ?></button>
		</div>
		<div class="right">
			<ol>
				<li>
					<label for="filter_group_id">
						<?php echo JText::_('Members_Filter_User_Group'); ?>
					</label>
					<?php echo JHtml::_('access.usergroup', 'filter_group_id', $this->state->get('filter.group_id'), 'onchange="this.form.submit()"'); ?>
				</li>
			</ol>
		</div>
	</fieldset>

	<table class="adminlist">
		<thead>
			<tr>
				<th width="20">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items);?>)" />
				</th>
				<th class="left">
					<?php echo JHtml::_('grid.sort', 'Members_Heading_Login_Name', 'a.username', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
					/ <?php echo JHtml::_('grid.sort', 'Members_Heading_Email', 'a.email', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th nowrap="nowrap" width="15%">
					<?php echo JHtml::_('grid.sort', 'Members_Heading_Name', 'a.name', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th nowrap="nowrap" width="5%">
					<?php echo JHtml::_('grid.sort', 'Members_Heading_Enabled', 'a.block', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th nowrap="nowrap" width="5%">
					<?php echo JHtml::_('grid.sort', 'Members_Heading_Activated', 'a.activation', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th nowrap="nowrap" width="15%">
					<?php echo JHtml::_('grid.sort', 'Members_Heading_Groups', 'a.gid', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th nowrap="nowrap" width="15%">
					<?php echo JHtml::_('grid.sort', 'Members_Heading_Registration_Date', 'a.registerDate', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
					<br /><?php echo JHtml::_('grid.sort', 'Members_Heading_Last_Visit_Date', 'a.lastvisitDate', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'JCommon_Heading_ID', 'a.id', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?>
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
					<a href="<?php echo JRoute::_('index.php?option=com_members&task=member.edit&cid[]='.$item->id);?>">
						<?php echo $item->username; ?></a>
						/ <?php echo $item->email; ?>
				</td>
				<td align="center">
					<?php echo $item->name; ?>
				</td>
				<td align="center">
					<?php echo $item->block; ?>
				</td>
				<td align="center">
					<?php echo $item->activation ? '' : '<img src="images/tick.png" width="16" height="16" border="0" alt="" />'; ?>
				</td>
				<td align="left">
					<?php echo nl2br($item->group_names); ?>
				</td>
				<td align="center">
					<?php echo JHtml::date($item->registerDate); ?>
					<br /><?php echo JHtml::date($item->lastvisitDate); ?>
				</td>
				<td align="center">
					<?php echo $item->id; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<fieldset>
		<legend><?php echo JText::_('Members_Batch_Update'); ?></legend>

		<label for="batch[group_id]">
			<?php echo JText::_('Members_Batch_Group'); ?>
		</label>
		<?php echo JHtml::_('access.usergroup', 'batch[group_id]', $this->state->get('list.group_id')); ?>

		<input type="radio" name="batch[group_logic]" id="batch_group_logic_add" value="add" checked="checked" />
		<label for="batch_group_logic_add">
			<?php echo JText::_('Members_Batch_Add_To_Group'); ?>
		</label>
		<input type="radio" name="batch[group_logic]" id="batch_group_logic_del" value="del" />
		<label for="batch_group_logic_del">
			<?php echo JText::_('Members_Batch_Delete_From_Group'); ?>
		</label>
		<input type="radio" name="batch[group_logic]" id="batch_group_logic_set" value="set" />
		<label for="batch_group_logic_set">
			<?php echo JText::_('Members_Batch_Set_To_Group'); ?>
		</label>

		<button type="button" onclick="submitbutton('member.batch');"><?php echo JText::_('Members_Batch_Process'); ?></button>

	</fieldset>


	<input type="hidden" name="task" value="" />

	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->state->get('list.ordering'); ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->get('list.direction'); ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
