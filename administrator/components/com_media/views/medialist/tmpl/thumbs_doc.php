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
		<div class="imgOutline">
			<div class="imgTotal">
				<div align="center" class="imgBorder">
					<a style="display: block; width: 100%; height: 100%">
						<?php echo JHTML::_('image', $this->_tmp_doc->icon_32, $this->_tmp_doc->name, array('border' => 0), true); ?></a>
				</div>
			</div>
			<div class="controls">
				<a class="delete-item" href="index.php?option=com_media&amp;task=file.delete&amp;tmpl=component&amp;<?php echo JUtility::getToken(); ?>=1&amp;folder=<?php echo $this->state->folder; ?>&amp;rm[]=<?php echo $this->_tmp_doc->name; ?>" rel="<?php echo $this->_tmp_doc->name; ?>"><?php echo JHTML::_('image', 'media/remove.png', JText::_('Delete'), array('width' => 16, 'height' => 16, 'border' => 0), true); ?></a>
				<input type="checkbox" name="rm[]" value="<?php echo $this->_tmp_doc->name; ?>" />
			</div>
			<div class="imginfoBorder">
				<?php echo $this->_tmp_doc->name; ?>
			</div>
		</div>
