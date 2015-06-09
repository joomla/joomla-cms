<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');

$user = JFactory::getUser();
$params = new JRegistry;
$modalId = $this->_tmp_video->title . 'Modal';
JHTML::_('behavior.modal');
$dispatcher	= JEventDispatcher::getInstance();
$dispatcher->trigger('onContentBeforeDisplay', array('com_media.file', &$this->_tmp_video, &$params));
?>
		<tr>
			<td>
				<a href="#<?php echo $modalId; ?>" class="modal" rel="{size: {x: 350, y: 260}, overlay:false}" href="<?php echo COM_MEDIA_BASEURL . '/' . $this->_tmp_video->path_relative; ?>" title="<?php echo $this->_tmp_video->name; ?>">
                    <?php  echo JHtml::_('image', $this->_tmp_video->icon_16, $this->_tmp_video->title, null, true, true) ? JHtml::_('image', $this->_tmp_video->icon_16, $this->_tmp_video->title, array('width' => 16, 'height' => 16), true) : JHtml::_('image', 'media/con_info.png', $this->_tmp_video->title, array('width' => 16, 'height' => 16), true);?> </a>
                </a>
			</td>
			<td class="description">
                <a href="#<?php echo $modalId; ?>" class="modal" rel="{size: {x: 350, y: 260}, overlay:false}" href="<?php echo COM_MEDIA_BASEURL . '/' . $this->_tmp_video->path_relative; ?>" title="<?php echo $this->_tmp_video->name; ?>">
                    <?php echo $this->escape($this->_tmp_video->title); ?>
                </a>
			</td>
			<td class="dimensions">
			</td>
			<td class="filesize">
				<?php echo JHtml::_('number.bytes', $this->_tmp_video->size); ?>
			</td>
		<?php if ($user->authorise('core.delete', 'com_media')):?>
			<td>
				<a class="delete-item" target="_top" href="index.php?option=com_media&amp;controller=media.delete.media&amp;tmpl=index&amp;<?php echo JSession::getFormToken(); ?>=1&amp;folder=<?php echo $this->state->get('folder'); ?>&amp;rm[]=<?php echo $this->_tmp_video->name; ?>" rel="<?php echo $this->_tmp_video->name; ?>"><span class="icon-remove hasTooltip" title="<?php echo JHtml::tooltipText('JACTION_DELETE');?>"></span></a>
				<input type="checkbox" name="rm[]" value="<?php echo $this->_tmp_video->name; ?>" />
			</td>
		<?php endif;?>
		</tr>

    <!-- Modal -->
    <div style="display: none">
        <div id="<?php echo $modalId; ?>">

            <video width="320" height="240" controls>
                <source src="<?php echo COM_MEDIA_BASEURL . '/' . $this->_tmp_video->path_relative; ?>" type="<?php echo $this->_tmp_video->media_type; ?>">
                Your browser does not support the video tag.
            </video>

        </div>
    </div>
<?php
$dispatcher->trigger('onContentAfterDisplay', array('com_media.file', &$this->_tmp_video, &$params));
