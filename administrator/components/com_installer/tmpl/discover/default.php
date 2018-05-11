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

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<div id="installer-discover" class="clearfix">
	<form action="<?php echo JRoute::_('index.php?option=com_installer&view=discover'); ?>" method="post" name="adminForm" id="adminForm">
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
						<joomla-alert type="info"><?php echo JText::_('COM_INSTALLER_MSG_DISCOVER_DESCRIPTION'); ?></joomla-alert>
						<joomla-alert type="warning"><?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></joomla-alert>
					<?php else : ?>
					<table class="table table-striped">
						<thead>
							<tr>
								<th style="width:1%" class="nowrap text-center">
									<?php echo JHtml::_('grid.checkall'); ?>
								</th>
								<th class="nowrap">
									<?php echo JHtml::_('searchtools.sort', 'COM_INSTALLER_HEADING_NAME', 'name', $listDirn, $listOrder); ?>
								</th>
								<th class="nowrap">
									<?php echo JHtml::_('searchtools.sort', 'COM_INSTALLER_HEADING_LOCATION', 'client_translated', $listDirn, $listOrder); ?>
								</th>
								<th class="nowrap">
									<?php echo JHtml::_('searchtools.sort', 'COM_INSTALLER_HEADING_TYPE', 'type_translated', $listDirn, $listOrder); ?>
								</th>
								<th style="width:10%" class="d-none d-md-table-cell">
									<?php echo JText::_('JVERSION'); ?>
								</th>
								<th style="width:10%" class="d-none d-md-table-cell">
									<?php echo JText::_('JDATE'); ?>
								</th>
								<th style="width:15%" class="d-none d-md-table-cell">
									<?php echo JText::_('JAUTHOR'); ?>
								</th>
								<th class="nowrap d-none d-md-table-cell">
									<?php echo JHtml::_('searchtools.sort', 'COM_INSTALLER_HEADING_FOLDER', 'folder_translated', $listDirn, $listOrder); ?>
								</th>
								<th style="width:1%" class="nowrap d-none d-md-table-cell">
									<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'extension_id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td colspan="9"><?php echo $this->pagination->getListFooter(); ?></td>
							</tr>
						</tfoot>
						<tbody>
						<?php foreach ($this->items as $i => $item) : ?>
							<tr class="row<?php echo $i % 2; ?>">
								<td class="text-center">
									<?php echo JHtml::_('grid.id', $i, $item->extension_id); ?>
								</td>
								<td>
									<label for="cb<?php echo $i; ?>">
										<span class="bold hasTooltip" title="<?php echo JHtml::_('tooltipText', $item->name, $item->description, 0); ?>"><?php echo $item->name; ?></span>
									</label>
								</td>
								<td>
									<?php echo $item->client_translated; ?>
								</td>
								<td>
									<?php echo $item->type_translated; ?>
								</td>
								<td class="d-none d-md-table-cell">
									<?php echo @$item->version != '' ? $item->version : '&#160;'; ?>
								</td>
								<td class="d-none d-md-table-cell">
									<?php echo @$item->creationDate != '' ? $item->creationDate : '&#160;'; ?>
								</td>
								<td class="d-none d-md-table-cell">
									<span class="editlinktip hasTooltip" title="<?php echo JHtml::_('tooltipText', JText::_('COM_INSTALLER_AUTHOR_INFORMATION'), $item->author_info, 0); ?>">
										<?php echo @$item->author != '' ? $item->author : '&#160;'; ?>
									</span>
								</td>
								<td class="d-none d-md-table-cell">
									<?php echo $item->folder_translated; ?>
								</td>
								<td class="d-none d-md-table-cell">
									<?php echo $item->extension_id; ?>
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
