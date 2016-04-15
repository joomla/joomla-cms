<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$loggeduser = JFactory::getUser();
?>

<form action="<?php echo JRoute::_('index.php?option=com_users&view=users');?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
	<fieldset id="filter-bar">
	<legend class="element-invisible"><?php echo JText::_('COM_USERS_SEARCH_USERS'); ?></legend>
		<div class="filter-search">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('COM_USERS_SEARCH_USERS'); ?></label>
			<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_USERS_SEARCH_USERS'); ?>" />
			<button type="submit"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
			<button type="button" onclick="document.getElementById('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_RESET'); ?></button>
		</div>

		<div class="filter-select">
			<span class="faux-label"><?php echo JText::_('COM_USERS_FILTER_LABEL'); ?></span>

			<label class="selectlabel" for="filter_state">
				<?php echo JText::_('COM_USERS_FILTER_LABEL'); ?>
			</label>
			<select name="filter_state" id="filter_state">
				<option value="*"><?php echo JText::_('COM_USERS_FILTER_STATE');?></option>
				<?php echo JHtml::_('select.options', UsersHelper::getStateOptions(), 'value', 'text', $this->state->get('filter.state'));?>
			</select>

			<label class="selectlabel" for="filter_active">
				<?php echo JText::_('COM_USERS_FILTER_ACTIVE'); ?>
			</label>
			<select name="filter_active" id="filter_active">
				<option value="*"><?php echo JText::_('COM_USERS_FILTER_ACTIVE');?></option>
				<?php echo JHtml::_('select.options', UsersHelper::getActiveOptions(), 'value', 'text', $this->state->get('filter.active'));?>
			</select>

			<label class="selectlabel" for="filter_group_id">
				<?php echo JText::_('COM_USERS_FILTER_USERGROUP'); ?>
			</label>
			<select name="filter_group_id" id="filter_group_id">
				<option value=""><?php echo JText::_('COM_USERS_FILTER_USERGROUP');?></option>
				<?php echo JHtml::_('select.options', UsersHelper::getGroups(), 'value', 'text', $this->state->get('filter.group_id'));?>
			</select>

			<label class="selectlabel" for="filter_range">
				<?php echo JText::_('COM_USERS_FILTER_FILTER_DATE'); ?>
			</label>
			<select name="filter_range" id="filter_range" >
				<option value=""><?php echo JText::_('COM_USERS_OPTION_FILTER_DATE');?></option>
				<?php echo JHtml::_('select.options', Usershelper::getRangeOptions(), 'value', 'text', $this->state->get('filter.range'));?>
			</select>

			<button type="submit" id="filter-go">
				<?php echo JText::_('JSUBMIT'); ?></button>
		</div>
	</fieldset>
	<div class="clr"> </div>

	<table class="adminlist">
		<thead>
			<tr>
				<th class="checkmark-col">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
				</th>
				<th class="title">
					<?php echo JHtml::_('grid.sort', 'COM_USERS_HEADING_NAME', 'a.name', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap width-10">
					<?php echo JHtml::_('grid.sort', 'JGLOBAL_USERNAME', 'a.username', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap width-5">
					<?php echo JHtml::_('grid.sort', 'COM_USERS_HEADING_ENABLED', 'a.block', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap width-5">
					<?php echo JHtml::_('grid.sort', 'COM_USERS_HEADING_ACTIVATED', 'a.activation', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap width-10">
					<?php echo JText::_('COM_USERS_HEADING_GROUPS'); ?>
				</th>
				<th class="nowrap width-15">
					<?php echo JHtml::_('grid.sort', 'JGLOBAL_EMAIL', 'a.email', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap width-15">
					<?php echo JHtml::_('grid.sort', 'COM_USERS_HEADING_LAST_VISIT_DATE', 'a.lastvisitDate', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap width-15">
					<?php echo JHtml::_('grid.sort', 'COM_USERS_HEADING_REGISTRATION_DATE', 'a.registerDate', $listDirn, $listOrder); ?>
				</th>
				<th class="nowrap id-col">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>

		<tbody>
		<?php foreach ($this->items as $i => $item) :
			$canEdit   = $this->canDo->get('core.edit');
			$canChange = $loggeduser->authorise('core.edit.state',	'com_users');

			// If this group is super admin and this user is not super admin, $canEdit is false
			if ((!$loggeduser->authorise('core.admin')) && JAccess::check($item->id, 'core.admin'))
			{
				$canEdit   = false;
				$canChange = false;
			}
		?>
			<tr class="row<?php echo $i % 2; ?>">
				<td>
					<?php if ($canEdit) : ?>
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					<?php endif; ?>
				</td>
				<td class="break-word">
					<div class="fltrt">
						<?php echo JHtml::_('users.filterNotes', $item->note_count, $item->id); ?>
						<?php echo JHtml::_('users.notes', $item->note_count, $item->id); ?>
						<?php echo JHtml::_('users.addNote', $item->id); ?>
						<?php if ($item->requireReset == '1') : ?>
						<span class="label label-warning"><?php echo JText::_('COM_USERS_PASSWORD_RESET_REQUIRED'); ?></span>
						<?php endif; ?>
						<?php echo JHtml::_('users.notesModal', $item->note_count, $item->id); ?>
					</div>
					<?php if ($canEdit) : ?>
					<a href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id='.(int) $item->id); ?>" title="<?php echo JText::sprintf('COM_USERS_EDIT_USER', $this->escape($item->name)); ?>">
						<?php echo $this->escape($item->name); ?></a>
					<?php else : ?>
						<?php echo $this->escape($item->name); ?>
					<?php endif; ?>
					<?php if (JDEBUG) : ?>
						<div class="fltrt"><div class="button2-left smallsub"><div class="blank"><a href="<?php echo JRoute::_('index.php?option=com_users&view=debuguser&user_id='.(int) $item->id);?>">
						<?php echo JText::_('COM_USERS_DEBUG_USER');?></a></div></div></div>
					<?php endif; ?>
				</td>
				<td class="center break-word">
					<?php echo $this->escape($item->username); ?>
				</td>
				<td class="center">
					<?php if ($canChange) : ?>
						<?php if ($loggeduser->id != $item->id) : ?>
							<?php echo JHtml::_('grid.boolean', $i, !$item->block, 'users.unblock', 'users.block'); ?>
						<?php else : ?>
							<?php echo JHtml::_('grid.boolean', $i, !$item->block, 'users.block', null); ?>
						<?php endif; ?>
					<?php else : ?>
						<?php echo JText::_($item->block ? 'JNO' : 'JYES'); ?>
					<?php endif; ?>
				</td>
				<td class="center">
					<?php echo JHtml::_('grid.boolean', $i, !$item->activation, 'users.activate', null); ?>
				</td>
				<td class="center">
					<?php if (substr_count($item->group_names, "\n") > 1) : ?>
						<span class="hasTooltip" title="<?php echo JHtml::tooltipText(JText::_('COM_USERS_HEADING_GROUPS'), nl2br($item->group_names), 0); ?>"><?php echo JText::_('COM_USERS_USERS_MULTIPLE_GROUPS'); ?></span>
					<?php else : ?>
						<?php echo nl2br($item->group_names); ?>
					<?php endif; ?>
				</td>
				<td class="center break-word">
					<?php echo $this->escape($item->email); ?>
				</td>
				<td class="center">
					<?php if ($item->lastvisitDate != '0000-00-00 00:00:00') : ?>
						<?php echo JHtml::_('date', $item->lastvisitDate, 'Y-m-d H:i:s'); ?>
					<?php else:?>
						<?php echo JText::_('JNEVER'); ?>
					<?php endif;?>
				</td>
				<td class="center">
					<?php echo JHtml::_('date', $item->registerDate, 'Y-m-d H:i:s'); ?>
				</td>
				<td class="center">
					<?php echo (int) $item->id; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<?php //Load the batch processing form. ?>
	<?php echo $this->loadTemplate('batch'); ?>

	<?php echo $this->pagination->getListFooter(); ?>
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</div>
</form>
