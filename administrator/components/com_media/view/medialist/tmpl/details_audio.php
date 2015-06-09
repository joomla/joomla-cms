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
$modalId = $this->_tmp_audio->title . 'Modal';
JHTML::_('behavior.modal');
$dispatcher	= JEventDispatcher::getInstance();
$dispatcher->trigger('onContentBeforeDisplay', array('com_media.file', &$this->_tmp_audio, &$params));
?>
		<tr>
			<td>
				<a href="#<?php echo $modalId; ?>" class="modal" rel="{size: {x: 350, y: 40}, overlay:false}" href="<?php echo COM_MEDIA_BASEURL . '/' . $this->_tmp_audio->path_relative; ?>" title="<?php echo $this->_tmp_audio->name; ?>">
                    <?php  echo JHtml::_('image', $this->_tmp_audio->icon_16, $this->_tmp_audio->title, null, true, true) ? JHtml::_('image', $this->_tmp_audio->icon_16, $this->_tmp_audio->title, array('width' => 16, 'height' => 16), true) : JHtml::_('image', 'media/con_info.png', $this->_tmp_audio->title, array('width' => 16, 'height' => 16), true);?> </a>
                </a>
			</td>
			<td class="description">
                <a href="#<?php echo $modalId; ?>" class="modal" rel="{size: {x: 350, y: 40}, overlay:false}" href="<?php echo COM_MEDIA_BASEURL . '/' . $this->_tmp_audio->path_relative; ?>" title="<?php echo $this->_tmp_audio->name; ?>">
                    <?php echo $this->escape($this->_tmp_audio->title); ?>
                </a>
			</td>
			<td class="dimensions">
			</td>
			<td class="filesize">
				<?php echo JHtml::_('number.bytes', $this->_tmp_audio->size); ?>
			</td>
		<?php if ($user->authorise('core.delete', 'com_media')):?>
			<td>
				<a class="delete-item" target="_top" href="index.php?option=com_media&amp;controller=media.delete.media&amp;tmpl=index&amp;<?php echo JSession::getFormToken(); ?>=1&amp;folder=<?php echo $this->state->get('folder'); ?>&amp;rm[]=<?php echo $this->_tmp_audio->name; ?>" rel="<?php echo $this->_tmp_audio->name; ?>"><span class="icon-remove hasTooltip" title="<?php echo JHtml::tooltipText('JACTION_DELETE');?>"></span></a>
				<input type="checkbox" name="rm[]" value="<?php echo $this->_tmp_audio->name; ?>" />
			</td>
		<?php endif;?>
		</tr>

    <!-- Modal -->
    <div style="display: none">
        <div id="<?php echo $modalId; ?>">

            <audio width="320" height="20" controls>
                <source src="<?php echo COM_MEDIA_BASEURL . '/' . $this->_tmp_audio->path_relative; ?>" type="<?php echo $this->_tmp_audio->media_type; ?>">
                Your browser does not support the audio tag.
            </audio>

        </div>
    </div>

    <!-- Modal -->
    <div style="display: none">
        <div id="<?php echo $modalId; ?>">

            <audio width="320" height="20" controls>
                <source src="<?php echo COM_MEDIA_BASEURL . '/' . $this->_tmp_audio->path_relative; ?>" type="<?php echo $this->_tmp_audio->media_type; ?>">
                Your browser does not support the audio tag.
            </audio>

        </div>
    </div>

<?php
$dispatcher->trigger('onContentAfterDisplay', array('com_media.file', &$this->_tmp_audio, &$params));
