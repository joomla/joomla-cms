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
$modalId = $this->_tmp_video->title . 'Modal';
JHTML::_('behavior.modal');
$dispatcher	= JEventDispatcher::getInstance();
$dispatcher->trigger('onContentBeforeDisplay', array('com_media.file', &$this->_tmp_video, &$params));
?>
	<li class="span2">
		<article class="thumbnail center">
			<div class="small height-40">
				<input class="pull-left" type="checkbox" name="rm[]" value="<?php echo $this->_tmp_video->name; ?>" id="<?php echo $this->_tmp_video->title; ?>" />
				<?php if ($user->authorise('core.delete', 'com_media')):?>
					<a class="close delete-item" target="_top" href="index.php?option=com_media&amp;controller=media.delete.media&amp;tmpl=index&amp;<?php echo JSession::getFormToken(); ?>=1&amp;folder=<?php echo $this->state->get('folder'); ?>&amp;rm[]=<?php echo $this->_tmp_video->name; ?>" rel="<?php echo $this->_tmp_video->name; ?>" title="<?php echo JText::_('JACTION_DELETE');?>">
						<span class="icon-delete" style="font-size: x-small; color: #CB0B0B;"></span>
					</a>
				<?php endif;?>
			</div>
			<div class="height-60"  onclick="toggleCheckedStatus('<?php echo $this->_tmp_video->title; ?>');">
				<a style="display: block; width: 100%; height: 100%" title="<?php echo $this->_tmp_video->name; ?>" >
					<?php echo JHtml::_('image', $this->_tmp_video->icon_32, $this->_tmp_video->name, null, true, true) ? JHtml::_('image', $this->_tmp_video->icon_32, $this->_tmp_video->title, null, true) : JHtml::_('image', 'media/con_info.png', $this->_tmp_video->name, null, true); ?></a>
			</div>
			<div class="height-20" title="<?php echo $this->_tmp_video->name; ?>" >
				
				<?php echo JHtml::_('string.truncate', $this->_tmp_video->name, 18, false); ?>
                <a class="modal pull-right" href="#<?php echo $modalId; ?>" title="<?php echo $this->_tmp_video->name; ?>" rel="{size: {x: 350, y: 260}, overlay:false}">
                    <span class="icon-zoom-in" style="padding-left: 5px;"></span>
                </a>
				<div class="clearfix"></div>
			</div>
		</article>
	</li>

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
