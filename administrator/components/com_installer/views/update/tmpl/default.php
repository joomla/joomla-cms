<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('bootstrap.tooltip');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>
<div id="installer-update" class="clearfix">
	<form action="<?php echo JRoute::_('index.php?option=com_installer&view=update');?>" method="post" name="adminForm" id="adminForm">
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">

	<?php if ($this->showMessage) : ?>
		<?php echo $this->loadTemplate('message'); ?>
	<?php endif; ?>

	<?php if ($this->ftp) : ?>
		<?php echo $this->loadTemplate('ftp'); ?>
	<?php endif; ?>
		<?php echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

	<!-- Begin Content -->
		<?php if (count($this->items)) : ?>
		<table class="table table-striped" >
			<thead>
				<tr>
					<th width="20">
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
					<th class="nowrap">
						<?php echo JHtml::_('grid.sort', 'COM_INSTALLER_HEADING_NAME', 'name', $listDirn, $listOrder); ?>
					</th>
					<th class="nowrap">
						<?php echo JHtml::_('grid.sort', 'COM_INSTALLER_HEADING_INSTALLTYPE', 'extension_id', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo JHtml::_('grid.sort', 'COM_INSTALLER_HEADING_TYPE', 'type', $listDirn, $listOrder); ?>
					</th>
					<th width="10%">
						<?php echo JText::_('COM_INSTALLER_CURRENT_VERSION'); ?>
					</th>
					<th width="10%">
						<?php echo JText::_('COM_INSTALLER_NEW_VERSION'); ?>
					</th>
					<th>
						<?php echo JHtml::_('grid.sort', 'COM_INSTALLER_HEADING_FOLDER', 'folder', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo JHtml::_('grid.sort', 'COM_INSTALLER_HEADING_CLIENT', 'client_id', $listDirn, $listOrder); ?>
					</th>
					<th width="25%">
						<?php echo JText::_('COM_INSTALLER_HEADING_DETAILSURL'); ?>
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
				<?php
				$client          = $item->client_id ? JText::_('JADMINISTRATOR') : JText::_('JSITE');
				$manifest        = json_decode($item->manifest_cache);
				$current_version = isset($manifest->version) ? $manifest->version : JText::_('JLIB_UNKNOWN');
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td>
						<?php echo JHtml::_('grid.id', $i, $item->update_id); ?>
					</td>
					<td>
						<label for="cb<?php echo $i; ?>">
							<span class="editlinktip hasTooltip" title="<?php echo JHtml::tooltipText(JText::_('JGLOBAL_DESCRIPTION'), $item->description ? $item->description : JText::_('COM_INSTALLER_MSG_UPDATE_NODESC'), 0); ?>">
							<?php echo $this->escape($item->name); ?>
							</span>
						</label>
					</td>
					<td>
						<?php echo $item->extension_id ? JText::_('COM_INSTALLER_MSG_UPDATE_UPDATE') : JText::_('COM_INSTALLER_NEW_INSTALL') ?>
					</td>
					<td>
						<?php echo JText::_('COM_INSTALLER_TYPE_' . $item->type) ?>
					</td>
					<td>
						<span class="label label-warning"><?php echo $current_version; ?></span>
					</td>
					<td>
						<span class="label label-success"><?php echo $item->version; ?></span>
					</td>
					<td>
						<?php echo @$item->folder != '' ? $item->folder : JText::_('COM_INSTALLER_TYPE_NONAPPLICABLE'); ?>
					</td>
					<td>
						<?php echo $client; ?>
					</td>
					<td><?php echo $item->detailsurl ?>
						<?php if (isset($item->infourl)) : ?>
							<br />
							<a href="<?php echo $item->infourl; ?>" target="_blank">
							<?php echo $this->escape($item->infourl); ?>
							</a>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php else : ?>
			<div class="alert alert-no-items">
				<a class="close" data-dismiss="alert" href="#">&times;</a>
				<?php echo JText::_('COM_INSTALLER_MSG_UPDATE_NOUPDATES'); ?>
			</div>
		<?php endif; ?>

			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>
