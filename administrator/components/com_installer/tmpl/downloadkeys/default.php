<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('bootstrap.tooltip');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<div id="installer-manage" class="clearfix">
	<form action="<?php echo Route::_('index.php?option=com_installer&view=downloadkeys'); ?>" method="post" name="adminForm" id="adminForm">
		<div class="row">
			<div class="col-md-12">
				<div id="j-main-container" class="j-main-container">
					<?php echo JLayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
					<?php if (empty($this->items)) : ?>
					<div class="alert alert-warning alert-no-items">
						<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
					<?php else : ?>
					<table class="table table-striped">
						<thead>
							<tr>
								<td style="width:1%" class="text-center">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</td>
								<th scope="col" class="nowrap">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_UPDATESITE_NAME', 'update_site_name', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:20%" class="d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_NAME', 'name', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:10%" class="d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_LOCATION', 'client_translated', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:10%" class="d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_TYPE', 'type_translated', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:10%" class="d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_FOLDER', 'folder_translated', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:5%" class="d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'update_site_id', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" style="width:5%" class="d-none d-md-table-cell">
									<?php echo Text::_('COM_INSTALLER_HEADING_DOWNLOADKEY'); ?>
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
							<tr>
								<td class="text-center">
									<?php if ($item->extra_query['valid']) : ?>
										<?php echo HTMLHelper::_('grid.id', $i, $item->update_site_id); ?>
									<?php endif; ?>
								</td>
								<th scope="row">
									<?php if ($item->extra_query['valid']) : ?>
										<a href="<?php echo Route::_('index.php?option=com_installer&task=downloadkey.edit&update_site_id=' . $item->update_site_id); ?>">
											<?php echo $item->update_site_name; ?>
										</a>
									<?php else: ?>
										<?php echo $item->update_site_name; ?>
									<?php endif; ?>
									<br>
									<span class="small break-word">
										<a href="<?php echo $item->location; ?>" target="_blank" rel="noopener noreferrer"><?php echo $this->escape($item->location); ?></a>
									</span>
								</th>
								<td class="hidden-sm-down">
									<span class="bold hasTooltip" title="<?php echo HTMLHelper::_('tooltipText', $item->name, $item->description, 0); ?>">
										<?php echo $item->name; ?>
									</span>
								</td>
								<td class="hidden-sm-down">
									<?php echo $item->client_translated; ?>
								</td>
								<td class="hidden-sm-down">
									<?php echo $item->type_translated; ?>
								</td>
								<td class="hidden-sm-down">
									<?php echo $item->folder_translated; ?>
								</td>
								<td class="hidden-sm-down text-center">
									<?php echo $item->update_site_id; ?>
								</td>
								<td>
									<?php echo $item->extra_query['valid'] ? $item->extra_query['value'] : Text::_('COM_INSTALLER_TYPE_NONAPPLICABLE'); ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
					<?php endif; ?>
					<input type="hidden" name="task" value="">
					<input type="hidden" name="boxchecked" value="0">
					<?php echo HTMLHelper::_('form.token'); ?>
				</div>
			</div>
		</div>
	</form>
</div>
