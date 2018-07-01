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

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<div id="installer-update" class="clearfix">
	<form action="<?php echo Route::_('index.php?option=com_installer&view=update'); ?>" method="post" name="adminForm" id="adminForm">
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
					<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
					<?php if (empty($this->items)) : ?>
						<joomla-alert type="info"><?php echo Text::_('COM_INSTALLER_MSG_UPDATE_NOUPDATES'); ?></joomla-alert>
					<?php else : ?>
						<table class="table table-striped">
							<thead>
								<tr>
									<th class="nowrap" style="width:1%">
										<?php echo HTMLHelper::_('grid.checkall'); ?>
									</th>
									<th class="nowrap">
										<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_NAME', 'u.name', $listDirn, $listOrder); ?>
									</th>
									<th class="text-center">
										<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_LOCATION', 'client_translated', $listDirn, $listOrder); ?>
									</th>
									<th class="text-center">
										<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_TYPE', 'type_translated', $listDirn, $listOrder); ?>
									</th>
									<th class="d-none d-md-table-cell text-center">
										<?php echo Text::_('COM_INSTALLER_CURRENT_VERSION'); ?>
									</th>
									<th class="d-none d-md-table-cell text-center">
										<?php echo Text::_('COM_INSTALLER_NEW_VERSION'); ?>
									</th>
									<th class="d-none d-md-table-cell text-center">
										<?php echo Text::_('COM_INSTALLER_CHANGELOG'); ?>
									</th>
									<th class="d-none d-md-table-cell text-center">
										<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_FOLDER', 'folder_translated', $listDirn, $listOrder); ?>
									</th>
									<th class="d-none d-md-table-cell text-center">
										<?php echo Text::_('COM_INSTALLER_HEADING_INSTALLTYPE'); ?>
									</th>
									<th class="d-none d-md-table-cell" style="width:40%">
										<?php echo Text::_('COM_INSTALLER_HEADING_DETAILSURL'); ?>
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
									<?php $client          = $item->client_id ? Text::_('JADMINISTRATOR') : Text::_('JSITE'); ?>
									<?php $manifest        = json_decode($item->manifest_cache); ?>
									<?php $current_version = isset($manifest->version) ? $manifest->version : Text::_('JLIB_UNKNOWN'); ?>
									<tr>
										<td>
											<?php echo HTMLHelper::_('grid.id', $i, $item->update_id); ?>
										</td>
										<td>
											<label for="cb<?php echo $i; ?>">
												<span class="hasPopover" data-original-title="<?php echo Text::_('JGLOBAL_DESCRIPTION'); ?>"
													data-content="<?php echo $item->description ?: Text::_('COM_INSTALLER_MSG_UPDATE_NODESC'); ?>">
													<?php echo $this->escape($item->name); ?>
												</span>
											</label>
										</td>
										<td class="text-center">
											<?php echo $item->client_translated; ?>
										</td>
										<td class="text-center">
											<?php echo $item->type_translated; ?>
										</td>
										<td class="d-none d-md-table-cell text-center">
											<span class="badge badge-warning"><?php echo $item->current_version; ?></span>
										</td>
										<td class="d-none d-md-table-cell text-center">
											<span class="badge badge-success"><?php echo $item->version; ?></span>
										</td>
										<td class="d-none d-md-table-cell text-center">
											<?php if ($item->changelogurl != null):?>
											<button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#changelog_modal<?php echo $item->extension_id; ?>"><?php echo Text::_('COM_INSTALLER_CHANGELOG'); ?></button>

											<?php
											echo HTMLHelper::_(
												'bootstrap.renderModal',
												'changelog_modal' . $item->extension_id,
												array(
													'title' => $item->version . " - " . $item->name,
													'bodyHeight'  => '60',
													'modalWidth'  => '60',
												),
												'<iframe src="' . $item->changelogurl . '"></iframe>');
											?>

											<?php else:?>
											<span>
												<?php echo Text::_('COM_INSTALLER_TYPE_NONAPPLICABLE')?>
											</span>

											<?php endif; ?>
										</td>
										<td class="d-none d-md-table-cell text-center">
											<?php echo $item->folder_translated; ?>
										</td>
										<td class="d-none d-md-table-cell text-center">
											<?php echo $item->install_type; ?>
										</td>
										<td class="d-none d-md-table-cell">
											<span class="break-word">
												<?php echo $item->detailsurl; ?>
												<?php if (isset($item->infourl)) : ?>
													<br>
													<a href="<?php echo $item->infourl; ?>" target="_blank" rel="noopener noreferrer"><?php echo $this->escape($item->infourl); ?></a>
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
					<?php echo HTMLHelper::_('form.token'); ?>
				</div>
			</div>
		</div>
	</form>
</div>
