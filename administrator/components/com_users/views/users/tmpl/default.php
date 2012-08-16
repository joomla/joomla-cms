<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('behavior.modal');

$canDo = UsersHelper::getActions();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$loggeduser = JFactory::getUser();
?>

<form action="<?php echo JRoute::_('index.php?option=com_users&view=users');?>" method="post" name="adminForm" id="adminForm">
	<div class="row-fluid">
		<!-- Begin Sidebar -->
		<div id="sidebar" class="span2">
			<div class="sidebar-nav">
				<?php
					// Display the submenu position modules
					$this->modules = JModuleHelper::getModules('submenu');
					foreach ($this->modules as $module) {
						$output = JModuleHelper::renderModule($module);
						$params = new JRegistry;
						$params->loadString($module->params);
						echo $output;
					}
				?>
				<hr />
				<div class="filter-select">
					<h4 class="page-header"><?php echo JText::_('JSEARCH_FILTER_LABEL');?></h4>
					<select name="filter_state" class="span12 small" onchange="this.form.submit()">
						<option value="*"><?php echo JText::_('COM_USERS_FILTER_STATE');?></option>
						<?php echo JHtml::_('select.options', UsersHelper::getStateOptions(), 'value', 'text', $this->state->get('filter.state'));?>
					</select>
					<hr class="hr-condensed" />
					<select name="filter_active" class="span12 small" onchange="this.form.submit()">
						<option value="*"><?php echo JText::_('COM_USERS_FILTER_ACTIVE');?></option>
						<?php echo JHtml::_('select.options', UsersHelper::getActiveOptions(), 'value', 'text', $this->state->get('filter.active'));?>
					</select>
					<hr class="hr-condensed" />
					<select name="filter_group_id" class="span12 small" onchange="this.form.submit()">
						<option value=""><?php echo JText::_('COM_USERS_FILTER_USERGROUP');?></option>
						<?php echo JHtml::_('select.options', UsersHelper::getGroups(), 'value', 'text', $this->state->get('filter.group_id'));?>
					</select>
					<hr class="hr-condensed" />
					<select name="filter_range" id="filter_range" class="span12 small" onchange="this.form.submit()">
						<option value=""><?php echo JText::_('COM_USERS_OPTION_FILTER_DATE');?></option>
						<?php echo JHtml::_('select.options', Usershelper::getRangeOptions(), 'value', 'text', $this->state->get('filter.range'));?>
					</select>
				</div>
			</div>
		</div>
		<!-- End Sidebar -->
		<!-- Begin Content -->
		<div class="span10">
			<div id="filter-bar" class="btn-toolbar">
				<div class="filter-search btn-group pull-left">
					<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('COM_USERS_SEARCH_USERS'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" title="<?php echo JText::_('COM_USERS_SEARCH_USERS'); ?>" />
				</div>
				<div class="btn-group pull-left">
					<button type="submit" class="btn tip" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
					<button type="button" class="btn tip" onclick="document.id('filter_search').value='';this.form.submit();" title="<?php echo JText::_('JSEARCH_RESET'); ?>"><i class="icon-remove"></i></button>
				</div>
			</div>
			<div class="clearfix"> </div>
			<table class="table table-striped">
				<thead>
					<tr>
						<th width="1%">
							<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
						</th>
						<th class="left">
							<?php echo JHtml::_('grid.sort', 'COM_USERS_HEADING_NAME', 'a.name', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" width="10%">
							<?php echo JHtml::_('grid.sort', 'JGLOBAL_USERNAME', 'a.username', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" width="5%">
							<?php echo JHtml::_('grid.sort', 'COM_USERS_HEADING_ENABLED', 'a.block', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" width="5%">
							<?php echo JHtml::_('grid.sort', 'COM_USERS_HEADING_ACTIVATED', 'a.activation', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" width="10%">
							<?php echo JText::_('COM_USERS_HEADING_GROUPS'); ?>
						</th>
						<th class="nowrap" width="15%">
							<?php echo JHtml::_('grid.sort', 'JGLOBAL_EMAIL', 'a.email', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" width="10%">
							<?php echo JHtml::_('grid.sort', 'COM_USERS_HEADING_LAST_VISIT_DATE', 'a.lastvisitDate', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" width="10%">
							<?php echo JHtml::_('grid.sort', 'COM_USERS_HEADING_REGISTRATION_DATE', 'a.registerDate', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap" width="3%">
							<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
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
				<?php foreach ($this->items as $i => $item) :
					$canEdit	= $canDo->get('core.edit');
					$canChange	= $loggeduser->authorise('core.edit.state',	'com_users');
					// If this group is super admin and this user is not super admin, $canEdit is false
					if ((!$loggeduser->authorise('core.admin')) && JAccess::check($item->id, 'core.admin')) {
						$canEdit	= false;
						$canChange	= false;
					}
				?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="center">
							<?php if ($canEdit) : ?>
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							<?php endif; ?>
						</td>
						<td>
							<?php if ($canEdit) : ?>
							<a href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id='.(int) $item->id); ?>" title="<?php echo JText::sprintf('COM_USERS_EDIT_USER', $this->escape($item->name)); ?>">
								<?php echo $this->escape($item->name); ?></a>
							<?php else : ?>
								<?php echo $this->escape($item->name); ?>
							<?php endif; ?>
							<div>
								<?php echo JHtml::_('users.filterNotes', $item->note_count, $item->id); ?>
								<?php echo JHtml::_('users.notes', $item->note_count, $item->id); ?>
								<?php echo JHtml::_('users.addNote', $item->id); ?>
							</div>
							<?php if (JDEBUG) : ?>
								<div class="small"><a href="<?php echo JRoute::_('index.php?option=com_users&view=debuguser&user_id='.(int) $item->id);?>">
								<?php echo JText::_('COM_USERS_DEBUG_USER');?></a></div>
							<?php endif; ?>
						</td>
						<td class="center">
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
								<span class="hasTip" title="<?php echo JText::_('COM_USERS_HEADING_GROUPS').'::'.nl2br($item->group_names); ?>"><?php echo JText::_('COM_USERS_USERS_MULTIPLE_GROUPS'); ?></span>
							<?php else : ?>
								<?php echo nl2br($item->group_names); ?>
							<?php endif; ?>
						</td>
						<td class="center">
							<?php echo $this->escape($item->email); ?>
						</td>
						<td class="center">
							<?php if ($item->lastvisitDate != '0000-00-00 00:00:00'):?>
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

			<div>
				<input type="hidden" name="task" value="" />
				<input type="hidden" name="boxchecked" value="0" />
				<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
				<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</div>
		<!-- End Content -->
	</div>
</form>
