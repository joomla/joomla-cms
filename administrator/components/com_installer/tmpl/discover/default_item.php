<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<tr class="<?php echo 'row' . $this->item->index % 2; ?>" <?php echo $this->item->style; ?>>
	<td>
		<input type="checkbox" id="cb<?php echo $this->item->index; ?>" name="eid[]" value="<?php echo $this->item->extension_id; ?>" onclick="Joomla.isChecked(this.checked);" <?php echo $this->item->cbd; ?>>
		<span class="bold"><?php echo $this->item->name; ?></span>
	</td>
	<td>
		<?php echo $this->item->type ?>
	</td>
	<td class="text-center">
		<?php if (!$this->item->element) : ?>
		<strong>X</strong>
		<?php else : ?>
		<a href="index.php?option=com_installer&amp;type=manage&amp;task=<?php echo $this->item->task; ?>&amp;eid[]=<?php echo $this->item->extension_id; ?>&amp;limitstart=<?php echo $this->pagination->limitstart; ?>&amp;<?php echo JSession::getFormToken(); ?>=1"><?php echo JHtml::_('image', 'images/' . $this->item->img, $this->item->alt, array('title' => $this->item->action)); ?></a>
		<?php endif; ?>
	</td>
	<td class="text-center"><?php echo @$this->item->folder != '' ? $this->item->folder : 'N/A'; ?></td>
	<td class="text-center"><?php echo @$this->item->client != '' ? $this->item->client : 'N/A'; ?></td>
	<td>
		<span class="editlinktip hasTooltip" title="<?php echo JHtml::_('tooltipText', JText::_('COM_INSTALLER_AUTHOR_INFORMATION'), $this->item->author_info, 0); ?>">
			<?php echo @$this->item->author != '' ? $this->item->author : '&#160;'; ?>
		</span>
	</td>
</tr>
