<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>

<p class="nowarning"><?php echo JText::_('COM_JOOMLAUPDATE_VIEW_UPDATE_INPROGRESS') ?></p>
<div class="joomlaupdate_spinner" ></div>

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
