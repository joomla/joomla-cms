<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

$params     = new Registry;
$dispatcher = JEventDispatcher::getInstance();
?>

<?php foreach ($this->images as $i => $img) : ?>
	<?php $dispatcher->trigger('onContentBeforeDisplay', array('com_media.file', &$img, &$params)); ?>
	<li class="imgOutline thumbnail height-80 width-80 center">
		<?php if ($this->canDelete) : ?>
			<a class="close delete-item" target="_top"
			href="index.php?option=com_media&amp;task=file.delete&amp;tmpl=index&amp;<?php echo JSession::getFormToken(); ?>=1&amp;folder=<?php echo rawurlencode($this->state->folder); ?>&amp;rm[]=<?php echo $this->escape($img->name); ?>"
			rel="<?php echo $this->escape($img->name); ?>" title="<?php echo JText::_('JACTION_DELETE'); ?>">&#215;</a>
			<div class="pull-left">
				<?php echo JHtml::_('grid.id', $i, $this->escape($img->name), false, 'rm', 'cb-image'); ?>
			</div>
			<div class="clearfix"></div>
		<?php endif; ?>

		<div class="height-50">
			<a class="img-preview" href="<?php echo COM_MEDIA_BASEURL . '/' . str_replace('%2F', '/', rawurlencode($img->path_relative)); ?>" title="<?php echo $this->escape($img->name); ?>" >
				<?php echo JHtml::_('image', COM_MEDIA_BASEURL . '/' . $this->escape($img->path_relative), JText::sprintf('COM_MEDIA_IMAGE_TITLE', $this->escape($img->title), JHtml::_('number.bytes', $img->size)), array('width' => $img->width_60, 'height' => $img->height_60)); ?>
			</a>
		</div>

		<div class="small">
			<a href="<?php echo COM_MEDIA_BASEURL, '/', rawurlencode($img->path_relative); ?>" title="<?php echo $this->escape($img->name); ?>" class="preview">
				<?php echo JHtml::_('string.truncate', $this->escape($img->name), 10, false); ?>
			</a>
		</div>
	</li>
	<?php $dispatcher->trigger('onContentAfterDisplay', array('com_media.file', &$img, &$params)); ?>
<?php endforeach; ?>
