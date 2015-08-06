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

<<<<<<< HEAD
$user       = JFactory::getUser();
$params     = new Registry;
$dispatcher = JEventDispatcher::getInstance();
$dispatcher->trigger('onContentBeforeDisplay', array('com_media.file', &$this->_tmp_img, &$params));
?>
	<article class="thumbnail center">
		<?php
		$data   = array(
			'item'   => $this->_tmp_img,
		);
		echo JLayoutHelper::render('medialist.thumbnail.delete', $data);
		?>

		<div class="height-80" onclick="toggleCheckedStatus('<?php echo $this->_tmp_img->title; ?>');">
			<div class="img-preview" style="height: 80px;">
				<?php echo JHtml::_('image', COM_MEDIA_BASEURL . '/' . $this->_tmp_img->path_relative, JText::sprintf('COM_MEDIA_IMAGE_TITLE', $this->_tmp_img->title, JHtml::_('number.bytes', $this->_tmp_img->size)), array('width' => $this->_tmp_img->width_150, 'height' => $this->_tmp_img->height_150)); ?>
			</div>
		</div>

		<div class="small height-20" style="text-align: center; font-size: small;">
			<?php echo JHtml::_('string.truncate', $this->_tmp_img->title, 10, false); ?>

			<a class="img-preview pull-right" href="<?php echo COM_MEDIA_BASEURL . '/' . $this->_tmp_img->path_relative; ?>" title="<?php echo $this->_tmp_img->name; ?>">
				<span class="icon-zoom-in" style="padding-left: 5px;"></span>
			</a>

			<?php
			$data   = array(
				'item'   => $this->_tmp_img,
				'folder' => $this->state->get('folder'),
			);
			echo JLayoutHelper::render('medialist.thumbnail.edit', $data);
			?>
		</div>
	</article>
<?php
$dispatcher->trigger('onContentAfterDisplay', array('com_media.file', &$this->_tmp_img, &$params));
=======
$user = JFactory::getUser();
$params = new Registry;
$dispatcher	= JEventDispatcher::getInstance();
$dispatcher->trigger('onContentBeforeDisplay', array('com_media.file', &$this->_tmp_img, &$params));
?>
<li class="imgOutline thumbnail height-80 width-80 center">
	<?php if ($user->authorise('core.delete', 'com_media')):?>
		<a class="close delete-item" target="_top"
		href="index.php?option=com_media&amp;task=file.delete&amp;tmpl=index&amp;<?php echo JSession::getFormToken(); ?>=1&amp;folder=<?php echo $this->state->folder; ?>&amp;rm[]=<?php echo $this->_tmp_img->name; ?>"
		rel="<?php echo $this->_tmp_img->name; ?>" title="<?php echo JText::_('JACTION_DELETE');?>">&#215;</a>
		<input class="pull-left" type="checkbox" name="rm[]" value="<?php echo $this->_tmp_img->name; ?>" />
		<div class="clearfix"></div>
	<?php endif;?>
	<div class="height-50">
		<a class="img-preview" href="<?php echo COM_MEDIA_BASEURL . '/' . $this->_tmp_img->path_relative; ?>" title="<?php echo $this->_tmp_img->name; ?>" >
			<?php echo JHtml::_('image', COM_MEDIA_BASEURL . '/' . $this->_tmp_img->path_relative, JText::sprintf('COM_MEDIA_IMAGE_TITLE', $this->_tmp_img->title, JHtml::_('number.bytes', $this->_tmp_img->size)), array('width' => $this->_tmp_img->width_60, 'height' => $this->_tmp_img->height_60)); ?>
		</a>
	</div>
	<div class="small">
		<a href="<?php echo COM_MEDIA_BASEURL . '/' . $this->_tmp_img->path_relative; ?>" title="<?php echo $this->_tmp_img->name; ?>" class="preview"><?php echo JHtml::_('string.truncate', $this->_tmp_img->name, 10, false); ?></a>
	</div>
</li>
<?php
	$dispatcher->trigger('onContentAfterDisplay', array('com_media.file', &$this->_tmp_img, &$params));
>>>>>>> upstream/3.5-dev
