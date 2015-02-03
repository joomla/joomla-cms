<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
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
			<div id="filter-bar" class="btn-toolbar">
				<div class="btn-group pull-right hidden-phone">
					<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
					<?php echo $this->pagination->getLimitBox(); ?>
				</div>
				<div class="filter-search btn-group pull-left">
					<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" class="hasTooltip" title="<?php echo JHtml::tooltipText('COM_INSTALLER_FILTER_LABEL'); ?>" />
				</div>
				<div class="btn-group pull-left">
					<button type="submit" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
					<button type="button" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
				</div>
			</div>
			<div class="clearfix"> </div>
			<?php if (count($this->items)) : ?>
			<table class="table table-striped">
				<thead>
					<tr>
						<th width="20" class="center">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>
						<th width="10%" class="center">
							<?php echo JHtml::_('grid.sort', 'JSTATUS', 'enabled', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap">
							<?php echo JHtml::_('grid.sort', 'COM_INSTALLER_HEADING_UPDATESITE_NAME', 'update_site_name', $listDirn, $listOrder); ?>
						</th>
						<th class="nowrap hidden-phone">
							<?php echo JHtml::_('grid.sort', 'COM_INSTALLER_HEADING_NAME', 'name', $listDirn, $listOrder); ?>
						</th>
						<th class="hidden-phone">
							<?php echo JHtml::_('grid.sort', 'COM_INSTALLER_HEADING_LOCATION', 'client_id', $listDirn, $listOrder); ?>
						</th>
						<th class="hidden-phone">
							<?php echo JHtml::_('grid.sort', 'COM_INSTALLER_HEADING_TYPE', 'type', $listDirn, $listOrder); ?>
						</th>
						<th class="hidden-phone">
							<?php echo JHtml::_('grid.sort', 'COM_INSTALLER_HEADING_FOLDER', 'folder', $listDirn, $listOrder); ?>
						</th>
						<th width="10"  class="hidden-phone">
							<?php echo JHtml::_('grid.sort', 'COM_INSTALLER_HEADING_UPDATESITEID', 'update_site_id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="12">
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
								<?php echo JHtml::_('jgrid.published', $item->enabled, $i, 'updatesites.'); ?>
							<?php endif; ?>
						</td>
						<td>
							<?php echo $item->update_site_name; ?>
						</td>
						<td  class="hidden-phone">
							<span class="bold hasTooltip" title="<?php echo JHtml::tooltipText($item->name, $item->description, 0); ?>">
								<?php echo $item->name; ?>
							</span>
						</td>
						<td class="center hidden-phone">
							<?php echo $item->client; ?>
						</td>
						<td class="center hidden-phone">
							<?php echo JText::_('COM_INSTALLER_TYPE_' . $item->type); ?>
						</td>
						<td class="center hidden-phone">
							<?php echo @$item->folder != '' ? $item->folder : JText::_('COM_INSTALLER_TYPE_NONAPPLICABLE'); ?>
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
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>
