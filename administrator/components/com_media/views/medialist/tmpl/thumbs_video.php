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

$user       = JFactory::getUser();
$params     = new Registry;
$dispatcher = JEventDispatcher::getInstance();
$dispatcher->trigger('onContentBeforeDisplay', array('com_media.file', &$this->_tmp_video, &$params));
?>

<li class="imgOutline thumbnail height-80 width-80 center">
	<?php if ($user->authorise('core.delete', 'com_media')):?>
		<a class="close delete-item" target="_top" href="index.php?option=com_media&amp;task=file.delete&amp;tmpl=index&amp;<?php echo JSession::getFormToken(); ?>=1&amp;folder=<?php echo $this->state->folder; ?>&amp;rm[]=<?php echo $this->_tmp_video->name; ?>" rel="<?php echo $this->_tmp_video->name; ?>" title="<?php echo JText::_('JACTION_DELETE');?>">&#215;</a>
		<input class="pull-left" type="checkbox" name="rm[]" value="<?php echo $this->_tmp_video->name; ?>" />
		<div class="clearfix"></div>
	<?php endif;?>
	<div class="height-50">
		<?php echo JHtml::_('image', $this->_tmp_video->icon_32, $this->_tmp_video->title, null, true); ?>
	</div>
	<div class="small">
		<a onclick="jQuery('<?php echo '#mediaelement' ?>').modal('show');" title="<?php echo $this->_tmp_video->name; ?>">
			<?php echo JHtml::_('string.truncate', $this->_tmp_video->name, 10, false); ?>
		</a>
	</div>
</li>
<?php

$dispatcher->trigger('onContentAfterDisplay', array('com_media.file', &$this->_tmp_video, &$params));

echo JHtml::_(
	'bootstrap.renderModal',
	'mediaelement',
	array(
		'title' => $this->_tmp_video->name,
		'footer' => '<button class="btn" data-dismiss="modal" aria-hidden="true">'
		. JText::_("JLIB_HTML_BEHAVIOR_CLOSE") . '</button>'
	),
	'<video class="mejs-player" src="' . COM_MEDIA_BASEURL . '/' . $this->_tmp_video->path_relative . '"></video>'
);
