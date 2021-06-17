<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
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

HTMLHelper::_('behavior.multiselect');

$user      = Factory::getUser();
$userId    = $user->id;

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrderingUrl = '';

$saveOrder = ($listOrder == 's.ordering');

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_workflow&task=stages.saveOrderAjax&workflow_id=' . (int) $this->workflowID . '&extension=' . $this->escape($this->extension) . '&' . Session::getFormToken() . '=1';
	HTMLHelper::_('draggablelist.draggable');
}
?>
<form action="<?php echo Route::_('index.php?option=com_workflow&view=stages&workflow_id=' . (int) $this->workflowID . '&extension=' . $this->extension); ?>" method="post" name="adminForm" id="adminForm">
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
				<?php if (empty($this->stages)) : ?>
					<div class="alert alert-info">
						<span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
						<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else: ?>
					<table class="table">
						<caption class="visually-hidden">
							<?php echo Text::_('COM_WORKFLOW_STAGES_TABLE_CAPTION'); ?>,
							<span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
							<span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
						</caption>
						<thead>
							<tr>
								<td class="w-1 text-center d-none d-md-table-cell">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</td>
								<th scope="col" class="w-1 text-center d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', '', 's.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
								</th>
								<th scope="col" class="w-1 text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 's.published', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-1 text-center">
									<?php echo Text::_('COM_WORKFLOW_DEFAULT'); ?>
								</th>
								<th scope="col">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_WORKFLOW_NAME', 's.title', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-5 d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_WORKFLOW_ID', 's.id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tbody class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>">
							<?php foreach ($this->stages as $i => $item):
								$edit = Route::_('index.php?option=com_workflow&task=stage.edit&id=' . $item->id . '&workflow_id=' . (int) $this->workflowID . '&extension=' . $this->extension);

								$canEdit    = $user->authorise('core.edit', $this->extension . '.stage.' . $item->id);
								$canCheckin = $user->authorise('core.admin', 'com_workflow') || $item->checked_out == $userId || is_null($item->checked_out);
								$canChange  = $user->authorise('core.edit.state', $this->extension . '.stage.' . $item->id) && $canCheckin;

								?>
								<tr class="row<?php echo $i % 2; ?>">
									<td class="text-center d-none d-md-table-cell">
										<?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', Text::_($item->title)); ?>
									</td>
									<td class="text-center d-none d-md-table-cell">
										<?php
										$iconClass = '';
										if (!$canChange)
										{
											$iconClass = ' inactive';
										}
										elseif (!$saveOrder)
										{
											$iconClass = ' inactive" title="' . Text::_('JORDERINGDISABLED');
										}
										?>
										<span class="sortable-handler<?php echo $iconClass ?>">
											<span class="icon-ellipsis-v" aria-hidden="true"></span>
										</span>
										<?php if ($canChange && $saveOrder) : ?>
											<input type="text" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order hidden">
										<?php endif; ?>
									</td>
									<td class="text-center">
										<?php echo HTMLHelper::_('jgrid.published', $item->published, $i, 'stages.', $canChange); ?>
									</td>
									<td class="text-center">
										<?php echo HTMLHelper::_('jgrid.isdefault', $item->default, $i, 'stages.', $canChange); ?>
									</td>
									<th scope="row">
										<?php if ($item->checked_out) : ?>
											<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'stages.', $canCheckin); ?>
										<?php endif; ?>
										<?php if ($canEdit) : ?>
											<a href="<?php echo $edit; ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape(Text::_($item->title)); ?>">
												<?php echo $this->escape(Text::_($item->title)); ?>
											</a>
											<div class="small"><?php echo $this->escape(Text::_($item->description)); ?></div>
										<?php else: ?>
											<?php echo $this->escape(Text::_($item->title)); ?>
											<div class="small"><?php echo $this->escape(Text::_($item->description)); ?></div>
										<?php endif; ?>
									</th>
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
