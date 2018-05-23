<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

JHtml::_('bootstrap.tooltip');

$user       = JFactory::getUser();
$params     = new Registry;
$dispatcher = JEventDispatcher::getInstance();
$dispatcher->trigger('onContentBeforeDisplay', array('com_media.file', &$this->_tmp_img, &$params));
?>

<tr>
	<td>
		<a class="img-preview" href="<?php echo COM_MEDIA_BASEURL . '/' . rawurlencode($this->_tmp_img->path_relative); ?>" title="<?php echo $this->escape($this->_tmp_img->name); ?>"><?php echo JHtml::_('image', COM_MEDIA_BASEURL . '/' . $this->escape($this->_tmp_img->path_relative), JText::sprintf('COM_MEDIA_IMAGE_TITLE', $this->_tmp_img->title, JHtml::_('number.bytes', $this->_tmp_img->size)), array('width' => $this->_tmp_img->width_16, 'height' => $this->_tmp_img->height_16)); ?></a>
	</td>
	<td class="description">
		<a href="<?php echo  COM_MEDIA_BASEURL . '/' . rawurlencode($this->_tmp_img->path_relative); ?>" title="<?php echo $this->escape($this->_tmp_img->name); ?>" class="preview"><?php echo $this->escape($this->_tmp_img->title); ?></a>
	</td>
	<td class="dimensions">
		<?php echo JText::sprintf('COM_MEDIA_IMAGE_DIMENSIONS', $this->_tmp_img->width, $this->_tmp_img->height); ?>
	</td>
	<td class="filesize">
		<?php echo JHtml::_('number.bytes', $this->_tmp_img->size); ?>
	</td>
	<?php if ($user->authorise('core.delete', 'com_media')):?>
		<td>
			<a class="delete-item" target="_top" href="index.php?option=com_media&amp;task=file.delete&amp;tmpl=index&amp;<?php echo JSession::getFormToken(); ?>=1&amp;folder=<?php echo rawurlencode($this->state->folder); ?>&amp;rm[]=<?php echo $this->escape($this->_tmp_img->name); ?>" rel="<?php echo $this->escape($this->_tmp_img->name); ?>"><span class="icon-remove hasTooltip" title="<?php echo JHtml::_('tooltipText', 'JACTION_DELETE');?>"></span></a>
			<input type="checkbox" name="rm[]" value="<?php echo $this->escape($this->_tmp_img->name); ?>" />
		</td>
	<?php endif;?>
</tr>
<?php $dispatcher->trigger('onContentAfterDisplay', array('com_media.file', &$this->_tmp_img, &$params));
