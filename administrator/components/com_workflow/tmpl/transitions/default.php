<?php
/**
 * Transitions View for a Workflow Component.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0.0
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Workflow\Workflow;

HTMLHelper::_('behavior.tooltip');
HTMLHelper::_('behavior.multiselect');

$user	= Factory::getUser();

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrderingUrl = '';

$saveOrder = ($listOrder == 't.ordering');

$isCore = $this->workflow->core;

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_workflow&task=transitions.saveOrderAjax&workflow_id=' . (int) $this->workflowID . '&extension=' . $this->escape($this->extension) . '&' . Session::getFormToken() . '=1';
	HTMLHelper::_('draggablelist.draggable');
}
?>
<form action="<?php echo Route::_('index.php?option=com_workflow&view=transitions&workflow_id=' . (int) $this->workflowID . '&extension=' . $this->extension); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<?php if (!empty($this->sidebar)) : ?>
		<div id="j-sidebar-container" class="col-md-2">
			<?php echo $this->sidebar; ?>
		</div>
		<?php endif; ?>
        <div class="<?php if (!empty($this->sidebar)) {echo 'col-md-10'; } else { echo 'col-md-12'; } ?>">
			<div id="j-main-container" class="j-main-container">
				<?php
					// Search tools bar
					echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
				?>
				<?php if (empty($this->transitions)) : ?>
					<div class="alert alert-warning">
						<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else: ?>
					<table class="table">
						<caption id="captionTable" class="sr-only">
							<?php echo Text::_('COM_WORKFLOW_TRANSITIONS_TABLE_CAPTION'); ?>, <?php echo Text::_('JGLOBAL_SORTED_BY'); ?>
						</caption>
						<thead>
							<tr>
								<td style="width:1%" class="text-center hidden-sm-down">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</td>
								<th scope="col" style="width:1%" class="text-center d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', '', 't.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
								</th>
								<th scope="col" style="width:1%" class="text-center hidden-sm-down">
									<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 't.published', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:20%">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_WORKFLOW_NAME', 't.title', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:20%" class="d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_WORKFLOW_FROM_STAGE', 'from_stage', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:20%" class="d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_WORKFLOW_TO_STAGE', 'to_stage', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:3%" class="d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_WORKFLOW_ID', 't.id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tbody class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>">
							<?php foreach ($this->transitions as $i => $item):
								$edit = Route::_('index.php?option=com_workflow&task=transition.edit&id=' . $item->id . '&workflow_id=' . (int) $this->workflowID . '&extension=' . $this->extension);

								$canEdit    = $user->authorise('core.edit', $this->extension . '.transition.' . $item->id) && !$isCore;
								// @TODO set proper checkin fields
								$canCheckin = true || $user->authorise('core.admin', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
								$canChange  = $user->authorise('core.edit.state', $this->extension . '.transition.' . $item->id) && $canCheckin && !$isCore;								?>
								<tr class="row<?php echo $i % 2; ?>">
									<td class="order text-center hidden-sm-down">
										<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
									</td>
									<td class="order text-center d-none d-md-table-cell">
										<?php
										$iconClass = '';
										if (!$canChange)
										{
											$iconClass = ' inactive';
										}
										elseif (!$saveOrder)
										{
											$iconClass = ' inactive tip-top hasTooltip" title="' . HTMLHelper::_('tooltipText', 'JORDERINGDISABLED');
										}
										?>
										<span class="sortable-handler<?php echo $iconClass ?>">
											<span class="fa fa-ellipsis-v" aria-hidden="true"></span>
										</span>
										<?php if ($canChange && $saveOrder) : ?>
											<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order">
										<?php endif; ?>									</td>
									<td class="text-center">
										<div class="btn-group">
											<?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'transitions.', $canChange); ?>
										</div>
									</td>
									<th scope="row">
										<?php if ($canEdit) : ?>
											<a href="<?php echo $edit; ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape(addslashes(Text::_($item->title))); ?>">
												<?php echo $this->escape(Text::_($item->title)); ?>
											</a>
											<div class="small"><?php echo $this->escape(Text::_($item->description)); ?></div>
										<?php else: ?>
											<?php echo $this->escape(Text::_($item->title)); ?>
											<div class="small"><?php echo $this->escape(Text::_($item->description)); ?></div>
										<?php endif; ?>
									</th>
									<td class="nowrap">
										<?php if ($item->from_stage_id < 0): ?>
											<?php echo Text::_('JALL'); ?>
										<?php else : ?>
											<?php
											if ($item->from_condition == Workflow::CONDITION_ARCHIVED):
												$icon = 'icon-archive';
												$condition = Text::_('JARCHIVED');
											elseif ($item->from_condition == Workflow::CONDITION_TRASHED):
												$icon = 'icon-trash';
												$condition = Text::_('JTRASHED');
											elseif ($item->from_condition == Workflow::CONDITION_PUBLISHED):
												$icon = 'icon-publish';
												$condition = Text::_('JPUBLISHED');
											elseif ($item->from_condition == Workflow::CONDITION_UNPUBLISHED):
												$icon = 'icon-unpublish';
												$condition = Text::_('JUNPUBLISHED');
											endif; ?>
											<span class="<?php echo $icon; ?>" aria-hidden="true"></span>
											<span class="sr-only"><?php echo Text::_('COM_WORKFLOW_CONDITION') . $condition; ?></span>
											<?php echo ' ' . $this->escape(Text::_($item->from_stage)); ?>
										<?php endif; ?>
									</td>
									<td class="nowrap">
										<?php
										if ($item->to_condition == Workflow::CONDITION_ARCHIVED):
											$icon = 'icon-archive';
											$condition = Text::_('JARCHIVED');
										elseif ($item->to_condition == Workflow::CONDITION_TRASHED):
											$icon = 'icon-trash';
											$condition = Text::_('JTRASHED');
										elseif ($item->to_condition == Workflow::CONDITION_PUBLISHED):
											$icon = 'icon-publish';
											$condition = Text::_('JPUBLISHED');
										elseif ($item->to_condition == Workflow::CONDITION_UNPUBLISHED):
											$icon = 'icon-unpublish';
											$condition = Text::_('JUNPUBLISHED');
										endif; ?>
										<span class="<?php echo $icon; ?>" aria-hidden="true"></span>
										<span class="sr-only"><?php echo Text::_('COM_WORKFLOW_CONDITION') . $condition; ?></span>
										<?php echo ' ' . $this->escape(Text::_($item->to_stage)); ?>
									</td>
									<td class="d-none d-md-table-cell">
										<?php echo (int) $item->id; ?>
									</td>
								</tr>
							<?php endforeach ?>
						</tbody>
					</table>
					<?php // load the pagination. ?>
					<?php echo $this->pagination->getListFooter(); ?>
				<?php endif; ?>
				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<input type="hidden" name="workflow_id" value="<?php echo (int) $this->workflowID ?>">
				<input type="hidden" name="extension" value="<?php echo $this->extension ?>">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
