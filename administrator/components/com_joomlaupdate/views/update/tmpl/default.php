<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include jQuery.
JHtml::_('jquery.framework');

// Load the scripts
JHtml::script('com_joomlaupdate/json2.js', false, true, false);
JHtml::script('com_joomlaupdate/encryption.js', false, true, false);
JHtml::script('com_joomlaupdate/update.js', false, true, false);

$password = JFactory::getApplication()->getUserState('com_joomlaupdate.password', null);
$filesize = JFactory::getApplication()->getUserState('com_joomlaupdate.filesize', null);
$ajaxUrl = JUri::base() . 'components/com_joomlaupdate/restore.php';
$returnUrl = 'index.php?option=com_joomlaupdate&task=update.finalise&' . JFactory::getSession()->getFormToken() . '=1';

JFactory::getDocument()->addScriptDeclaration(
	"
	var joomlaupdate_password = '$password';
	var joomlaupdate_totalsize = '$filesize';
	var joomlaupdate_ajax_url = '$ajaxUrl';
	var joomlaupdate_return_url = '$returnUrl';

	jQuery(document).ready(function(){
		window.pingExtract();
		});
	"
);
?>

<p class="nowarning"><?php echo JText::_('COM_JOOMLAUPDATE_VIEW_UPDATE_INPROGRESS') ?></p>

<div id="update-progress">
	<div id="extprogress">
		<div id="progress" class="progress progress-striped active">
			<div id="progress-bar" class="bar bar-success" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
		</div>
		<div class="extprogrow">
			<span class="extlabel"><?php echo JText::_('COM_JOOMLAUPDATE_VIEW_UPDATE_PERCENT'); ?></span>
			<span class="extvalue" id="extpercent"></span>
		</div>
		<div class="extprogrow">
			<span class="extlabel"><?php echo JText::_('COM_JOOMLAUPDATE_VIEW_UPDATE_BYTESREAD'); ?></span>
			<span class="extvalue" id="extbytesin"></span>
		</div>
		<div class="extprogrow">
			<span class="extlabel"><?php echo JText::_('COM_JOOMLAUPDATE_VIEW_UPDATE_BYTESEXTRACTED'); ?></span>
			<span class="extvalue" id="extbytesout"></span>
		</div>
		<div class="extprogrow">
			<span class="extlabel"><?php echo JText::_('COM_JOOMLAUPDATE_VIEW_UPDATE_FILESEXTRACTED'); ?></span>
			<span class="extvalue" id="extfiles"></span>
		</div>
	</div>
</div>
