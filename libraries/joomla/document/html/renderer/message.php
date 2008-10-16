<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Document
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

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
	public function render($name, $params = array (), $content = null)
	{
		$appl = JFactory::getApplication();

		// Initialize variables
		$contents	= null;
		$lists		= null;

		// Get the message queue
		$messages = $appl->getMessageQueue();

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
					$contents .= "\n<dt class=\"".strtolower($type)."\">".JText::_( $type )."</dt>";
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
