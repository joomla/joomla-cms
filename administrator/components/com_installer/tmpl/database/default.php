<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.multiselect');
JHtml::_('bootstrap.tooltip');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

?>
<div id="installer-database" class="clearfix">
	<form action="<?php echo JRoute::_('index.php?option=com_installer&view=database'); ?>" method="post" name="adminForm" id="adminForm">
		<div class="row">
		<div id="j-sidebar-container" class="col-md-2">
			<?php echo $this->sidebar; ?>
		</div>
		<div class="col-md-10">
			<div id="j-main-container" class="j-main-container">
				<?php if($this->errorCount != 0) :?>
					<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'problems')); ?>
					<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'problems', JText::plural('COM_INSTALLER_MSG_N_DATABASE_ERROR_PANEL', $this->errorCount)); ?>
						<fieldset class="panelform">
							<ul>
								<?php if (!$this->filterParams) : ?>
									<li><?php echo JText::_('COM_INSTALLER_MSG_DATABASE_FILTER_ERROR'); ?></li>
								<?php endif; ?>

								<?php if ($this->schemaVersion != $this->changeSet['core']['changeset']->getSchema()) : ?>
									<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_SCHEMA_ERROR', $this->schemaVersion, $this->changeSet['core']['changeset']->getSchema()); ?></li>
								<?php endif; ?>

								<?php if (version_compare($this->updateVersion, JVERSION) != 0) : ?>
									<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_UPDATEVERSION_ERROR', $this->updateVersion, JVERSION); ?></li>
								<?php endif; ?>

								<?php foreach ($this->errors as $line => $error) : ?>
									<?php $key = 'COM_INSTALLER_MSG_DATABASE_' . $error->queryType;
									$msgs = $error->msgElements;
									$file = basename($error->file);
									$msg0 = isset($msgs[0]) ? $msgs[0] : ' ';
									$msg1 = isset($msgs[1]) ? $msgs[1] : ' ';
									$msg2 = isset($msgs[2]) ? $msgs[2] : ' ';
									$message = JText::sprintf($key, $file, $msg0, $msg1, $msg2); ?>
									<li><?php echo $message; ?></li>
								<?php endforeach; ?>
							</ul>
						</fieldset>
					<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php endif; ?>

				<?php if (($this->errorCount3rd != 0) && ($this->errorCount === 0)) : ?>
					<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'otherproblems')); ?>
				<?php endif; ?>

				<?php if ($this->errorCount3rd != 0) : ?>
					<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'otherproblems', JText::plural('COM_INSTALLER_MSG_N_DATABASE_ERROR_PANEL_3RD', $this->errorCount3rd)); ?>
						<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
						<div class="control-group">
							<table class="table table-striped">
								<thead>
								<tr>
									<th style="width:1%" class="nowrap text-center"">
										<?php echo JHtml::_('grid.checkall'); ?>
									</th>
									<th class="nowrap">
										<?php echo JHtml::_('searchtools.sort', 'COM_INSTALLER_HEADING_NAME', 'name', $listDirn, $listOrder); ?>
									</th>
									<th class="nowrap">
										<?php echo JHtml::_('searchtools.sort', 'COM_INSTALLER_HEADING_LOCATION', 'location', $listDirn, $listOrder); ?>
									</th>
									<th class="nowrap">
										<?php echo JHtml::_('searchtools.sort', 'COM_INSTALLER_HEADING_TYPE', 'type', $listDirn, $listOrder); ?>
									</th>
									<th class="nowrap">
										<?php echo JText::_('COM_INSTALLER_CURRENT_VERSION'); ?>
									</th>
									<th class="nowrap">
										<?php echo JText::_('COM_INSTALLER_NEW_VERSION'); ?>
									</th>
									<th class="nowrap">
										<?php echo JHtml::_('searchtools.sort', 'COM_INSTALLER_HEADING_FOLDER', 'folder_translated', $listDirn, $listOrder); ?>
									</th>
									<th class="nowrap">
										<?php echo JHtml::_('searchtools.sort', 'COM_INSTALLER_HEADING_ID', 'extension_id', $listDirn, $listOrder); ?>
									</th>
								</tr>
								</thead>
								<tbody>
								<?php foreach ($this->changeSet as $i => $item) : ?>
									<?php
									$extension = $item['extension'];
									$manifest = json_decode($extension->manifest_cache);

									if (($extension->extension_id == 700) || strcmp($item['schema'], $extension->version_id) == 0)
									{
										continue;
									};
									?>
									<tr class="row<?php echo $i % 2; ?>">
										<td>
											<?php echo JHtml::_('grid.id', $i, $extension->extension_id); ?>
										</td>
										<td>
											<label for="cb<?php echo $i; ?>">
										<span class="editlinktip hasTooltip" title="<?php echo JHtml::_('tooltipText',
											JText::_('JGLOBAL_DESCRIPTION'),
											JText::_($manifest->description) ?
												JText::_($manifest->description) :
												JText::_(
													'COM_INSTALLER_MSG_UPDATE_NODESC'
												),
											0
										); ?>">
										<?php echo $extension->name;?>
										</span>
											</label>
										</td>
										<td class="center">
											<?php echo $extension->client_translated;?>
										</td>
										<td class="center">
											<?php echo $extension->type_translated; ?>
										</td>
										<td class="hidden-sm-down">
											<span class="badge badge-warning"><?php echo $extension->version_id; ?></span>
										</td>
										<td>
											<span class="badge badge-success"><?php echo $item['schema']; ?></span>
										</td>
										<td class="hidden-sm-down">
											<?php echo $extension->folder_translated; ?>
										</td>
										<td>
											<?php echo $extension->extension_id; ?>
										</td>
									</tr>
								<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php endif; ?>

				<?php if (($this->errorCount === 0) && ($this->errorCount3rd === 0)) :?>
					<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'other')); ?>
				<?php endif; ?>
				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'other', JText::_('COM_INSTALLER_MSG_DATABASE_INFO')); ?>
					<div class="control-group">
						<fieldset class="panelform">
							<ul>
								<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_SCHEMA_VERSION', $this->schemaVersion); ?></li>
								<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_UPDATE_VERSION', $this->updateVersion); ?></li>
								<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_DRIVER', JFactory::getDbo()->name); ?></li>
								<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_CHECKED_OK', count($this->results['ok'])); ?></li>
								<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_SKIPPED', count($this->results['skipped'])); ?></li>
							</ul>
						</fieldset>
					</div>
					<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.endTabSet'); ?>

				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</div>
	</form>
</div>
