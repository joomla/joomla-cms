<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('multiselect')
	->useScript('com_installer.changelog');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<div id="installer-manage" class="clearfix">
	<form action="<?php echo Route::_('index.php?option=com_installer&view=manage'); ?>" method="post" name="adminForm" id="adminForm">
		<div class="row">
			<div class="col-md-12">
				<div id="j-main-container" class="j-main-container">
					<?php if ($this->showMessage) : ?>
						<?php echo $this->loadTemplate('message'); ?>
					<?php endif; ?>
					<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
					<?php if (empty($this->items)) : ?>
						<div class="alert alert-info">
							<span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
							<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
						</div>
					<?php else : ?>
					<table class="table" id="manageList">
						<caption class="visually-hidden">
							<?php echo Text::_('COM_INSTALLER_MANAGE_TABLE_CAPTION'); ?>,
							<span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
							<span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
						</caption>
						<thead>
							<tr>
								<td class="w-1 text-center">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</td>
								<th scope="col" class="w-1 text-center">
									<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'status', $listDirn, $listOrder); ?>
								</th>
								<th scope="col">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_NAME', 'name', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-10 d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_LOCATION', 'client_translated', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-10">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_TYPE', 'type_translated', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-10 d-none d-md-table-cell">
									<?php echo Text::_('JVERSION'); ?>
								</th>
								<th scope="col" class="w-10 d-none d-md-table-cell">
									<?php echo Text::_('JDATE'); ?>
								</th>
								<th scope="col" class="w-10 d-none d-md-table-cell">
									<?php echo Text::_('JAUTHOR'); ?>
								</th>
								<th scope="col" class="w-5 d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_FOLDER', 'folder_translated', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-1 d-none d-md-table-cell">
									<?php echo Text::_('COM_INSTALLER_HEADING_LOCKED'); ?>
								</th>
								<th scope="col" class="d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_PACKAGE_ID', 'package_id', $listDirn, $listOrder); ?>
								</th>
								<th scope="col" class="w-1 d-none d-md-table-cell">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_ID', 'extension_id', $listDirn, $listOrder); ?>
								</th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ($this->items as $i => $item) : ?>
							<tr class="row<?php echo $i % 2; if ($item->status == 2) echo ' protected'; ?>">
								<td class="text-center">
									<?php echo HTMLHelper::_('grid.id', $i, $item->extension_id, false, 'cid', 'cb', $item->name); ?>
								</td>
								<td class="text-center">
									<?php if (!$item->element) : ?>
									<strong>X</strong>
									<?php else : ?>
										<?php echo HTMLHelper::_('manage.state', $item->status, $i, $item->status < 2, 'cb'); ?>
									<?php endif; ?>
								</td>
								<th scope="row">
									<span tabindex="0"><?php echo $item->name; ?></span>
									<div role="tooltip" id="tip<?php echo $i; ?>">
										<?php echo $item->description; ?>
									</div>
								</th>
								<td class="d-none d-md-table-cell">
									<?php echo $item->client_translated; ?>
								</td>
								<td>
									<?php echo $item->type_translated; ?>
								</td>
								<td class="d-none d-md-table-cell">
									<?php if (!empty($item->version)) : ?>
										<?php if (!empty($item->changelogurl)) : ?>
											<a href="#changelogModal<?php echo $item->extension_id; ?>" class="changelogModal" data-js-extensionid="<?php echo $item->extension_id; ?>" data-js-view="manage" data-bs-toggle="modal">
												<?php echo $item->version?>
											</a>
											<?php
											echo HTMLHelper::_(
												'bootstrap.renderModal',
												'changelogModal' . $item->extension_id,
												array(
													'title' => Text::sprintf('COM_INSTALLER_CHANGELOG_TITLE', $item->name, $item->version),
												),
												''
											);
											?>
										<?php else : ?>
											<?php echo $item->version; ?>
										<?php endif; ?>
									<?php else :
										echo '&#160;';
									endif; ?>
								</td>
								<td class="d-none d-md-table-cell">
									<?php echo !empty($item->creationDate) ? $item->creationDate : '&#160;'; ?>
								</td>
								<td class="d-none d-md-table-cell">
									<?php echo !empty($item->author) ? $item->author : '&#160;'; ?>
								</td>
								<td class="d-none d-md-table-cell">
									<?php echo $item->folder_translated; ?>
								</td>
								<td class="d-none d-md-table-cell">
									<?php echo $item->locked ? Text::_('JYES') : Text::_('JNO'); ?>
								</td>
								<td class="d-none d-md-table-cell">
									<?php echo $item->package_id ?: '&#160;'; ?>
								</td>
								<td class="d-none d-md-table-cell">
									<?php echo $item->extension_id; ?>
								</td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>

					<?php // load the pagination. ?>
					<?php echo $this->pagination->getListFooter(); ?>

					<?php endif; ?>
					<input type="hidden" name="task" value="">
					<input type="hidden" name="boxchecked" value="0">
					<?php echo HTMLHelper::_('form.token'); ?>
				</div>
			</div>
		</div>
	</form>
</div>
