<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

// Include jQuery.
JHtml::_('jquery.framework');

// Load the scripts
JHtml::_('script', 'com_joomlaupdate/encryption.js', array('version' => 'auto', 'relative' => true));
JHtml::_('script', 'com_joomlaupdate/update.js', array('version' => 'auto', 'relative' => true));

$password = JFactory::getApplication()->getUserState('com_joomlaupdate.password', null);
$filesize = JFactory::getApplication()->getUserState('com_joomlaupdate.filesize', null);
$ajaxUrl = JUri::base() . 'components/com_joomlaupdate/restore.php';
$returnUrl = 'index.php?option=com_joomlaupdate&task=update.finalise&' . JFactory::getSession()->getFormToken() . '=1';

HTMLHelper::_('script', 'com_joomlaupdate/admin-update-default.js', ['relative' => true, 'version' => 'auto']);

Factory::getDocument()->addScriptOptions(
	'joomlaupdate',
	[
		'password' => $password,
		'totalsize' => $filesize,
		'ajax_url' => $ajaxUrl,
		'return_url' => $returnUrl,
	]
);
?>

<p class="nowarning"><?php echo JText::_('COM_JOOMLAUPDATE_VIEW_UPDATE_INPROGRESS'); ?></p>

<div id="update-progress">
	<div id="extprogress">
		<div id="progress" class="progress">
			<div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
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
