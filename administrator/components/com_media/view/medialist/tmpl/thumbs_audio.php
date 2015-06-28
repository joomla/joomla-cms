<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$user = JFactory::getUser();
$params = new JRegistry;
$modalId = $this->_tmp_audio->title . 'Modal';
JHTML::_('behavior.modal');
$dispatcher	= JEventDispatcher::getInstance();
$dispatcher->trigger('onContentBeforeDisplay', array('com_media.file', &$this->_tmp_audio, &$params));
?>
	<li class="span2">
		<article class="thumbnail center">
			<div class="small height-40">
				<input class="pull-left" type="checkbox" name="rm[]" value="<?php echo $this->_tmp_audio->name; ?>" id="<?php echo $this->_tmp_audio->title; ?>" />
				<?php if ($user->authorise('core.delete', 'com_media')):?>
					<a class="close delete-item" target="_top" href="index.php?option=com_media&amp;controller=media.delete.media&amp;tmpl=index&amp;<?php echo JSession::getFormToken(); ?>=1&amp;folder=<?php echo $this->state->get('folder'); ?>&amp;rm[]=<?php echo $this->_tmp_audio->name; ?>" rel="<?php echo $this->_tmp_audio->name; ?>" title="<?php echo JText::_('JACTION_DELETE');?>">
						<span class="icon-delete" style="font-size: x-small; color: #CB0B0B;"></span>
					</a>
				<?php endif;?>
			</div>
			<div class="height-60"  onclick="toggleCheckedStatus('<?php echo $this->_tmp_audio->title; ?>');">
				<a style="display: block; width: 100%; height: 100%" title="<?php echo $this->_tmp_audio->name; ?>" >
					<?php echo JHtml::_('image', $this->_tmp_audio->icon_32, $this->_tmp_audio->name, null, true, true) ? JHtml::_('image', $this->_tmp_audio->icon_32, $this->_tmp_audio->title, null, true) : JHtml::_('image', 'media/con_info.png', $this->_tmp_audio->name, null, true); ?></a>
			</div>
			<div class="height-20" title="<?php echo $this->_tmp_audio->name; ?>" >
				
				<?php echo JHtml::_('string.truncate', $this->_tmp_audio->name, 18, false); ?>
                <a  href="#<?php echo $modalId; ?>" title="<?php echo $this->_tmp_audio->name; ?>" class="modal pull-right" rel="{size: {x: 350, y: 40}, overlay:false}">
                    <span class="icon-zoom-in" style="padding-left: 5px;"></span>
                </a>
				<div class="clearfix"></div>
			</div>
		</article>
	</li>

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
