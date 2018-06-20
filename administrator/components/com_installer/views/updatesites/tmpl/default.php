<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('bootstrap.tooltip');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<div id="installer-manage" class="clearfix">
	<form action="<?php echo JRoute::_('index.php?option=com_installer&view=updatesites'); ?>" method="post" name="adminForm" id="adminForm">
		<?php if (!empty( $this->sidebar)) : ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">
		<?php else : ?>
		<div id="j-main-container">
		<?php endif; ?>
			<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
			<div class="clearfix"></div>
			<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
			<?php else : ?>
			<table class="table table-striped">
				<thead>
					<tr>
						<th width="1%" class="center">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>
						<th width="1%" class="nowrap center">
							<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'enabled', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap">
							<?php echo JHtml::_('searchtools.sort', 'COM_INSTALLER_HEADING_UPDATESITE_NAME', 'update_site_name', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'COM_INSTALLER_HEADING_NAME', 'name', $listDirn, $listOrder); ?>
						</th>
						<th class="hidden-phone hidden-tablet">
							<?php echo JHtml::_('searchtools.sort', 'COM_INSTALLER_HEADING_LOCATION', 'client_translated', $listDirn, $listOrder); ?>
						</th>
						<th class="hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'COM_INSTALLER_HEADING_TYPE', 'type_translated', $listDirn, $listOrder); ?>
						</th>
						<th class="hidden-phone hidden-tablet">
							<?php echo JHtml::_('searchtools.sort', 'COM_INSTALLER_HEADING_FOLDER', 'folder_translated', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="nowrap hidden-phone">
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
				<tbody>
				<?php foreach ($this->items as $i => $item) : ?>
					<tr class="row<?php echo $i % 2; if ($item->enabled == 2) echo ' protected'; ?>">
						<td class="center">
							<?php echo JHtml::_('grid.id', $i, $item->update_site_id); ?>
						</td>
						<td class="center">
							<?php if (!$item->element) : ?>
								<strong>X</strong>
							<?php else : ?>
								<?php echo JHtml::_('InstallerHtml.Updatesites.state', $item->enabled, $i, $item->enabled < 2, 'cb'); ?>
							<?php endif; ?>
						</td>
						<td>
							<label for="cb<?php echo $i; ?>">
								<?php echo JText::_($item->update_site_name); ?>
								<br />
								<span class="small break-word">
									<a href="<?php echo $item->location; ?>" target="_blank" rel="noopener noreferrer"><?php echo $this->escape($item->location); ?></a>
								</span>
							</label>
						</td>
						<td class="hidden-phone">
							<span class="bold hasTooltip" title="<?php echo JHtml::_('tooltipText', $item->name, $item->description, 0); ?>">
								<?php echo $item->name; ?>
							</span>
						</td>
						<td class="hidden-phone hidden-tablet">
							<?php echo $item->client_translated; ?>
						</td>
						<td class="hidden-phone">
							<?php echo $item->type_translated; ?>
						</td>
						<td class="hidden-phone hidden-tablet">
							<?php echo $item->folder_translated; ?>
						</td>
						<td class="hidden-phone">
							<?php echo $item->update_site_id; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php endif; ?>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>
