<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');

$user = JFactory::getUser();
$params = new JRegistry;
$dispatcher = JEventDispatcher::getInstance();
$dispatcher->trigger('onContentBeforeDisplay', array('com_media.file', &$this->_tmp_img, &$params));
$link = trim($this->state->get('folder') . '/' . $this->_tmp_img->name, '/');
?>
<tr>
    <td>
        <a class="img-preview" href="<?php echo COM_MEDIA_BASEURL . '/' . $this->_tmp_img->path_relative; ?>"
           title="<?php echo $this->_tmp_img->name; ?>"><?php echo JHtml::_('image', COM_MEDIA_BASEURL . '/' . $this->_tmp_img->path_relative, JText::sprintf('COM_MEDIA_IMAGE_TITLE', $this->_tmp_img->title, JHtml::_('number.bytes', $this->_tmp_img->size)), array('width' => $this->_tmp_img->width_16, 'height' => $this->_tmp_img->height_16)); ?></a>
    </td>
    <td class="description">
	    <a onclick = "parent.location.href= 'index.php?option=com_media&amp;view=information&amp;editing=<?php echo $link?>'"
           title="<?php echo $this->_tmp_img->name; ?>"
           rel="preview"><?php echo $this->escape($this->_tmp_img->title); ?></a>
    </td>
	<td class="status">
		<?php echo $this->_tmp_img->checkedOut ?>
	</td>
    <td class="dimensions">
        <?php echo JText::sprintf('COM_MEDIA_IMAGE_DIMENSIONS', $this->_tmp_img->width, $this->_tmp_img->height); ?>
    </td>
    <td class="filesize">
        <?php echo JHtml::_('number.bytes', $this->_tmp_img->size); ?>
    </td>
    <?php if ($user->authorise('core.delete', 'com_media')): ?>
        <td>
            <a class="delete-item" target="_top"
               href="index.php?option=com_media&amp;controller=delete&amp;tmpl=index&amp;operation=delete&amp;<?php echo JSession::getFormToken(); ?>=1&amp;folder=<?php echo $this->state->get('folder'); ?>&amp;rm[]=<?php echo $this->_tmp_img->name; ?>"
               rel="<?php echo $this->_tmp_img->name; ?>"><i class="icon-remove hasTooltip"
                                                             title="<?php echo JText::_('JACTION_DELETE'); ?>"></i></a>
            <input type="checkbox" name="rm[]" value="<?php echo $this->_tmp_img->name; ?>"/>
        </td>
    <?php endif; ?>
    <?php if ($user->authorise('core.create', 'com_media')): ?>
        <td>
            <a class="edit-item" target="_top"
               href="index.php?option=com_media&amp;controller=edit&amp;operation=edit&amp;editing=<?php echo $link ?>"
               rel="<?php echo $this->_tmp_img->name; ?>"><i class="icon-brush hasTooltip"
                                                             title="<?php echo JText::_('JACTION_EDIT'); ?>"></i></a>
        </td>
    <?php endif; ?>
</tr>
<?php
$dispatcher->trigger('onContentAfterDisplay', array('com_media.file', &$this->_tmp_img, &$params));
?>
