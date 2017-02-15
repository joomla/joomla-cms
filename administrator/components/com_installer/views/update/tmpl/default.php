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

JHtml::_('bootstrap.tooltip');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<div id="installer-update" class="clearfix">
	<form action="<?php echo JRoute::_('index.php?option=com_installer&view=update'); ?>" method="post" name="adminForm" id="adminForm">
		<div class="row">
			<div id="j-sidebar-container" class="col-md-2">
				<?php echo $this->sidebar; ?>
			</div>
			<div class="col-md-10">
				<div id="j-main-container" class="j-main-container">
					<?php if ($this->showMessage) : ?>
						<?php echo $this->loadTemplate('message'); ?>
					<?php endif; ?>

					<?php if ($this->ftp) : ?>
						<?php echo $this->loadTemplate('ftp'); ?>
					<?php endif; ?>

					<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
					<?php if (empty($this->items)) : ?>
						<div class="alert alert-info alert-no-items">
							<?php echo JText::_('COM_INSTALLER_MSG_UPDATE_NOUPDATES'); ?>
						</div>
					<?php else : ?>
						<table class="table table-striped">
							<thead>
							<tr>
								<th width="1%" class="nowrap">
									<?php echo JHtml::_('grid.checkall'); ?>
								</th>
								<th class="nowrap">
									<?php echo JHtml::_('searchtools.sort', 'COM_INSTALLER_HEADING_NAME', 'u.name', $listDirn, $listOrder); ?>
								</th>
								<th class="nowrap center">
									<?php echo JHtml::_('searchtools.sort', 'COM_INSTALLER_HEADING_LOCATION', 'client_translated', $listDirn, $listOrder); ?>
								</th>
								<th class="nowrap center">
									<?php echo JHtml::_('searchtools.sort', 'COM_INSTALLER_HEADING_TYPE', 'type_translated', $listDirn, $listOrder); ?>
								</th>
								<th class="nowrap hidden-sm-down">
									<?php echo JText::_('COM_INSTALLER_CURRENT_VERSION'); ?>
								</th>
								<th class="nowrap center">
									<?php echo JText::_('COM_INSTALLER_NEW_VERSION'); ?>
								</th>
								<th class="nowrap hidden-sm-down">
									<?php echo JHtml::_('searchtools.sort', 'COM_INSTALLER_HEADING_FOLDER', 'folder_translated', $listDirn, $listOrder); ?>
								</th>
								<th class="nowrap hidden-sm-down">
									<?php echo JText::_('COM_INSTALLER_HEADING_INSTALLTYPE'); ?>
								</th>
								<th width="40%" class="nowrap hidden-sm-down">
									<?php echo JText::_('COM_INSTALLER_HEADING_DETAILSURL'); ?>
								</th>
							</tr>
							</thead>
							<tfoot>
							<tr>
								<td colspan="9">
									<?php echo $this->pagination->getListFooter(); ?>
								</td>
							</tr>
							</tfoot>
							<tbody>
							<?php foreach ($this->items as $i => $item) : ?>
								<?php
								$client          = $item->client_id ? JText::_('JADMINISTRATOR') : JText::_('JSITE');
								$manifest        = json_decode($item->manifest_cache);
								$current_version = isset($manifest->version) ? $manifest->version : JText::_('JLIB_UNKNOWN');
								?>
								<tr class="row<?php echo $i % 2; ?>">
									<td>
										<?php echo JHtml::_('grid.id', $i, $item->update_id); ?>
									</td>
									<td>
										<label for="cb<?php echo $i; ?>">
											<span class="editlinktip hasTooltip" title="<?php echo JHtml::_('tooltipText', JText::_('JGLOBAL_DESCRIPTION'), $item->description ? $item->description : JText::_('COM_INSTALLER_MSG_UPDATE_NODESC'), 0); ?>">
											<?php echo $this->escape($item->name); ?>
											</span>
										</label>
									</td>
									<td class="center">
										<?php echo $item->client_translated; ?>
									</td>
									<td class="center">
										<?php echo $item->type_translated; ?>
									</td>
									<td class="hidden-sm-down text-center">
										<span class="badge badge-warning"><?php echo $item->current_version; ?></span>
									</td>
									<td>
										<span class="badge badge-success"><?php echo $item->version; ?></span>
									</td>
									<td class="hidden-sm-down text-center">
										<?php echo $item->folder_translated; ?>
									</td>
									<td class="hidden-sm-down text-center">
										<?php echo $item->install_type; ?>
									</td>
									<td class="hidden-sm-down">
										<span class="break-word">
										<?php echo $item->detailsurl; ?>
											<?php if (isset($item->infourl)) : ?>
												<br />
												<a href="<?php echo $item->infourl; ?>" target="_blank"><?php echo $this->escape($item->infourl); ?></a>
											<?php endif; ?>
										</span>
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
