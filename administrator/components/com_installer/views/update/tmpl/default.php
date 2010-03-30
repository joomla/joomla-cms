<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<form action="<?php echo JRoute::_('index.php?option=com_installer&view=update');?>" method="post" name="adminForm">
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
				<th width="10"><?php echo JText::_('INSTALLER_HEADING_UPDATE_NUM'); ?></th>
				<th width="20"><input type="checkbox" name="toggle" value="" onclick="checkAll(this)" /></th>
				<th class="nowrap"><?php echo JHTML::_('grid.sort', 'INSTALLER_HEADING_UPDATE_NAME', 'name', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?></th>
				<th class="nowrap"><?php echo JHTML::_('grid.sort', 'INSTALLER_HEADING_UPDATE_INSTALLTYPE', 'extension_id', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?></th>
				<th ><?php echo JHTML::_('grid.sort', 'INSTALLER_HEADING_UPDATE_EXTENSIONTYPE', 'type', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?></th>
				<th width="10%" class="center"><?php echo JText::_('INSTALLER_HEADING_UPDATE_VERSION'); ?></th>
				<th><?php echo JHTML::_('grid.sort', 'INSTALLER_HEADING_UPDATE_FOLDER', 'folder', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?></th>
				<th><?php echo JHTML::_('grid.sort', 'INSTALLER_HEADING_UPDATE_CLIENT', 'client_id', $this->state->get('list.direction'), $this->state->get('list.ordering')); ?></th>
				<th width="25%"><?php echo JText::_('INSTALLER_HEADING_UPDATE_DETAILSURL'); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
			<td colspan="9"><?php echo $this->pagination->getListFooter(); ?></td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach($this->items as $i=>$item):?>
			<tr class="row<?php echo $i%2; ?>">
				<td><?php echo $this->pagination->getRowOffset($i); ?></td>
				<td><?php echo JHtml::_('grid.id', $i, $item->update_id); ?></td>
				<td>
					<span class="editlinktip hasTip" title="<?php echo JText::_('INSTALLER_TIP_UPDATE_DESCRIPTION');?>::<?php echo $item->description ? $item->description : JText::_('INSTALLER_MSG_UPDATE_NODESC'); ?>">
					<?php echo $item->name; ?>
					</span>
				</td>
				<td class="center">
					<?php echo $item->extension_id ? JText::_('INSTALLER_MSG_UPDATE_UPDATE') : JText::_('INSTALLER_MSG_UPDATE_NEW') ?>
				</td>
				<td><?php echo JText::_('INSTALLER_' . $item->type) ?></td>
				<td class="center"><?php echo $item->version ?></td>
				<td class="center"><?php echo @$item->folder != '' ? $item->folder : JText::_('INSTALLER_NONAPPLICABLE'); ?></td>
				<td class="center"><?php echo @$item->client != '' ? JText::_('INSTALLER_' . $item->client) : JText::_('INSTALLER_NONAPPLICABLE'); ?></td>
				<td><?php echo $item->detailsurl ?></td>
			</tr>
		<?php endforeach;?>
		</tbody>
	</table>
	<?php else : ?>
		<p class="nowarning"><?php echo JText::_('INSTALLER_MSG_UPDATE_NOUPDATES'); ?></p>
	<?php endif; ?>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->state->get('list.ordering'); ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->get('list.direction'); ?>" />
	<?php echo JHTML::_('form.token'); ?>
</form>
