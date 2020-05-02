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
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.multiselect');

$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirection = $this->escape($this->state->get('list.direction'));

?>
<div id="installer-database" class="clearfix">
	<form action="<?php echo Route::_('index.php?option=com_installer&view=database'); ?>" method="post" name="adminForm" id="adminForm">
		<div class="row">
			<div class="col-md-12">
				<div id="j-main-container" class="j-main-container">
					<div class="control-group">
						<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
						<?php if (empty($this->changeSet)) : ?>
							<div class="alert alert-info">
								<span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('INFO'); ?></span>
								<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
							</div>
						<?php else : ?>
							<table class="table">
								<caption id="captionTable" class="sr-only">
									<?php echo Text::_('COM_INSTALLER_DATABASE_TABLE_CAPTION'); ?>, <?php echo Text::_('JGLOBAL_SORTED_BY'); ?>
								</caption>
								<thead>
									<tr>
										<td class="text-center" style="width:1%">
											<?php echo HTMLHelper::_('grid.checkall'); ?>
										</td>
										<th scope="col">
											<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_NAME', 'name', $listDirection, $listOrder); ?>
										</th>
										<th scope="col" class="w-10">
											<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_LOCATION', 'client_translated', $listDirection, $listOrder); ?>
										</th>
										<th scope="col" class="w-10">
											<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_TYPE', 'type_translated', $listDirection, $listOrder); ?>
										</th>
										<th scope="col" class="w-10 d-none d-md-table-cell">
											<?php echo Text::_('COM_INSTALLER_HEADING_PROBLEMS'); ?>
										</th>
										<th scope="col" class="w-10 d-none d-md-table-cell text-right">
											<?php echo Text::_('COM_INSTALLER_HEADING_DATABASE_SCHEMA'); ?>
										</th>
										<th scope="col" class="w-10 d-none d-md-table-cell">
											<?php echo Text::_('COM_INSTALLER_HEADING_UPDATE_VERSION'); ?>
										</th>
										<th scope="col" class="w-10 d-none d-md-table-cell">
											<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_FOLDER', 'folder_translated', $listDirection, $listOrder); ?>
										</th>
										<th scope="col" class="d-none d-md-table-cell" style="width:1%">
											<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_ID', 'extension_id', $listDirection, $listOrder); ?>
										</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($this->changeSet as $i => $item) : ?>
										<?php $extension = $item['extension']; ?>
										<?php $manifest = json_decode($extension->manifest_cache); ?>

										<tr>
											<td class="text-center">
												<?php echo HTMLHelper::_('grid.id', $i, $extension->extension_id); ?>
											</td>
											<th scope="row">
												<label for="cb<?php echo $i; ?>">
													<?php echo $extension->name; ?>
												</label>
												<div class="small">
													<?php echo Text::_($manifest->description); ?>
												</div>
											</th>
											<td>
												<?php echo $extension->client_translated; ?>
											</td>
											<td>
												<?php echo $extension->type_translated; ?>
											</td>
											<td class="d-none d-md-table-cell">
												<span class="badge badge-<?php echo count($item['results']['error']) ? 'danger' : ($item['errorsCount'] ? 'warning' : 'success'); ?>" tabindex="0">
													<?php echo Text::plural('COM_INSTALLER_MSG_DATABASE_ERRORS', $item['errorsCount']); ?>
												</span>
												<div role="tooltip" id="tip<?php echo $i; ?>">
													<strong><?php echo Text::plural('COM_INSTALLER_MSG_DATABASE_ERRORS', $item['errorsCount']); ?></strong>
													<ul><li><?php echo implode('</li><li>', $item['errorsMessage']); ?></li></ul>
												</div>
											</td>
											<td class="d-none d-md-table-cell text-right">
												<?php echo $extension->version_id; ?>
											</td>
											<td class="d-none d-md-table-cell">
												<?php echo '&#x200E;' . $extension->version; ?>
											</td>
											<td class="d-none d-md-table-cell">
												<?php echo $extension->folder_translated; ?>
											</td>
											<td class="d-none d-md-table-cell">
												<?php echo $extension->extension_id; ?>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>

							<?php // load the pagination. ?>
							<?php echo $this->pagination->getListFooter(); ?>

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
