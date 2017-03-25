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

$user       = JFactory::getUser();
$params     = new Registry;
JFactory::getApplication()->triggerEvent('onContentBeforeDisplay', array('com_media.file', &$this->_tmp_video, &$params));

JFactory::getDocument()->addScriptDeclaration("
jQuery(document).ready(function($){
	window.parent.jQuery('#videoPreview').on('hidden', function () {
		window.parent.jQuery('#mejsPlayer')[0].player.pause();
	});
});
");
?>

<tr>
	<td>
		<a class="video-preview" href="<?php echo COM_MEDIA_BASEURL . '/' . $this->_tmp_video->name; ?>" title="<?php echo $this->_tmp_video->title; ?>"><?php JHtml::_('image', $this->_tmp_video->icon_16, $this->_tmp_video->title, null, true); ?></a>
	</td>
	<td class="description">
		<a class="video-preview" href="<?php echo COM_MEDIA_BASEURL . '/' . $this->_tmp_video->name; ?>" title="<?php echo $this->_tmp_video->name; ?>">
			<?php echo JHtml::_('string.truncate', $this->_tmp_video->name, 10, false); ?>
		</a>
	</td>
	<td class="dimensions">
		<?php // Can we figure out the dimensions of the video? ?>
	</td>
	<td class="filesize">
		<?php echo JHtml::_('number.bytes', $this->_tmp_video->size); ?>
	</td>
	<?php if ($user->authorise('core.delete', 'com_media')):?>
		<td>
			<a class="delete-item" target="_top" href="index.php?option=com_media&amp;task=file.delete&amp;tmpl=index&amp;<?php echo JSession::getFormToken(); ?>=1&amp;folder=<?php echo $this->state->folder; ?>&amp;rm[]=<?php echo $this->_tmp_video->name; ?>" rel="<?php echo $this->_tmp_video->name; ?>"><span class="icon-remove hasTooltip" title="<?php echo JHtml::_('tooltipText', 'JACTION_DELETE');?>"></span></a>
			<input type="checkbox" name="rm[]" value="<?php echo $this->_tmp_video->name; ?>">
		</td>
	<?php endif;?>
</tr>

<?php
JFactory::getApplication()->triggerEvent('onContentAfterDisplay', array('com_media.file', &$this->_tmp_video, &$params));
