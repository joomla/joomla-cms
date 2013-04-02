<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.Isis
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

function renderMessage($msgList)
{
	$buffer  = null;
	$buffer .= "\n<div id=\"system-message-container\">";
	$alert = array('error' => 'alert-error', 'warning' => '', 'notice' => 'alert-info', 'message' => 'alert-success');

	// Only render the message list and the close button if $msgList has items
	if (is_array($msgList) && (count($msgList) >= 1))
	{
		$buffer .= '<button type="button" class="close" data-dismiss="alert">&times;</button>';
		foreach ($msgList as $type => $msgs)
		{
			$buffer .= '<div class="alert ' . $alert[$type]. '">';
			$buffer .= "\n<h4 class=\"alert-heading\">" . JText::_($type) . "</h4>";
			if (count($msgs))
			{
				foreach ($msgs as $msg)
				{
					$buffer .= "\n\t\t<p>" . $msg . "</p>";
				}
			}
			$buffer .= "\n</div>";
		}
	}

	$buffer .= "\n</div>";

	return $buffer;
}
