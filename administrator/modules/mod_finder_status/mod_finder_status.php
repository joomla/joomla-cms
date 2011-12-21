<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_finder_status
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_BASE') or die;

// Pause if the main menu is disabled.
if (JRequest::getBool('hidemainmenu'))
{
	$text = JText::_('MOD_FINDER_STATUS_PAUSED');
}
else
{
	$text = JText::_('MOD_FINDER_STATUS_WAITING');
	JHtml::_('behavior.framework');
	JHtml::script('mod_finder_status/status.js', false, true);
}

// We need to add some CSS to fix the status bar display.
$doc = JFactory::getDocument();
$doc->addStyleDeclaration(
	'span#finder-status-message { display: none; }'
);
?>
<span id="finder-status-message"><?php echo $text; ?></span>
