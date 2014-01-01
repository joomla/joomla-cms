<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.multiselect');

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>
<div id="installer-update">
<form action="<?php echo JRoute::_('index.php?option=com_installer&view=update');?>" method="post" name="adminForm" id="adminForm">
	<?php if ($this->showMessage) : ?>
		<?php echo $this->loadTemplate('message'); ?>
	<?php endif; ?>

	<?php if ($this->ftp) : ?>
		<?php echo $this->loadTemplate('ftp'); ?>
	<?php endif; ?>

	<?php if (count($this->items)) : ?>
	<table class="adminlist" cellspacing="1">
		<thead>
			<tr>
				<th width="20"><input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" /></th>
				<th class="nowrap"><?php echo JHtml::_('grid.sort', 'COM_INSTALLER_HEADING_NAME', 'name', $listDirn, $listOrder); ?></th>
				<th class="nowrap"><?php echo JHtml::_('grid.sort', 'COM_INSTALLER_HEADING_INSTALLTYPE', 'extension_id', $listDirn, $listOrder); ?></th>
				<th ><?php echo JHtml::_('grid.sort', 'COM_INSTALLER_HEADING_TYPE', 'type', $listDirn, $listOrder); ?></th>
				<th width="10%" class="center"><?php echo JText::_('JVERSION'); ?></th>
				<th><?php echo JHtml::_('grid.sort', 'COM_INSTALLER_HEADING_FOLDER', 'folder', $listDirn, $listOrder); ?></th>
				<th><?php echo JHtml::_('grid.sort', 'COM_INSTALLER_HEADING_CLIENT', 'client_id', $listDirn, $listOrder); ?></th>
				<th width="25%"><?php echo JText::_('COM_INSTALLER_HEADING_DETAILSURL'); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
			<td colspan="9"><?php echo $this->pagination->getListFooter(); ?></td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach($this->items as $i=>$item):
			$client	= $item->client_id ? JText::_('JADMINISTRATOR') : JText::_('JSITE');
		?>
			<tr class="row<?php echo $i%2; ?>">
				<td><?php echo JHtml::_('grid.id', $i, $item->update_id); ?></td>
				<td>
					<span class="editlinktip hasTip" title="<?php echo JText::_('JGLOBAL_DESCRIPTION');?>::<?php echo $item->description ? $this->escape($item->description) : JText::_('COM_INSTALLER_MSG_UPDATE_NODESC'); ?>">
					<?php echo $this->escape($item->name); ?>
					</span>
				</td>
				<td class="center">
					<?php echo $item->extension_id ? JText::_('COM_INSTALLER_MSG_UPDATE_UPDATE') : JText::_('COM_INSTALLER_NEW_INSTALL') ?>
				</td>
				<td><?php echo JText::_('COM_INSTALLER_TYPE_' . $item->type) ?></td>
				<td class="center"><?php echo $item->version ?></td>
				<td class="center"><?php echo @$item->folder != '' ? $item->folder : JText::_('COM_INSTALLER_TYPE_NONAPPLICABLE'); ?></td>
				<td class="center"><?php echo $client; ?></td>
				<td><?php echo $item->detailsurl ?>
					<?php if (isset($item->infourl)) : ?>
					<br /><a href="<?php echo $item->infourl;?>"><?php echo $this->escape($item->infourl);?></a>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach;?>
		</tbody>
	</table>
	<?php else : ?>
		<p class="nowarning"><?php echo JText::_('COM_INSTALLER_MSG_UPDATE_NOUPDATES'); ?></p>
	<?php endif; ?>

	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
</div>