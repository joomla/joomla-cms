<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$params = new JRegistry;
$dispatcher	= JEventDispatcher::getInstance();
$dispatcher->trigger('onContentBeforeDisplay', array('com_media.file', &$this->_tmp_img, &$params));
?>
		<div class="item">
			<a href="javascript:ImageManager.populateFields('<?php echo $this->_tmp_img->path_relative; ?>')" title="<?php echo $this->_tmp_img->name; ?>" >
				<?php echo JHtml::_('image', $this->baseURL.'/'.$this->_tmp_img->path_relative, JText::sprintf('COM_MEDIA_IMAGE_TITLE', $this->_tmp_img->title, MediaHelper::parseSize($this->_tmp_img->size)), array('width' => $this->_tmp_img->width_60, 'height' => $this->_tmp_img->height_60)); ?>
				<span title="<?php echo $this->_tmp_img->name; ?>"><?php echo $this->_tmp_img->title; ?></span></a>
		</div>
<?php
$dispatcher->trigger('onContentAfterDisplay', array('com_media.file', &$this->_tmp_img, &$params));
?>
