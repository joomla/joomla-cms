<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;

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
					<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
					<?php if (empty($this->items)) : ?>
					<div class="alert alert-warning alert-no-items">
						<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
					<?php else : ?>
					<table class="table table-striped">
						<thead>
							<tr>
								<th style="width:1%" class="text-center">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</th>
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
						<?php $itemsSkipped = 0; ?>
						<?php foreach ($this->items as $i => $item) : ?>
							<?php if ($item->extra_query == null) : ?>
								<?php $itemsSkipped++; ?>
								<?php continue; ?>
							<?php endif; ?>

							<tr class="row<?php echo ($i + $itemsSkipped) % 2; ?>">
								<td class="text-center">
									<?php echo HTMLHelper::_('grid.id', $i-$itemsSkipped, $item->update_site_id); ?>
								</td>
								<td>
									<label for="cb<?php echo $i-$itemsSkipped; ?>">
										<?php echo $item->update_site_name; ?>
										<br>
										<span class="small break-word">
											<a href="<?php echo $item->location; ?>" target="_blank" rel="noopener noreferrer"><?php echo $this->escape($item->location); ?></a>
										</span>
									</label>
								</td>
								<td class="hidden-sm-down text-center">
									<span class="bold hasTooltip" title="<?php echo HTMLHelper::_('tooltipText', $item->name, $item->description, 0); ?>">
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
								<td>
									<?php echo $item->extra_query['value'] ? $item->extra_query['value'] : Text::_('COM_INSTALLER_TYPE_NONAPPLICABLE'); ?>
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
