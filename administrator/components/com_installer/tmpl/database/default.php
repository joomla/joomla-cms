<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('bootstrap.popover');

$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirection = $this->escape($this->state->get('list.direction'));

?>
<div id="installer-database" class="clearfix">
	<form action="<?php echo Route::_('index.php?option=com_installer&view=database'); ?>" method="post" name="adminForm" id="adminForm">
		<div class="row">
			<div id="j-sidebar-container" class="col-md-2">
				<?php echo $this->sidebar; ?>
			</div>
			<div class="col-md-10">
				<div id="j-main-container" class="j-main-container">
					<div class="control-group">
						<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
						<?php if (empty($this->changeSet)) : ?>
							<joomla-alert type="warning"><?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?></joomla-alert>
						<?php else : ?>
							<table class="table table-striped">
								<thead>
									<tr>
										<th class="nowrap" style="width:1%">
											<?php echo HTMLHelper::_('grid.checkall'); ?>
										</th>
										<th class="nowrap">
											<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_NAME', 'name', $listDirection, $listOrder); ?>
										</th>
										<th class="text-center" style="width:10%">
											<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_LOCATION', 'client_translated', $listDirection, $listOrder); ?>
										</th>
										<th class="text-center" style="width:10%">
											<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_TYPE', 'type_translated', $listDirection, $listOrder); ?>
										</th>
										<th class="d-none d-md-table-cell text-center" style="width:10%">
											<?php echo Text::_('COM_INSTALLER_HEADING_PROBLEMS'); ?>
										</th>
										<th class="d-none d-md-table-cell text-center" style="width:10%">
											<span class="hasPopover" data-original-title="<?php echo Text::_('COM_INSTALLER_HEADING_DATABASE_SCHEMA'); ?>"
											    data-content="<?php echo Text::_('COM_INSTALLER_HEADING_DATABASE_SCHEMA_DESC'); ?>" data-placement="top">
												<?php echo Text::_('COM_INSTALLER_HEADING_DATABASE_SCHEMA'); ?>
											</span>
										</th>
										<th class="d-none d-md-table-cell text-center" style="width:10%">
											<span class="hasPopover" data-original-title="<?php echo Text::_('COM_INSTALLER_HEADING_UPDATE_VERSION'); ?>"
											    data-content="<?php echo Text::_('COM_INSTALLER_HEADING_UPDATE_VERSION_DESC'); ?>" data-placement="top">
												<?php echo Text::_('COM_INSTALLER_HEADING_UPDATE_VERSION'); ?>
											</span>
										</th>
										<th class="d-none d-md-table-cell text-center" style="width:10%">
											<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_FOLDER', 'folder_translated', $listDirection, $listOrder); ?>
										</th>
										<th class="nowrap d-none d-md-table-cell text-center" style="width:1%">
											<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_ID', 'extension_id', $listDirection, $listOrder); ?>
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
									<?php foreach ($this->changeSet as $i => $item) : ?>
										<?php $extension = $item['extension']; ?>
										<?php $manifest = json_decode($extension->manifest_cache); ?>

										<tr>
											<td>
												<?php echo HTMLHelper::_('grid.id', $i, $extension->extension_id); ?>
											</td>
											<td>
												<label for="cb<?php echo $i; ?>">
													<span class="hasPopover" data-original-title="<?php echo $extension->name; ?>"
														data-content="<?php echo Text::_($manifest->description) ?: Text::_('COM_INSTALLER_MSG_UPDATE_NODESC'); ?>">
														<?php echo $extension->name; ?>
													</span>
												</label>
											</td>
											<td class="text-center">
												<?php echo $extension->client_translated; ?>
											</td>
											<td class="text-center">
												<?php echo $extension->type_translated; ?>
											</td>
											<td class="d-none d-md-table-cell text-center">
												<span class="badge badge-<?php echo count($item['results']['error']) ? 'danger' : ($item['errorsCount'] ? 'warning' : 'success'); ?> hasPopover"
													data-content="<ul><li><?php echo implode('</li><li>', $item['errorsMessage']); ?></li></ul>"
													data-original-title="<?php echo Text::plural('COM_INSTALLER_MSG_DATABASE_ERRORS', $item['errorsCount']); ?>">
													<?php echo Text::plural('COM_INSTALLER_MSG_DATABASE_ERRORS', $item['errorsCount']); ?>
												</span>
											</td>
											<td class="d-none d-md-table-cell text-center">
												<?php echo $extension->version_id; ?>
											</td>
											<td class="d-none d-md-table-cell text-center">
												<?php echo $extension->version; ?>
											</td>
											<td class="d-none d-md-table-cell text-center">
												<?php echo $extension->folder_translated; ?>
											</td>
											<td class="d-none d-md-table-cell text-center">
												<?php echo $extension->extension_id; ?>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						<?php endif; ?>
					</div>
					<input type="hidden" name="task" value="">
					<input type="hidden" name="boxchecked" value="0">
					<?php echo HTMLHelper::_('form.token'); ?>
				</div>
			</div>
		</div>
	</form>
</div>
