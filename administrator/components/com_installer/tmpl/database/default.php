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

?>

<div id="installer-database" class="clearfix">
<<<<<<< HEAD
	<form enctype="multipart/form-data" action="<?php echo Route::_('index.php?option=com_installer&view=database'); ?>" method="post" name="adminForm" id="adminForm">
				<?php echo HTMLHelper::_('uitab.startTabSet', 'database-tabs', array('active' => 'update-structure')); ?>
=======
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
										<th scope="col" style="width:10%">
											<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_LOCATION', 'client_translated', $listDirection, $listOrder); ?>
										</th>
										<th scope="col" style="width:10%">
											<?php echo HTMLHelper::_('searchtools.sort', 'COM_INSTALLER_HEADING_TYPE', 'type_translated', $listDirection, $listOrder); ?>
										</th>
										<th scope="col" class="d-none d-md-table-cell" style="width:10%">
											<?php echo Text::_('COM_INSTALLER_HEADING_PROBLEMS'); ?>
										</th>
										<th scope="col" class="d-none d-md-table-cell text-right" style="width:10%">
											<?php echo Text::_('COM_INSTALLER_HEADING_DATABASE_SCHEMA'); ?>
										</th>
										<th scope="col" class="d-none d-md-table-cell" style="width:10%">
											<?php echo Text::_('COM_INSTALLER_HEADING_UPDATE_VERSION'); ?>
										</th>
										<th scope="col" class="d-none d-md-table-cell" style="width:10%">
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
>>>>>>> 5638552f721cf28f27f67e6a6e4a8ffd7c66d274

				<?php echo HTMLHelper::_('uitab.addTab', 'database-tabs', 'update-structure', Text::_('COM_INSTALLER_VIEW_DEFAULT_TAB_FIX')); ?>
				<?php echo $this->loadTemplate('update'); ?>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>

				<?php echo HTMLHelper::_('uitab.addTab', 'database-tabs', 'upload-import', Text::_('COM_INSTALLER_VIEW_DEFAULT_TAB_IMPORT')); ?>
				<?php echo $this->loadTemplate('import'); ?>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>

				<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
	</form>
</div>
