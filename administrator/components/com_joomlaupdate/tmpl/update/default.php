<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

// Include jQuery. @TODO remove jQuery dependency, not needed
HTMLHelper::_('jquery.framework');

// Load the scripts
HTMLHelper::_('script', 'vendor/json3/js/json3.min.js', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('script', 'vendor/aes-js/js/index.js', array('version' => 'auto', 'relative' => true));
HTMLHelper::_('script', 'com_joomlaupdate/update.js', array('version' => 'auto', 'relative' => true));

$password = Factory::getApplication()->getUserState('com_joomlaupdate.password', null);
$filesize = Factory::getApplication()->getUserState('com_joomlaupdate.filesize', null);
$ajaxUrl =  Uri::base() . 'components/com_joomlaupdate/restore.php';
$returnUrl = 'index.php?option=com_joomlaupdate&task=update.finalise&' . Factory::getSession()->getFormToken() . '=1';

Factory::getDocument()->addScriptOptions(
		'com_joomlaupdate',
		[
			'joomlaupdate_password' => $password,
			'joomlaupdate_totalsize' => $filesize,
			'joomlaupdate_ajax_url' => $ajaxUrl,
			'joomlaupdate_return_url' => $returnUrl,
		]
);
?>

<p class="nowarning"><?php echo Text::_('COM_JOOMLAUPDATE_VIEW_UPDATE_INPROGRESS'); ?></p>

<div id="update-progress">
	<div id="extprogress">
		<div id="progress" class="progress">
			<div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
		</div>
		<div class="extprogrow">
			<span class="extlabel"><?php echo Text::_('COM_JOOMLAUPDATE_VIEW_UPDATE_PERCENT'); ?></span>
			<span class="extvalue" id="extpercent"></span>
		</div>
		<div class="extprogrow">
			<span class="extlabel"><?php echo Text::_('COM_JOOMLAUPDATE_VIEW_UPDATE_BYTESREAD'); ?></span>
			<span class="extvalue" id="extbytesin"></span>
		</div>
		<div class="extprogrow">
			<span class="extlabel"><?php echo Text::_('COM_JOOMLAUPDATE_VIEW_UPDATE_BYTESEXTRACTED'); ?></span>
			<span class="extvalue" id="extbytesout"></span>
		</div>
		<div class="extprogrow">
			<span class="extlabel"><?php echo Text::_('COM_JOOMLAUPDATE_VIEW_UPDATE_FILESEXTRACTED'); ?></span>
			<span class="extvalue" id="extfiles"></span>
		</div>
	</div>
</div>
