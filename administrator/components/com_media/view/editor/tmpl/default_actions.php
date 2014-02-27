<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.modal');
JHtml::_('formbehavior.chosen', 'select');

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tabstate');

$input = JFactory::getApplication()->input;

// JHtml::_('script', 'system/jquery.Jcrop.min.js', false, true);
// JHtml::_('stylesheet', 'system/jquery.Jcrop.min.css', array(), true);

?>

<p class="well well-small lead">

	<button data-toggle='modal' data-target='#resizeModal'
		class='btn btn-large'>
		<i class='icon-refresh' title='COM_MEDIA_EDITOR_BUTTON_RESIZE'></i>
		<?php echo JText::_('COM_MEDIA_EDITOR_BUTTON_RESIZE') ?>
	</button>

	<br /> <br />

	<button data-toggle='modal' data-target='#renameModal'
		class='btn btn-large'>
		<i class='icon-refresh' title='COM_MEDIA_EDITOR_BUTTON_RENAME'></i>
		<?php echo JText::_('COM_MEDIA_EDITOR_BUTTON_RENAME') ?>
	</button>

	<br /> <br /> 

	<button type="submit" form="adminForm" class='btn btn-large'>
		<i class='icon-move' title='COM_MEDIA_EDITOR_BUTTON_CROP'></i>
		<?php echo JText::_('COM_MEDIA_EDITOR_BUTTON_CROP') ?>
	</button>
	
	<br /> <br />

	<button data-toggle='modal' data-target='#rotateModal' class='btn btn-large'>
		<i class='icon-refresh' title='COM_MEDIA_EDITOR_BUTTON_ROTATE'></i>
		<?php echo JText::_('COM_MEDIA_EDITOR_BUTTON_ROTATE') ?>
	</button>

	<br /> <br />

	<button data-toggle='modal' data-target='#filterModal' class='btn btn-large'>
		<i class='icon-refresh' title='COM_MEDIA_EDITOR_BUTTON_FILTER'></i>
		<?php echo JText::_('COM_MEDIA_EDITOR_BUTTON_FILTER') ?>
	</button>

	<br /> <br />

	<button data-toggle='modal' data-target='#thumbsModal' class='btn btn-large'>
		<i class='icon-refresh' title='COM_MEDIA_EDITOR_BUTTON_THUMBS'></i>
		<?php echo JText::_('COM_MEDIA_EDITOR_BUTTON_THUMBS') ?>
	</button>

	<br /> <br />

</p>
