<?php
/**
 * @package     Joomla.Site
 * @subpackage  Template.Beez3
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

function renderMessage($msgList)
{
	$buffer  = null;
	$buffer .= "\n<div id=\"system-message-container\">";

	if (is_array($msgList))
	{
		$buffer .= "\n<dl id=\"system-message\">";
		foreach ($msgList as $type => $msgs)
		{
			if (count($msgs))
			{
				$buffer .= "\n<dt class=\"" . strtolower($type) . "\">" . JText::_($type) . "</dt>";
				$buffer .= "\n<dd class=\"" . strtolower($type) . " message\">";
				$buffer .= "\n\t<ul>";
				foreach ($msgs as $msg)
				{
					$buffer .= "\n\t\t<li>" . $msg . "</li>";
				}
				$buffer .= "\n\t</ul>";
				$buffer .= "\n</dd>";
			}
			$buffer .= "\n</dl>";
		}

		$buffer .= "\n</div>";
		return $buffer;
	}
}
