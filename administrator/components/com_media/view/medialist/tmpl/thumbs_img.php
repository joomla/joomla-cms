<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
$user = JFactory::getUser();
$params = new JRegistry;
$dispatcher	= JEventDispatcher::getInstance();
$dispatcher->trigger('onContentBeforeDisplay', array('com_media.file', &$this->_tmp_img, &$params));
?>

<!-- Remove opacity set on close by template -->
<style>
.close {
	opacity: 1;
}
</style>

	<li class="span2">
		<article class="thumbnail center" >
			<div class="height-80">					
				<a class="img-preview" href="<?php echo COM_MEDIA_BASEURL . '/' . $this->_tmp_img->path_relative; ?>" title="<?php echo $this->_tmp_img->name; ?>" >
					<?php echo JHtml::_('image', COM_MEDIA_BASEURL . '/' . $this->_tmp_img->path_relative, JText::sprintf('COM_MEDIA_IMAGE_TITLE', $this->_tmp_img->title, JHtml::_('number.bytes', $this->_tmp_img->size)), array('width' => $this->_tmp_img->width_120, 'height' => $this->_tmp_img->height_120)); ?>
				</a> 
			</div>

			<div class="small height-20">
				<label class="checkbox">
					<input class="pull-left" type="checkbox" name="rm[]" value="<?php echo $this->_tmp_img->name; ?>" />
					<a target="_top" href="index.php?option=com_media&amp;controller=media.display.editor&amp;folder=<?php echo $this->state->get('folder'); ?>&amp;file=<?php echo $this->_tmp_img->name; ?>&amp;id=<?php echo $this->_tmp_img->id; ?>" title="<?php echo $this->_tmp_img->name; ?>" class="preview"><?php echo JHtml::_('string.truncate', $this->_tmp_img->title, 8, false); ?></a>

				<?php if ($user->authorise('core.delete', 'com_media')):?>
					<a class="close delete-item" target="_top" href="index.php?option=com_media&amp;controller=media.delete.media&amp;tmpl=index&amp;<?php echo JSession::getFormToken(); ?>=1&amp;folder=<?php echo $this->state->get('folder'); ?>&amp;rm[]=<?php echo $this->_tmp_img->name; ?>" rel="<?php echo $this->_tmp_img->name; ?>" title="<?php echo JText::_('JACTION_DELETE');?>">
						<span class="label label-important">&#215;</span>
					</a>
					<div class="clearfix"></div>
				<?php endif;?>
				</label>
			</div>
		</article>
	</li>
<?php
$dispatcher->trigger('onContentAfterDisplay', array('com_media.file', &$this->_tmp_img, &$params));
