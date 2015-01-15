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
$dispatcher	= JEventDispatcher::getInstance();
$dispatcher->trigger('onContentBeforeDisplay', array('com_media.file', &$this->_tmp_img, &$params));
?>

	<li class="span2">
		<div  style="height: 20px;position: fixed;width: 170px;">		
			<input class="pull-left" type="checkbox" name="rm[]" id="<?php echo $this->_tmp_img->title; ?>" value="<?php echo $this->_tmp_img->name; ?>" style="position: fixed;"/>
			<?php if ($user->authorise('core.delete', 'com_media')):?>
				<a class="pull-right close delete-item" target="_top" href="index.php?option=com_media&amp;controller=media.delete.media&amp;tmpl=index&amp;<?php echo JSession::getFormToken(); ?>=1&amp;folder=<?php echo $this->state->get('folder'); ?>&amp;rm[]=<?php echo $this->_tmp_img->name; ?>" rel="<?php echo $this->_tmp_img->name; ?>" title="<?php echo JText::_('JACTION_DELETE');?>">
					<i class="icon-delete" style="font-size: small; color: #CB0B0B;"></i>
				</a>
			<?php endif;?>
		</div>
		<article class="thumbnail center" onclick="toggleCheckedStatus('<?php echo $this->_tmp_img->title; ?>');">
			<div class="height-80">					
				<div class="img-preview" style="height: 80px;">
					<?php echo JHtml::_('image', COM_MEDIA_BASEURL . '/' . $this->_tmp_img->path_relative, JText::sprintf('COM_MEDIA_IMAGE_TITLE', $this->_tmp_img->title, JHtml::_('number.bytes', $this->_tmp_img->size)), array('width' => $this->_tmp_img->width_150, 'height' => $this->_tmp_img->height_150)); ?>
				</div> 
			</div>
			<div class="small height-20" style="text-align: centre; font-size: small; padding-top: 5px;">					
				<?php echo JHtml::_('string.truncate', $this->_tmp_img->title, 18, false); ?>
				<a class="img-preview pull-right" href="<?php echo COM_MEDIA_BASEURL . '/' . $this->_tmp_img->path_relative; ?>" title="<?php echo $this->_tmp_img->name; ?>" >
					<i class="icon-zoom-in" style="padding-left: 5px;"></i>
				</a>				
				<?php if ($user->authorise('core.edit', 'com_media')):?>	
					<a class="pull-right" target="_top" href="index.php?option=com_media&amp;controller=media.display.editor&amp;folder=<?php echo $this->state->get('folder'); ?>&amp;file=<?php echo $this->_tmp_img->name; ?>&amp;id=<?php echo $this->_tmp_img->id; ?>" title="<?php echo $this->_tmp_img->name; ?>" class="preview">
						<i class="icon-pencil" style="padding-left: 5px;"></i>
					</a>
				<?php endif;?>
				<div class="clearfix"></div>
			</div>
		</article>		
	</li>
<?php
$dispatcher->trigger('onContentAfterDisplay', array('com_media.file', &$this->_tmp_img, &$params));
