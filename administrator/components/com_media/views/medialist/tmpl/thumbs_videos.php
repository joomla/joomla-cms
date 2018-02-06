<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

$params     = new Registry;
$dispatcher = JEventDispatcher::getInstance();

JFactory::getDocument()->addScriptDeclaration("
jQuery(document).ready(function($){
	window.parent.jQuery('#videoPreview').on('hidden', function () {
		window.parent.jQuery('#mejsPlayer')[0].player.pause();
	});
});
");
?>
<?php foreach ($this->videos as $i => $video) : ?>
	<?php $dispatcher->trigger('onContentBeforeDisplay', array('com_media.file', &$video, &$params)); ?>
	<li class="imgOutline thumbnail height-80 width-80 center">
		<?php if ($this->canDelete) : ?>
			<a class="close delete-item" target="_top" href="index.php?option=com_media&amp;task=file.delete&amp;tmpl=index&amp;<?php echo JSession::getFormToken(); ?>=1&amp;folder=<?php echo $this->state->folder; ?>&amp;rm[]=<?php echo $video->name; ?>" rel="<?php echo $video->name; ?>" title="<?php echo JText::_('JACTION_DELETE'); ?>">&#215;</a>
			<div class="pull-left">
				<?php echo JHtml::_('grid.id', $i, $video->name, false, 'rm', 'cb-video'); ?>
			</div>
			<div class="clearfix"></div>
		<?php endif; ?>

		<div class="height-50">
			<?php echo JHtml::_('image', $video->icon_32, $video->title, null, true); ?>
		</div>

		<div class="small">
			<a class="video-preview" href="<?php echo COM_MEDIA_BASEURL, '/', $video->name; ?>" title="<?php echo $video->name; ?>">
				<?php echo JHtml::_('string.truncate', $video->name, 10, false); ?>
			</a>
		</div>
	</li>
	<?php $dispatcher->trigger('onContentAfterDisplay', array('com_media.file', &$video, &$params)); ?>
<?php endforeach; ?>
