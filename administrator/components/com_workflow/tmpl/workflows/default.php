<?php
/**
 * Items Model for a Workflow Component.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since  __DEPLOY_VERSION__
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

JHtml::_('behavior.tooltip');

$columns = 11;

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

$saveOrder = $listOrder == 'w.ordering';

$orderingColumn = 'created';

if (strpos($listOrder, 'modified') !== false)
{
    $orderingColumn = 'modified';
}

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_workflow&task=workflows.saveOrderAjax&tmpl=component' . JSession::getFormToken() . '=1';
	JHtml::_('draggablelist.draggable');
}

$extension = $this->escape($this->state->get('filter.extension'));

$user = Factory::getUser();
$userId = $user->id;
?>
<form action="<?php echo JRoute::_('index.php?option=com_workflow&extension=' . $extension); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div id="j-sidebar-container" class="col-md-2">
			<?php echo $this->sidebar; ?>
		</div>
		<div class="col-md-10">
			<div id="j-main-container" class="j-main-container">
				<?php
					// Search tools bar
					echo \JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
				?>
				<?php if (empty($this->workflows)) : ?>
					<div class="alert alert-warning alert-no-items">
						<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else: ?>
					<table class="table table-striped" id="emailList">
						<thead>
							<tr>
								<th style="width:1%" class="nowrap text-center hidden-sm-down">
									<?php echo JHtml::_('searchtools.sort', '', 'w.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
								</th>
								<th style="width:1%" class="nowrap text-center hidden-sm-down">
									<?php echo JHtml::_('grid.checkall'); ?>
								</th>
								<th style="width:1%" class="nowrap text-center hidden-sm-down">
									<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'w.condition', $listDirn, $listOrder); ?>
								</th>
								<th style="width:10%" class="nowrap hidden-sm-down">
									<?php echo JHtml::_('searchtools.sort', 'COM_WORKFLOW_TITLE', 'w.title', $listDirn, $listOrder); ?>
								</th>
								<th style="width:10%" class="nowrap text-center hidden-sm-down">
									<?php echo JText::_('COM_WORKFLOW_STATES'); ?>
								</th>
								<th style="width:10%" class="text-center nowrap hidden-sm-down">
									<?php echo JText::_('COM_WORKFLOW_DEFAULT'); ?>
								</th>
								<th style="width:3%" class="nowrap text-center hidden-sm-down">
									<span class="fa fa-circle-o text-warning hasTooltip" aria-hidden="true" title="<?php echo JText::_('COM_WORKFLOW_COUNT_STATES'); ?>">
										<span class="sr-only"><?php echo JText::_('COM_WORKFLOW_COUNT_STATES'); ?></span>
									</span>
								</th>
								<th style="width:3%" class="nowrap text-center hidden-sm-down">
									<span class="fa fa-arrows-h text-info hasTooltip" aria-hidden="true" title="<?php echo JText::_('COM_WORKFLOW_COUNT_TRANSITIONS'); ?>">
										<span class="sr-only"><?php echo JText::_('COM_WORKFLOW_COUNT_TRANSITIONS'); ?></span>
									</span>
								</th>
								<th style="width:10%" class="nowrap hidden-sm-down text-center">
									<?php echo JHtml::_('searchtools.sort', 'COM_WORKFLOW_DATE_' . strtoupper($orderingColumn), 'w.' . $orderingColumn, $listDirn, $listOrder); ?>
								</th>
								<th style="width:10%" class="nowrap text-center hidden-sm-down">
									<?php echo JHtml::_('searchtools.sort', 'COM_WORKFLOW_AUTHOR', 'w.created_by', $listDirn, $listOrder); ?>
								</th>
								<th style="width:10%" class="nowrap text-right hidden-sm-down">
									<?php echo JHtml::_('searchtools.sort', 'COM_WORKFLOW_ID', 'w.id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tbody <?php if ($saveOrder) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="false"<?php endif; ?>>
						<?php foreach ($this->workflows as $i => $item):
							$states = JRoute::_('index.php?option=com_workflow&view=states&workflow_id=' . $item->id . '&extension=' . $extension);
							$transitions = JRoute::_('index.php?option=com_workflow&view=transitions&workflow_id=' . $item->id . '&extension=' . $extension);
							$edit = JRoute::_('index.php?option=com_workflow&task=workflow.edit&id=' . $item->id);

							$canEdit    = $user->authorise('core.edit', $extension . '.workflow.' . $item->id);
							// @TODO set proper checkin fields
							$canCheckin = true || $user->authorise('core.admin', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
							$canEditOwn = $user->authorise('core.edit.own',   $extension . '.workflow.' . $item->id) && $item->created_by == $userId;
							$canChange  = $user->authorise('core.edit.state', $extension . '.workflow.' . $item->id) && $canCheckin;
							?>
							<tr class="row<?php echo $i % 2; ?>" data-dragable-group="0">
								<td class="order nowrap text-center hidden-sm-down">
									<?php
									$iconClass = '';
									if (!$canChange)
									{
										$iconClass = ' inactive';
									}
									elseif (!$saveOrder)
									{
										$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::_('tooltipText', 'JORDERINGDISABLED');
									}
									?>
									<span class="sortable-handler<?php echo $iconClass ?>">
										<span class="icon-menu" aria-hidden="true"></span>
									</span>
									<?php if ($canChange && $saveOrder) : ?>
										<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order">
									<?php endif; ?>
								</td>
								<td class="order nowrap text-center hidden-sm-down">
									<?php echo JHtml::_('grid.id', $i, $item->id); ?>
								</td>
								<td class="text-center">
									<div class="btn-group">
										<?php echo JHtml::_('jgrid.published', $item->published, $i, 'workflows.', $canChange); ?>
									</div>
								</td>
								<td>
									<?php if ($canEdit || $canEditOwn) : ?>
										<?php $editIcon = '<span class="fa fa-pencil-square mr-2" aria-hidden="true"></span>'; ?>
										<a href="<?php echo $edit; ?>" title="<?php echo JText::_('JACTION_EDIT'); ?> <?php echo $this->escape(addslashes($item->title)); ?>">
											<?php echo $editIcon; ?><?php echo $item->title; ?>
										</a>
									<?php else: ?>
										<?php echo $item->title; ?>
									<?php endif; ?>
								</td>
								<td class="text-center">
									<a href="<?php echo $states; ?>"><?php echo \JText::_('COM_WORKFLOW_MANAGE'); ?></a>
								</td>
								<td class="text-center hidden-sm-down">
									<?php echo JHtml::_('jgrid.isdefault', $item->default, $i, 'workflows.', $canChange); ?>
								</td>
								<td class="text-center btns hidden-sm-down">
									<a class="badge <?php echo ($item->count_states > 0) ? 'badge-warning' : 'badge-secondary'; ?>" title="<?php echo JText::_('COM_WORKFLOW_COUNT_STATES'); ?>" href="<?php echo JRoute::_('index.php?option=com_workflow&view=states&workflow_id=' . (int) $item->id . '&extension=' . $extension); ?>">
										<?php echo $item->count_states; ?></a>
								</td>
								<td class="text-center btns hidden-sm-down">
									<a class="badge <?php echo ($item->count_transitions > 0) ? 'badge-info' : 'badge-secondary'; ?>" title="<?php echo JText::_('COM_WORKFLOW_COUNT_TRANSITIONS'); ?>" href="<?php echo JRoute::_('index.php?option=com_workflow&view=transitions&workflow_id=' . (int) $item->id . '&extension=' . $extension); ?>">
										<?php echo $item->count_transitions; ?></a>
								</td>
								<td class="text-center">
									<?php
									$date = $item->{$orderingColumn};
									echo $date > 0 ? JHtml::_('date', $date, JText::_('DATE_FORMAT_LC4')) : '-';
									?>
								</td>
								<td class="text-center">
									<?php echo empty($item->name) ? JText::_('COM_WORKFLOW_NA') : $item->name; ?>
								</td>
								<td class="text-right">
									<?php echo $item->id; ?>
								</td>
							</tr>
						<?php endforeach ?>
						</tbody>
						<tfoot>
							<tr>
								<td colspan="<?php echo $columns; ?>">
								<?php echo $this->pagination->getListFooter(); ?>
								</td>
							</tr>
						</tfoot>
					</table>
				<?php endif; ?>
				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<input type="hidden" name="extension" value="<?php echo $extension ?>">
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
