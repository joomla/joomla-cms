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

JHtml::_('bootstrap.tooltip');

$params = new Registry;

JFactory::getDocument()->addScriptDeclaration("
jQuery(document).ready(function($){
	window.parent.jQuery('#videoPreview').on('hidden', function () {
		window.parent.jQuery('#mejsPlayer')[0].player.pause();
	});
});
");
?>

<?php foreach ($this->videos as $i => $video) : ?>
	<?php JFactory::getApplication()->triggerEvent('onContentBeforeDisplay', array('com_media.file', &$video, &$params)); ?>
	<tr>
		<?php if ($this->canDelete) : ?>
			<td>
				<?php echo JHtml::_('grid.id', $i, $video->name, false, 'rm', 'cb-video'); ?>
			</td>
		<?php endif; ?>

		<td>
			<a class="video-preview" href="<?php echo COM_MEDIA_BASEURL, '/', $video->name; ?>" title="<?php echo $video->title; ?>">
				<?php JHtml::_('image', $video->icon_16, $video->title, null, true); ?>
			</a>
		</td>

		<td class="description">
			<a class="video-preview" href="<?php echo COM_MEDIA_BASEURL, '/', $video->name; ?>" title="<?php echo $video->name; ?>">
				<?php echo JHtml::_('string.truncate', $video->name, 10, false); ?>
			</a>
		</td>

		<td class="dimensions">
			<?php // Can we figure out the dimensions of the video? ?>
		</td>

		<td class="filesize">
			<?php echo JHtml::_('number.bytes', $video->size); ?>
		</td>

		<?php if ($this->canDelete) : ?>
			<td>
				<a class="delete-item" target="_top" href="index.php?option=com_media&amp;task=file.delete&amp;tmpl=index&amp;<?php echo JSession::getFormToken(); ?>=1&amp;folder=<?php echo $this->state->folder; ?>&amp;rm[]=<?php echo $video->name; ?>" rel="<?php echo $video->name; ?>">
					<span class="icon-remove hasTooltip" title="<?php echo JHtml::tooltipText('JACTION_DELETE'); ?>"></span>
				</a>
			</td>
		<?php endif; ?>
	</tr>

	<?php JFactory::getApplication()->triggerEvent('onContentAfterDisplay', array('com_media.file', &$video, &$params)); ?>
<?php endforeach; ?>
