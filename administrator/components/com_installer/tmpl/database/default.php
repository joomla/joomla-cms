<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;

HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('bootstrap.tooltip');

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
					<table class="table table-striped">
						<thead>
							<tr>
								<th style="width:1%" class="nowrap text-center">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</th>
								<th class="nowrap">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_NAME', 'name', $listDirection, $listOrder); ?>
								</th>
								<th class="nowrap">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_LOCATION', 'client_translated', $listDirection, $listOrder); ?>
								</th>
								<th class="nowrap">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_TYPE', 'type_translated', $listDirection, $listOrder); ?>
								</th>
								<th class="nowrap">
									<?php echo Text::_('COM_INSTALLER_HEADING_ERRORS'); ?>
								</th>
								<th class="nowrap hasPopover" data-original-title="<?php echo Text::_('COM_INSTALLER_HEADING_DATABASE_SCHEMA'); ?>"
									data-content="<?php echo Text::_('COM_INSTALLER_HEADING_DATABASE_SCHEMA_DESC'); ?>">
									<?php echo Text::_('COM_INSTALLER_HEADING_DATABASE_SCHEMA'); ?>
								</th>
								<th class="nowrap hasPopover" data-original-title="<?php echo Text::_('COM_INSTALLER_HEADING_UPDATE_VERSION'); ?>"
									data-content="<?php echo Text::_('COM_INSTALLER_HEADING_UPDATE_VERSION_DESC'); ?>">
									<?php echo Text::_('COM_INSTALLER_HEADING_UPDATE_VERSION'); ?>
								</th>
								<th class="nowrap">
									<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_FOLDER', 'folder_translated', $listDirection, $listOrder); ?>
								</th>
								<th class="nowrap">
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
								<?php $manifest = json_decode($extension['manifest_cache']); ?>

								<tr class="row<?php echo $i % 2; ?>">
									<td>
										<?php echo HTMLHelper::_('grid.id', $i, $extension['element']); ?>
									</td>
									<td>
									<label for="cb<?php echo $i; ?>">
										<span class="editlinktip hasTooltip" title="<?php echo HTMLHelper::_('tooltipText',
											Text::_('JGLOBAL_DESCRIPTION'),
											Text::_($manifest->description) ?
												Text::_($manifest->description) :
												Text::_(
													'COM_INSTALLER_MSG_UPDATE_NODESC'
												),
											0
										); ?>">
											<?php echo $extension['name'];?>
										</span>
									</label>
									</td>
									<td class="center">
										<?php echo $extension['client_translated'];?>
									</td>
									<td class="center">
										<?php echo $extension['type_translated']; ?>
									</td>
									<td>
										<label class="badge badge-<?php echo count($item['results']['error']) > 0 ? 'danger' : ($item['errorsCount'] > 0 ? 'warning' : 'success' ); ?> hasPopover" title=""
											data-content="<?php echo $item['errorsMessage']; ?>"
											data-original-title="<?php echo Text::plural('COM_INSTALLER_MSG_DATABASE_ERRORS', $item['errorsCount']); ?>">
											<?php echo Text::plural('COM_INSTALLER_MSG_DATABASE_ERRORS', $item['errorsCount']); ?>
										</label>
									</td>
									<td>
										<?php echo $extension['version_id']; ?>
									</td>
									<td>
										<?php echo $extension['version']; ?>
									</td>
									<td class="hidden-sm-down">
										<?php echo $extension['folder_translated']; ?>
									</td>
									<td>
										<?php echo $extension['extension_id']; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</form>
</div>
