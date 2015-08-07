<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

JHtml::_('bootstrap.tooltip');

$user        = JFactory::getUser();
$params     = new Registry;
$dispatcher = JEventDispatcher::getInstance();

$dispatcher->trigger('onContentBeforeDisplay', array('com_media.file', &$this->_tmp_img, &$params));

$data = array(
	'item' => $this->_tmp_img,
);

?>
		<tr>
			<?php echo JLayoutHelper::render('medialist.detail.delete', $data); ?>
			<td>
				<a class="img-preview" href="<?php echo COM_MEDIA_BASEURL . '/' . $this->_tmp_img->path_relative; ?>" title="<?php echo $this->_tmp_img->name; ?>">
					<?php echo JHtml::_( ?>
						<?php 'image', ?>
						<?php COM_MEDIA_BASEURL . '/' . $this->_tmp_img->path_relative, ?>
						<?php JText::sprintf('COM_MEDIA_IMAGE_TITLE', $this->_tmp_img->title, JHtml::_('number.bytes', $this->_tmp_img->size)), ?>
						<?php array('width' => $this->_tmp_img->width_16, 'height' => $this->_tmp_img->height_16) ?>
					<?php ); ?>
				</a>
			</td>
			<td class="description">
				<a href="<?php echo  COM_MEDIA_BASEURL . '/' . $this->_tmp_img->path_relative; ?>" title="<?php echo $this->_tmp_img->name; ?>" rel="preview">
					<?php echo $this->escape($this->_tmp_img->title); ?>
				</a>
			</td>
			<td class="dimensions">
				<?php echo JText::sprintf('COM_MEDIA_IMAGE_DIMENSIONS', $this->_tmp_img->width, $this->_tmp_img->height); ?>
			</td>
			<td class="filesize">
				<?php echo JHtml::_('number.bytes', $this->_tmp_img->size); ?>
			</td>
		</tr>
<?php
$dispatcher->trigger('onContentAfterDisplay', array('com_media.file', &$this->_tmp_img, &$params));
