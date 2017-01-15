<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

$params     = new Registry;
?>

<?php foreach ($this->images as $i => $img) : ?>
	<?php JFactory::getApplication()->triggerEvent('onContentBeforeDisplay', array('com_media.file', &$img, &$params)); ?>
	<li class="imgOutline thumbnail center">

		<?php if ($this->canDelete):?>
		<div class="imgDelete">
			<a class="close delete-item" target="_top"
			href="index.php?option=com_media&amp;task=file.delete&amp;tmpl=index&amp;<?php echo JSession::getFormToken(); ?>=1&amp;folder=<?php echo $this->state->folder; ?>&amp;rm[]=<?php echo $img->name; ?>"
			rel="<?php echo $img->name; ?>" title="<?php echo JText::_('JACTION_DELETE'); ?>"><span class="icon-delete"> </span></a>
		</div>
		<?php endif; ?>

		<div class="imgThumb imgInput">
			<?php if ($this->canDelete):?>
			<?php echo JHtml::_('grid.id', $i, $img->name, false, 'rm', 'cb-image'); ?>
			<?php endif; ?>
			<label for="cb-image<?php echo $i ?>">
				<?php echo JHtml::_('image', COM_MEDIA_BASEURL . '/' . $img->path_relative, JText::sprintf('COM_MEDIA_IMAGE_TITLE', $img->title, JHtml::_('number.bytes', $img->size)), array('width' => $img->width_60, 'height' => $img->height_60)); ?>
			</label>
		</div>

 		<div class="imgPreview">
			<a href="<?php echo COM_MEDIA_BASEURL, '/', $img->path_relative; ?>" title="<?php echo $img->name; ?>" class="preview">
				<span class="icon-search"> </span>
			</a>
		</div>
	</li>
	<?php JFactory::getApplication()->triggerEvent('onContentAfterDisplay', array('com_media.file', &$img, &$params)); ?>
<?php endforeach; ?>
