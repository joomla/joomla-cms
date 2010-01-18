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
				<div class="imgBorder center">
					<a class="img-preview" href="<?php echo COM_MEDIA_BASEURL.'/'.$this->_tmp_img->path_relative; ?>" title="<?php echo $this->_tmp_img->name; ?>" style="display: block; width: 100%; height: 100%">
						<div class="image">
							<img src="<?php echo COM_MEDIA_BASEURL.'/'.$this->_tmp_img->path_relative; ?>" width="<?php echo $this->_tmp_img->width_60; ?>" height="<?php echo $this->_tmp_img->height_60; ?>" alt="<?php echo $this->_tmp_img->name; ?> - <?php echo MediaHelper::parseSize($this->_tmp_img->size); ?>" border="0" />
						</div></a>
				</div>
			</div>
			<div class="controls">
				<a class="delete-item" href="index.php?option=com_media&amp;task=file.delete&amp;tmpl=component&amp;<?php echo JUtility::getToken(); ?>=1&amp;folder=<?php echo $this->state->folder; ?>&amp;rm[]=<?php echo $this->_tmp_img->name; ?>" rel="<?php echo $this->_tmp_img->name; ?>"><img src="../media/media/images/remove.png" width="16" height="16" border="0" alt="<?php echo JText::_('Delete'); ?>" /></a>
				<input type="checkbox" name="rm[]" value="<?php echo $this->_tmp_img->name; ?>" />
			</div>
			<div class="imginfoBorder">
				<a href="<?php echo COM_MEDIA_BASEURL.'/'.$this->_tmp_img->path_relative; ?>" class="preview"><?php echo $this->escape(substr($this->_tmp_img->name, 0, 10) . (strlen($this->_tmp_img->name) > 10 ? '...' : '')); ?></a>
			</div>
		</div>
