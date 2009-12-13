<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
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
	 * @param	string $name	(unused)
	 * @param	array $params	Associative array of values
	 * @return	string			The output of the script
	 */
	public function render($name = null, $params = array (), $content = null)
	{
		// Initialise variables.
		$buffer	= null;
		$lists	= null;

		// Get the message queue
		$messages = JFactory::getApplication()->getMessageQueue();

		// Build the sorted message list
		if (is_array($messages) && count($messages))
		{
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
			$buffer .= "\n<dl id=\"system-message\">";
			foreach ($lists as $type => $msgs)
			{
				if (count($msgs))
				{
					$buffer .= "\n<dt class=\"".strtolower($type)."\">".JText::_($type)."</dt>";
					$buffer .= "\n<dd class=\"".strtolower($type)." message fade\">";
					$buffer .= "\n\t<ul>";
					foreach ($msgs as $msg) {
						$buffer .="\n\t\t<li>".$msg."</li>";
					}
					$buffer .= "\n\t</ul>";
					$buffer .= "\n</dd>";
				}
			}
			$buffer .= "\n</dl>";
		}
		return $buffer;
	}
}
