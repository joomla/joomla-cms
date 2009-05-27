<?php
/**
 * @version		$Id: message.php 10707 2008-08-21 09:52:47Z eddieajau $
 * @package		Joomla.Framework
 * @subpackage	Document
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * JDocument system message renderer
 *
 * @package		Joomla.Framework
 * @subpackage	Document
 * @since		1.5
 */
class JDocumentRendererMessage extends JDocumentRenderer
{
	/**
	 * Renders the error stack and returns the results as a string
	 *
	 * @access public
	 * @param string 	$name		(unused)
	 * @param array 	$params		Associative array of values
	 * @return string	The output of the script
	 */
	function render($name = null, $params = array (), $content = null)
	{
		global $mainframe;

		// Initialize variables
		$contents	= null;
		$lists		= null;

		// Get the message queue
		$messages = $mainframe->getMessageQueue();

		// Build the sorted message list
		if (is_array($messages) && count($messages)) {
			foreach ($messages as $msg)
			{
				if (isset($msg['type']) && isset($msg['message'])) {
					$lists[$msg['type']][] = $msg['message'];
				}
			}
		}

		// If messages exist render them
		if (is_array($lists))
		{
			// Build the return string
			$contents .= "\n<dl id=\"system-message\">";
			foreach ($lists as $type => $msgs)
			{
				if (count($msgs)) {
					$contents .= "\n<dt class=\"".strtolower($type)."\">".JText::_($type)."</dt>";
					$contents .= "\n<dd class=\"".strtolower($type)." message fade\">";
					$contents .= "\n\t<ul>";
					foreach ($msgs as $msg)
					{
						$contents .="\n\t\t<li>".$msg."</li>";
					}
					$contents .= "\n\t</ul>";
					$contents .= "\n</dd>";
				}
			}
			$contents .= "\n</dl>";
		}
		return $contents;
	}
}
