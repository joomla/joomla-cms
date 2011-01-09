<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

// no direct access
defined('_JEXEC') or die;
?>
<tr class="<?php echo "row".$this->item->index % 2; ?>" >
	<td><?php echo $this->pagination->getRowOffset($this->item->index); ?></td>
	<td>
		<input type="checkbox" id="cb<?php echo $this->item->index;?>" name="uid[]" value="<?php echo $this->item->update_id; ?>" onclick="isChecked(this.checked);" />
		<span class="editlinktip hasTip" title="<?php echo JText::_('INSTALLER_TIP_UPDATE_DESCRIPTION');?>::<?php echo $this->item->description ? $this->item->description : JText::_('INSTALLER_MSG_UPDATE_NODESC'); ?>">
		<?php echo $this->item->name; ?>
		</span>
	</td>
	<td class="center">
		<?php echo $this->item->extension_id ? JText::_('INSTALLER_MSG_UPDATE_UPDATE') : JText::_('INSTALLER_MSG_UPDATE_NEW') ?>
	</td>
	<td>
		<?php echo JText::_($this->item->type) ?>
	</td>
	<td class="center">
		<?php echo $this->item->version ?>
	</td>
	<td class="center"><?php echo @$this->item->folder != '' ? $this->item->folder : 'N/A'; ?></td>
	<td class="center"><?php echo @$this->item->client != '' ? JText::_($this->item->client) : 'N/A'; ?></td>
	<td>
		<?php echo $this->item->detailsurl ?>
	</td>
</tr>