<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.multiselect');
JHtml::_('behavior.tabstate');

$user      = JFactory::getUser();
$userId    = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$saveOrder = $listOrder == 's.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_installer&task=updatesites.saveOrderAjax&tmpl=component' . JSession::getFormToken() . '=1';
	\JHtml::_('draggablelist.draggable');
}
?>
<div id="installer-manage" class="clearfix">
	<form action="<?php echo JRoute::_('index.php?option=com_installer&view=updatesites'); ?>" method="post" name="adminForm" id="adminForm">
		<div class="row">
			<div id="j-sidebar-container" class="col-md-2">
				<?php echo $this->sidebar; ?>
			</div>
			<div class="col-md-10">
				<div id="j-main-container" class="j-main-container">
					<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
					<?php if (empty($this->items)) : ?>
					<div class="alert alert-warning alert-no-items">
						<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
					<?php else : ?>
					<table class="table table-striped">
						<thead>
							<tr>
								<th style="width:1%" class="nowrap text-center hidden-sm-down">
									<?php echo JHtml::_('searchtools.sort', '', 's.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
								</th>
								<th style="width:1%" class="text-center">
									<?php echo JHtml::_('grid.checkall'); ?>
								</th>
								<th style="width:1%" class="nowrap text-center">
									<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'enabled', $listDirn, $listOrder); ?>
								</th>
								<th class="nowrap">
									<?php echo JHtml::_('searchtools.sort', 'COM_INSTALLER_HEADING_UPDATESITE_NAME', 'update_site_name', $listDirn, $listOrder); ?>
								</th>
								<th style="width:20%" class="nowrap hidden-sm-down text-center">
									<?php echo JHtml::_('searchtools.sort', 'COM_INSTALLER_HEADING_NAME', 'name', $listDirn, $listOrder); ?>
								</th>
								<th style="width:10%" class="hidden-sm-down text-center">
									<?php echo JHtml::_('searchtools.sort', 'COM_INSTALLER_HEADING_LOCATION', 'client_translated', $listDirn, $listOrder); ?>
								</th>
								<th style="width:10%" class="hidden-sm-down text-center">
									<?php echo JHtml::_('searchtools.sort', 'COM_INSTALLER_HEADING_TYPE', 'type_translated', $listDirn, $listOrder); ?>
								</th>
								<th style="width:10%" class="hidden-sm-down text-center">
									<?php echo JHtml::_('searchtools.sort', 'COM_INSTALLER_HEADING_FOLDER', 'folder_translated', $listDirn, $listOrder); ?>
								</th>
								<th style="width:5%" class="nowrap hidden-sm-down text-center">
									<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'update_site_id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td colspan="8">
									<?php echo $this->pagination->getListFooter(); ?>
								</td>
							</tr>
						</tfoot>
						<tbody <?php if ($saveOrder) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" data-nested="true"<?php endif; ?>>
						<?php foreach ($this->items as $i => $item) :
							$canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
							$canEdit    = $user->authorise('core.edit', 'com_installer');
							$canChange  = $user->authorise('core.edit.state', 'com_installer') && $canCheckin;
							?>
							<tr class="row<?php echo $i % 2; if ($item->enabled == 2) echo ' protected'; ?>" data-dragable-group="0">
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
									<span class="sortable-handler <?php echo $iconClass ?>">
											<span class="icon-menu" aria-hidden="true"></span>
										</span>
									<?php if ($canChange && $saveOrder) : ?>
										<input type="text" style="display:none" name="order[]" size="5"
											   value="<?php echo $item->ordering; ?>" class="width-20 text-area-order">
									<?php endif; ?>
								</td>
								<td class="text-center">
									<?php echo JHtml::_('grid.id', $i, $item->update_site_id); ?>
								</td>
								<td class="text-center">
									<?php if (!$item->element) : ?>
										<strong>X</strong>
									<?php else : ?>
										<?php echo JHtml::_('InstallerHtml.Updatesites.state', $item->enabled, $i, $item->enabled < 2, 'cb'); ?>
									<?php endif; ?>
								</td>
								<td>
									<label for="cb<?php echo $i; ?>">
										<?php if ($item->checked_out) : ?>
											<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'updatesites.', $canCheckin); ?>
										<?php endif; ?>
										<?php if ($canEdit) : ?>
											<?php $editIcon = $item->checked_out ? '' : '<span class="fa fa-pencil-square mr-2" aria-hidden="true"></span>'; ?>
											<a class="hasTooltip" href="<?php echo JRoute::_('index.php?option=com_installer&task=updatesite.edit&update_site_id=' . (int) $item->update_site_id); ?>" title="<?php echo JText::_('JACTION_EDIT'); ?> <?php echo $this->escape(addslashes($item->name)); ?>">
												<?php echo $editIcon; ?><?php echo $this->escape($item->name); ?></a>
										<?php else : ?>
											<?php echo $this->escape($item->name); ?>
										<?php endif; ?>
										<br>
										<span class="small break-word">
											<a href="<?php echo $item->location; ?>" target="_blank" rel="noopener noreferrer"><?php echo $this->escape($item->location); ?></a>
										</span>
									</label>
								</td>
								<td class="hidden-sm-down text-center">
									<span class="bold hasTooltip" title="<?php echo JHtml::_('tooltipText', $item->name, $item->description, 0); ?>">
										<?php echo $item->name; ?>
									</span>
								</td>
								<td class="hidden-sm-down text-center">
									<?php echo $item->client_translated; ?>
								</td>
								<td class="hidden-sm-down text-center">
									<?php echo $item->type_translated; ?>
								</td>
								<td class="hidden-sm-down text-center">
									<?php echo $item->folder_translated; ?>
								</td>
								<td class="hidden-sm-down text-center">
									<?php echo $item->update_site_id; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
					<?php endif; ?>
					<input type="hidden" name="task" value="">
					<input type="hidden" name="boxchecked" value="0">
					<?php echo JHtml::_('form.token'); ?>
				</div>
			</div>
		</div>
	</form>
</div>
