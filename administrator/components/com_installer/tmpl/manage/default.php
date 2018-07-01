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
use Joomla\CMS\Router\Route;

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<div id="installer-manage" class="clearfix">
	<form action="<?php echo Route::_('index.php?option=com_installer&view=manage'); ?>" method="post" name="adminForm" id="adminForm">
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
						<joomla-alert type="warning"><?php echo Text::_('COM_INSTALLER_MSG_MANAGE_NOEXTENSION'); ?></joomla-alert>
					<?php else : ?>
					<table class="table table-striped" id="manageList">
						<thead>
							<tr>
								<th class="nowrap" style="width:1%">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</th>
								<th class="nowrap text-center" style="width:1%">
									<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'status', $listDirn, $listOrder); ?>
								</th>
								<th class="nowrap">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_NAME', 'name', $listDirn, $listOrder); ?>
								</th>
								<th class="text-center" style="width:10%">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_LOCATION', 'client_translated', $listDirn, $listOrder); ?>
								</th>
								<th class="text-center" style="width:10%">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_TYPE', 'type_translated', $listDirn, $listOrder); ?>
								</th>
								<th class="d-none d-md-table-cell text-center" style="width:10%">
									<?php echo Text::_('JVERSION'); ?>
								</th>
								<th class="d-none d-md-table-cell text-center" style="width:10%">
									<?php echo Text::_('JDATE'); ?>
								</th>
								<th class="d-none d-md-table-cell text-center" style="width:10%">
									<?php echo Text::_('JAUTHOR'); ?>
								</th>
								<th class="d-none d-md-table-cell text-center" style="width:5%">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_FOLDER', 'folder_translated', $listDirn, $listOrder); ?>
								</th>
								<th class="d-none d-md-table-cell text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_PACKAGE_ID', 'package_id', $listDirn, $listOrder); ?>
								</th>
								<th class="d-none d-md-table-cell text-center" style="width:1%">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_ID', 'extension_id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td colspan="11">
									<?php echo $this->pagination->getListFooter(); ?>
								</td>
							</tr>
						</tfoot>
						<tbody>
							<?php foreach ($this->items as $i => $item) : ?>
								<tr class="<?php if ($item->status === 2) echo 'protected'; ?>">
									<td>
										<?php echo HTMLHelper::_('grid.id', $i, $item->extension_id); ?>
									</td>
									<td class="text-center">
										<?php if (!$item->element) : ?>
											<strong>X</strong>
										<?php else : ?>
											<?php echo HTMLHelper::_('InstallerHtml.Manage.state', $item->status, $i, $item->status < 2, 'cb'); ?>
										<?php endif; ?>
									</td>
									<td>
										<label for="cb<?php echo $i; ?>">
											<span class="bold hasPopover" data-original-title="<?php echo $item->name; ?>"
												data-content="<?php echo $item->description; ?>">
												<?php echo $item->name; ?>
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
										<?php if ($item->version != ''): ?>
											<?php if ($item->changelogurl != null): ?>
												<a href="#changelog_modal" onclick="loadChangelog(<?php echo $item->extension_id; ?>); return false;" data-toggle="modal">
													<?php echo $item->version?>
												</a>
												<?php
												echo HTMLHelper::_(
														'bootstrap.renderModal',
														'changelog_modal',
														array(
															'title' => $item->version . " - " . $item->name,
														),
														''
													);
												?>
											<?php else : ?>
												<?php echo $item->version; ?>
											<?php endif; ?>
										<?php else:
											echo '&#160;';
										endif; ?>
									</td>
									<td class="d-none d-md-table-cell text-center">
										<?php echo isset($item->creationDate) && $item->creationDate !== '' ? $item->creationDate : '&#160;'; ?>
									</td>
									<td class="d-none d-md-table-cell text-center">
										<span class="editlinktip hasTooltip" title="<?php echo HTMLHelper::_('tooltipText', Text::_('COM_INSTALLER_AUTHOR_INFORMATION'), $item->author_info, 0); ?>">
											<?php echo isset($item->author) && $item->author !== '' ? $item->author : '&#160;'; ?>
										</span>
									</td>
									<td class="d-none d-md-table-cell text-center">
										<?php echo $item->folder_translated; ?>
									</td>
									<td class="d-none d-md-table-cell text-center">
										<?php echo $item->package_id ?: '&#160;'; ?>
									</td>
									<td class="d-none d-md-table-cell text-center">
										<?php echo $item->extension_id; ?>
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
<script type="application/javascript">
	function loadChangelog(extensionId) {
	    var url = 'index.php?option=com_installer&task=manage.loadChangelog&eid=' + extensionId + '&format=json';

        Joomla.request({
            url:    url,
            onSuccess: function(response, xhr)
            {
                var result = JSON.parse(response);
                document.querySelectorAll('#changelog_modal .modal-body')[0].innerHTML = result.data;

                // Do nothing
            },
            onError: function(xhr)
            {
                // Do nothing
            }
        });
	}
</script>