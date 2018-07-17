<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Access\Access;

HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('behavior.tabstate');

$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));
$loggeduser = Factory::getUser();
$debugUsers = $this->state->get('params')->get('debugUsers', 1);
?>
<form action="<?php echo Route::_('index.php?option=com_users&view=users'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div id="j-sidebar-container" class="col-md-2">
			<?php echo $this->sidebar; ?>
		</div>
		<div class="col-md-10">
			<div id="j-main-container" class="j-main-container">
				<?php
				// Search tools bar
				echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
				?>
				<?php if (empty($this->items)) : ?>
					<joomla-alert type="warning"><?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></joomla-alert>
				<?php else : ?>
					<table class="table" id="userList">
						<thead>
							<tr>
								<th style="width:1%" class="nowrap text-center">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</th>
								<th class="nowrap">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_USERS_HEADING_NAME', 'a.name', $listDirn, $listOrder); ?>
								</th>
								<th style="width:10%" class="nowrap text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_USERNAME', 'a.username', $listDirn, $listOrder); ?>
								</th>
								<th style="width:5%" class="nowrap text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_USERS_HEADING_ENABLED', 'a.block', $listDirn, $listOrder); ?>
								</th>
								<th style="width:5%" class="nowrap text-center d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_USERS_HEADING_ACTIVATED', 'a.activation', $listDirn, $listOrder); ?>
								</th>
								<th style="width:12%" class="nowrap text-center">
									<?php echo Text::_('COM_USERS_HEADING_GROUPS'); ?>
								</th>
								<th style="width:12%" class="nowrap d-none d-lg-table-cell text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGLOBAL_EMAIL', 'a.email', $listDirn, $listOrder); ?>
								</th>
								<th style="width:12%" class="nowrap d-none d-lg-table-cell text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_USERS_HEADING_LAST_VISIT_DATE', 'a.lastvisitDate', $listDirn, $listOrder); ?>
								</th>
								<th style="width:12%" class="nowrap d-none d-lg-table-cell text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_USERS_HEADING_REGISTRATION_DATE', 'a.registerDate', $listDirn, $listOrder); ?>
								</th>
								<th style="width:5%" class="nowrap d-none d-md-table-cell text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td colspan="10">
									<?php echo $this->pagination->getListFooter(); ?>
								</td>
							</tr>
						</tfoot>
						<tbody>
						<?php foreach ($this->items as $i => $item) :
							$canEdit   = $this->canDo->get('core.edit');
							$canChange = $loggeduser->authorise('core.edit.state',	'com_users');

							// If this group is super admin and this user is not super admin, $canEdit is false
							if ((!$loggeduser->authorise('core.admin')) && Access::check($item->id, 'core.admin'))
							{
								$canEdit   = false;
								$canChange = false;
							}
						?>
							<tr class="row<?php echo $i % 2; ?>">
								<td class="text-center">
									<?php if ($canEdit || $canChange) : ?>
										<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
									<?php endif; ?>
								</td>
								<td>
									<div class="name break-word">
									<?php if ($canEdit) : ?>
										<a href="<?php echo Route::_('index.php?option=com_users&task=user.edit&id=' . (int) $item->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo Text::sprintf('COM_USERS_EDIT_USER', $this->escape($item->name)); ?>">
											<span class="fa fa-pencil-square mr-2" aria-hidden="true"></span><?php echo $this->escape($item->name); ?></a>
									<?php else : ?>
										<?php echo $this->escape($item->name); ?>
									<?php endif; ?>
									</div>
									<div class="btn-group">
										<?php echo HTMLHelper::_('users.addNote', $item->id); ?>
										<?php if ($item->note_count > 0) : ?>
										<button type="button" class="btn btn-secondary btn-sm dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
											<span class="sr-only">Toggle Dropdown</span>
										</button>
										<div class="dropdown-menu">
											<?php echo HTMLHelper::_('users.filterNotes', $item->note_count, $item->id); ?>
											<?php echo HTMLHelper::_('users.notes', $item->note_count, $item->id); ?>
										</div>
										<?php endif; ?>
									</div>
									<?php echo HTMLHelper::_('users.notesModal', $item->note_count, $item->id); ?>
									<?php if ($item->requireReset == '1') : ?>
										<span class="badge badge-warning"><?php echo Text::_('COM_USERS_PASSWORD_RESET_REQUIRED'); ?></span>
									<?php endif; ?>
									<?php if ($debugUsers) : ?>
										<div class="small"><a href="<?php echo Route::_('index.php?option=com_users&view=debuguser&user_id=' . (int) $item->id); ?>">
										<?php echo Text::_('COM_USERS_DEBUG_USER'); ?></a></div>
									<?php endif; ?>
								</td>
								<td class="break-word text-center">
									<?php echo $this->escape($item->username); ?>
								</td>
								<td class="text-center">
									<?php if ($canChange) : ?>
										<?php
										$self = $loggeduser->id == $item->id;
										echo HTMLHelper::_('jgrid.state', JHtmlUsers::blockStates($self), $item->block, $i, 'users.', !$self);
										?>
									<?php else : ?>
										<?php echo Text::_($item->block ? 'JNO' : 'JYES'); ?>
									<?php endif; ?>
								</td>
								<td class="text-center d-none d-md-table-cell">
									<?php
									$activated = empty( $item->activation) ? 0 : 1;
									echo HTMLHelper::_('jgrid.state', JHtmlUsers::activateStates(), $activated, $i, 'users.', (boolean) $activated);
									?>
								</td>
								<td class="text-center">
									<?php if (substr_count($item->group_names, "\n") > 1) : ?>
										<span class="hasTooltip" title="<?php echo HTMLHelper::_('tooltipText', Text::_('COM_USERS_HEADING_GROUPS'), nl2br($item->group_names), 0); ?>"><?php echo Text::_('COM_USERS_USERS_MULTIPLE_GROUPS'); ?></span>
									<?php else : ?>
										<?php echo nl2br($item->group_names); ?>
									<?php endif; ?>
								</td>
								<td class="d-none d-lg-table-cell break-word text-center">
									<?php echo PunycodeHelper::emailToUTF8($this->escape($item->email)); ?>
								</td>
								<td class="d-none d-lg-table-cell text-center">
									<?php if ($item->lastvisitDate != $this->db->getNullDate()) : ?>
										<?php echo HTMLHelper::_('date', $item->lastvisitDate, 'Y-m-d H:i:s'); ?>
									<?php else : ?>
										<?php echo Text::_('JNEVER'); ?>
									<?php endif; ?>
								</td>
								<td class="d-none d-lg-table-cell text-center">
									<?php echo HTMLHelper::_('date', $item->registerDate, 'Y-m-d H:i:s'); ?>
								</td>
								<td class="d-none d-md-table-cell text-center">
									<?php echo (int) $item->id; ?>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					<?php // Load the batch processing form if user is allowed ?>
					<?php if ($loggeduser->authorise('core.create', 'com_users')
						&& $loggeduser->authorise('core.edit', 'com_users')
						&& $loggeduser->authorise('core.edit.state', 'com_users')) : ?>
						<?php echo HTMLHelper::_(
							'bootstrap.renderModal',
							'collapseModal',
							array(
								'title'  => Text::_('COM_USERS_BATCH_OPTIONS'),
								'footer' => $this->loadTemplate('batch_footer'),
							),
							$this->loadTemplate('batch_body')
						); ?>
					<?php endif; ?>
				<?php endif; ?>

				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
