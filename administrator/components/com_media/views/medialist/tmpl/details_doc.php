<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_media
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
?>
		<tr>
			<td>
				<a>
					<img src="<?php echo $this->_tmp_doc->icon_16; ?>" width="16" height="16" border="0" alt="<?php echo $this->_tmp_doc->name; ?>" /></a>
			</td>
			<td class="description">
				<?php echo $this->_tmp_doc->name; ?>
			</td>
			<td>&nbsp;

			</td>
			<td>
				<?php echo MediaHelper::parseSize($this->_tmp_doc->size); ?>
			</td>
			<td>
				<a class="delete-item" href="index.php?option=com_media&amp;task=file.delete&amp;tmpl=component&amp;<?php echo JUtility::getToken(); ?>=1&amp;folder=<?php echo $this->state->folder; ?>&amp;rm[]=<?php echo $this->_tmp_doc->name; ?>" rel="<?php echo $this->_tmp_doc->name; ?>"><img src="components/com_media/images/remove.png" width="16" height="16" border="0" alt="<?php echo JText::_('Delete'); ?>" /></a>
				<input type="checkbox" name="rm[]" value="<?php echo $this->_tmp_doc->name; ?>" />
			</td>
		</tr>
