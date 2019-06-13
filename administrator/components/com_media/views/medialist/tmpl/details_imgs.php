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

JHtml::_('bootstrap.tooltip');

$user       = JFactory::getUser();
$params     = new Registry;
$dispatcher = JEventDispatcher::getInstance();
?>

<?php foreach ($this->images as $i => $image) : ?>
	<?php $dispatcher->trigger('onContentBeforeDisplay', array('com_media.file', &$image, &$params)); ?>
	<tr>
		<?php if ($this->canDelete) : ?>
			<td>
				<?php echo JHtml::_('grid.id', $i, $this->escape($image->name), false, 'rm', 'cb-image'); ?>
			</td>
		<?php endif; ?>

		<td>
			<a class="img-preview" href="<?php echo COM_MEDIA_BASEURL . '/' . str_replace('%2F', '/', rawurlencode($image->path_relative)); ?>" title="<?php echo $this->escape($image->name); ?>">
				<?php echo JHtml::_('image', COM_MEDIA_BASEURL . '/' . $this->escape($image->path_relative), JText::sprintf('COM_MEDIA_IMAGE_TITLE', $this->escape($image->title), JHtml::_('number.bytes', $image->size)), array('width' => $image->width_16, 'height' => $image->height_16)); ?>
			</a>
		</td>

		<td class="description">
			<a href="<?php echo  COM_MEDIA_BASEURL . '/' . str_replace('%2F', '/', rawurlencode($image->path_relative)); ?>" title="<?php echo $this->escape($image->name); ?>" class="preview">
				<?php echo $this->escape($image->title); ?>
			</a>
		</td>

		<td class="dimensions">
			<?php echo JText::sprintf('COM_MEDIA_IMAGE_DIMENSIONS', $image->width, $image->height); ?>
		</td>

		<td class="filesize">
			<?php echo JHtml::_('number.bytes', $image->size); ?>
		</td>

		<?php if ($this->canDelete) : ?>
			<td>
				<a class="delete-item" target="_top" href="index.php?option=com_media&amp;task=file.delete&amp;tmpl=index&amp;<?php echo JSession::getFormToken(); ?>=1&amp;folder=<?php echo rawurlencode($this->state->folder); ?>&amp;rm[]=<?php echo $this->escape($image->name); ?>" rel="<?php echo $this->escape($image->name); ?>">
					<span class="icon-remove hasTooltip" title="<?php echo JHtml::tooltipText('JACTION_DELETE'); ?>"></span>
				</a>
			</td>
		<?php endif; ?>
	</tr>
	<?php $dispatcher->trigger('onContentAfterDisplay', array('com_media.file', &$image, &$params)); ?>
<?php endforeach; ?>
