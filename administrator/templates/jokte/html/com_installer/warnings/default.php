<?php
/**
 * @version		$Id: default.php 20196 2011-01-09 02:40:25Z ian $
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

// no direct access
defined('_JEXEC') or die;
?>
<form action="<?php echo JRoute::_('index.php?option=com_installer&view=warnings');?>" method="post" name="adminForm" id="adminForm">
<div id="warnings-list">
<?php

if (!count($this->messages)) {
	echo '<div class="noresults"><p>'. JText::_('COM_INSTALLER_MSG_WARNINGS_NONE').'</p></div>';
} else {
	echo JHtml::_('sliders.start', 'warning-sliders', array('useCookie'=>1));
	foreach($this->messages as $message) {
		echo JHtml::_('sliders.panel', $message['message'], str_replace(' ','', $message['message']));
		echo '<div style="padding: 5px; margin: 0 10px!Important;" ><p>'.$message['description'].'</p></div>';
	}
	echo JHtml::_('sliders.panel', JText::_('COM_INSTALLER_MSG_WARNINGFURTHERINFO'),'furtherinfo-pane');
	echo '<div style="padding: 5px; margin: 0 10px!Important;" ><p>'. JText::_('COM_INSTALLER_MSG_WARNINGFURTHERINFODESC') .'</p></div>';
	echo JHtml::_('sliders.end');
}
?>
</div>
<div class="clr"> </div>
<div>
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHTML::_('form.token'); ?>
</div>
</form>